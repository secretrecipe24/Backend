<?php

namespace App\Http\Controllers;

use App\Models\Coach;
use App\Models\CoachReview;
use App\Models\Exercise;
use App\Models\Food;
use App\Models\MealLog;
use App\Models\MealPlan;
use App\Models\User;
use App\Models\UserMatch;
use App\Models\WorkoutPlan;
use App\Models\WorkoutLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CoachPlanController extends Controller
{
    public function acceptMatch(Request $request, int $matchId)
    {
        /** @var \App\Models\Coach $coach */
        $coach = $request->user();

        $match = UserMatch::query()
            ->where('id', $matchId)
            ->where('coach_id', $coach->id)
            ->firstOrFail();

        if ($match->status !== 'pending') {
            return response()->json(['message' => 'Only pending matches can be accepted.'], 422);
        }

        $match->forceFill([
            'status' => 'active',
            'start_date' => $match->start_date ?? now()->toDateString(),
            'end_date' => null,
        ])->save();

        return response()->json([
            'message' => 'Request accepted. Match is now active.',
            'match' => $match->fresh(),
        ]);
    }

    public function rejectMatch(Request $request, int $matchId)
    {
        /** @var \App\Models\Coach $coach */
        $coach = $request->user();

        return DB::transaction(function () use ($coach, $matchId) {
            $match = UserMatch::query()
                ->lockForUpdate()
                ->where('id', $matchId)
                ->where('coach_id', $coach->id)
                ->firstOrFail();

            if ($match->status !== 'pending') {
                return response()->json(['message' => 'Only pending matches can be rejected.'], 422);
            }

            $match->forceFill([
                'status' => 'cancelled',
                'end_date' => now()->toDateString(),
            ])->save();

            // Selection increments current_clients immediately; revert on rejection.
            Coach::query()
                ->where('id', $coach->id)
                ->where('current_clients', '>', 0)
                ->decrement('current_clients');

            return response()->json([
                'message' => 'Request rejected.',
                'match' => $match->fresh(),
            ]);
        });
    }

    public function getMyClients(Request $request)
    {
        /** @var \App\Models\Coach $coach */
        $coach = $request->user();

        $matches = UserMatch::query()
            ->with(['client:id,name,email'])
            ->where('coach_id', $coach->id)
            ->whereIn('status', ['pending', 'active'])
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->orderByDesc('created_at')
            ->get();

        $pendingMatches = $matches->where('status', 'pending')->values();
        $activeMatches = $matches->where('status', 'active')->values();

        // Backwards compatibility: keep `clients` as active clients.
        $activeClients = $activeMatches
            ->pluck('client')
            ->filter()
            ->values();

        $activeClientIds = $activeClients->pluck('id');
        $activeWorkoutPlans = WorkoutPlan::query()
            ->where('coach_id', $coach->id)
            ->whereIn('client_id', $activeClientIds)
            ->where('status', 'active')
            ->get(['client_id', 'plan_type']);
        $activeMealPlans = MealPlan::query()
            ->where('coach_id', $coach->id)
            ->whereIn('client_id', $activeClientIds)
            ->where('status', 'active')
            ->get(['client_id', 'plan_type']);
        $recentWorkoutLogs = WorkoutLog::query()
            ->whereIn('client_id', $activeClientIds)
            ->whereDate('date', '>=', now()->subDays(6)->toDateString())
            ->get(['client_id', 'date']);
        $recentMealLogs = MealLog::query()
            ->whereIn('client_id', $activeClientIds)
            ->whereDate('date', '>=', now()->subDays(6)->toDateString())
            ->get(['client_id', 'date']);

        $activeClients->each(function (User $client) use (
            $activeWorkoutPlans,
            $activeMealPlans,
            $recentWorkoutLogs,
            $recentMealLogs
        ) {
            $planTypes = collect([
                $activeWorkoutPlans->where('client_id', $client->id)->isNotEmpty() ? 'Workout' : null,
                $activeMealPlans->where('client_id', $client->id)->isNotEmpty() ? 'Meal' : null,
            ])->filter()->values();
            $logDates = collect()
                ->merge($recentWorkoutLogs->where('client_id', $client->id)->pluck('date'))
                ->merge($recentMealLogs->where('client_id', $client->id)->pluck('date'))
                ->map(fn ($date) => (string) $date)
                ->unique()
                ->count();
            $latestActivity = collect()
                ->merge($recentWorkoutLogs->where('client_id', $client->id)->pluck('date'))
                ->merge($recentMealLogs->where('client_id', $client->id)->pluck('date'))
                ->sortDesc()
                ->first();

            $client->setAttribute('plan_type', $planTypes->isEmpty() ? null : $planTypes->implode(' + '));
            $client->setAttribute('last_active', $latestActivity);
            $client->setAttribute('adherence', $logDates ? min(100, round(($logDates / 7) * 100)) : null);
        });

        $pendingClients = $pendingMatches
            ->pluck('client')
            ->filter()
            ->values();

        return response()->json([
            'clients' => $activeClients,
            'active_clients' => $activeClients,
            'pending_clients' => $pendingClients,
            'active_matches' => $activeMatches,
            'pending_matches' => $pendingMatches,
            'counts' => [
                'total' => $matches->count(),
                'active' => $activeMatches->count(),
                'pending' => $pendingMatches->count(),
                'satisfaction' => CoachReview::where('coach_id', $coach->id)->avg('rating'),
            ],
        ]);
    }

    public function getUpcomingSchedules(Request $request)
    {
        $coach = $request->user();

        $workouts = WorkoutPlan::query()
            ->with('client:id,name')
            ->where('coach_id', $coach->id)
            ->where('status', 'active')
            ->get()
            ->map(fn (WorkoutPlan $plan) => [
                'id' => $plan->id,
                'type' => 'Workout',
                'title' => $plan->title,
                'client' => $plan->client?->name,
                'start_date' => $plan->start_date?->toDateString(),
                'end_date' => $plan->end_date?->toDateString(),
                'schedule' => $plan->schedule,
            ]);

        $meals = MealPlan::query()
            ->with('client:id,name')
            ->where('coach_id', $coach->id)
            ->where('status', 'active')
            ->get()
            ->map(fn (MealPlan $plan) => [
                'id' => $plan->id,
                'type' => 'Meal',
                'title' => $plan->title,
                'client' => $plan->client?->name,
                'start_date' => $plan->start_date?->toDateString(),
                'end_date' => $plan->end_date?->toDateString(),
                'schedule' => $plan->schedule,
            ]);

        return response()->json([
            'data' => $workouts->merge($meals)->sortBy('start_date')->values(),
        ]);
    }

    public function getClientWorkoutPlans(Request $request, int $clientId)
    {
        $coach = $request->user();
        $this->ensureCoachCanAccessClient($coach->id, $clientId);

        $plans = WorkoutPlan::query()
            ->where('coach_id', $coach->id)
            ->where('client_id', $clientId)
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['workout_plans' => $plans]);
    }

    public function getClientMealPlans(Request $request, int $clientId)
    {
        $coach = $request->user();
        $this->ensureCoachCanAccessClient($coach->id, $clientId);

        $plans = MealPlan::query()
            ->where('coach_id', $coach->id)
            ->where('client_id', $clientId)
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['meal_plans' => $plans]);
    }

    public function storeWorkoutPlan(Request $request)
    {
        /** @var \App\Models\Coach $coach */
        $coach = $request->user();

        $validated = $request->validate([
            'client_id' => ['required', 'integer', 'exists:users,id'],
            'match_id' => ['nullable', 'integer', 'exists:matches,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'exercises' => ['required', 'array'],
            'plan_type' => ['required', Rule::in(['daily', 'weekly', 'monthly'])],
            'schedule' => ['nullable', 'array'],
            'duration' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'frequency' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'goals' => ['nullable', 'array'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $this->ensureCoachCanAccessClient($coach->id, (int) $validated['client_id'], $validated['match_id'] ?? null);

        $plan = WorkoutPlan::create([
            'coach_id' => $coach->id,
            'client_id' => $validated['client_id'],
            'match_id' => $validated['match_id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'exercises' => $validated['exercises'],
            'plan_type' => $validated['plan_type'],
            'schedule' => $validated['schedule'] ?? null,
            'duration' => $validated['duration'] ?? null,
            'frequency' => $validated['frequency'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'goals' => $validated['goals'] ?? null,
            'status' => 'draft',
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'] ?? null,
        ]);

        return response()->json(['workout_plan' => $plan], 201);
    }

    public function updateWorkoutPlan(Request $request, int $id)
    {
        /** @var \App\Models\Coach $coach */
        $coach = $request->user();

        $plan = WorkoutPlan::query()
            ->where('coach_id', $coach->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'exercises' => ['sometimes', 'array'],
            'plan_type' => ['sometimes', Rule::in(['daily', 'weekly', 'monthly'])],
            'schedule' => ['sometimes', 'nullable', 'array'],
            'duration' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:100000'],
            'frequency' => ['sometimes', 'nullable', 'string', 'max:255'],
            'notes' => ['sometimes', 'nullable', 'string'],
            'goals' => ['sometimes', 'nullable', 'array'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'nullable', 'date'],
            'status' => ['sometimes', Rule::in(['draft', 'active', 'completed', 'archived'])],
            'completed_at' => ['sometimes', 'nullable', 'date'],
        ]);

        if (isset($validated['end_date']) && isset($validated['start_date']) && $validated['end_date'] < $validated['start_date']) {
            return response()->json(['message' => 'end_date must be after or equal to start_date.'], 422);
        }

        $plan->fill($validated)->save();

        return response()->json(['workout_plan' => $plan->fresh()]);
    }

    public function storeMealPlan(Request $request)
    {
        /** @var \App\Models\Coach $coach */
        $coach = $request->user();

        $validated = $request->validate([
            'client_id' => ['required', 'integer', 'exists:users,id'],
            'match_id' => ['nullable', 'integer', 'exists:matches,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'meals' => ['required', 'array'],
            'plan_type' => ['required', Rule::in(['daily', 'weekly', 'monthly'])],
            'target_calories' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'macros' => ['nullable', 'array'],
            'schedule' => ['nullable', 'array'],
            'notes' => ['nullable', 'string'],
            'dietary_restrictions' => ['nullable', 'array'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $this->ensureCoachCanAccessClient($coach->id, (int) $validated['client_id'], $validated['match_id'] ?? null);

        $plan = MealPlan::create([
            'coach_id' => $coach->id,
            'client_id' => $validated['client_id'],
            'match_id' => $validated['match_id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'meals' => $validated['meals'],
            'plan_type' => $validated['plan_type'],
            'target_calories' => $validated['target_calories'] ?? null,
            'macros' => $validated['macros'] ?? null,
            'schedule' => $validated['schedule'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'dietary_restrictions' => $validated['dietary_restrictions'] ?? null,
            'status' => 'draft',
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'] ?? null,
        ]);

        return response()->json(['meal_plan' => $plan], 201);
    }

    public function updateMealPlan(Request $request, int $id)
    {
        /** @var \App\Models\Coach $coach */
        $coach = $request->user();

        $plan = MealPlan::query()
            ->where('coach_id', $coach->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'meals' => ['sometimes', 'array'],
            'plan_type' => ['sometimes', Rule::in(['daily', 'weekly', 'monthly'])],
            'target_calories' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:100000'],
            'macros' => ['sometimes', 'nullable', 'array'],
            'schedule' => ['sometimes', 'nullable', 'array'],
            'notes' => ['sometimes', 'nullable', 'string'],
            'dietary_restrictions' => ['sometimes', 'nullable', 'array'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'nullable', 'date'],
            'status' => ['sometimes', Rule::in(['draft', 'active', 'completed', 'archived'])],
            'completed_at' => ['sometimes', 'nullable', 'date'],
        ]);

        $plan->fill($validated)->save();

        return response()->json(['meal_plan' => $plan->fresh()]);
    }

    public function activatePlan(Request $request, string $type, int $id)
    {
        /** @var \App\Models\Coach $coach */
        $coach = $request->user();

        if (! in_array($type, ['workout', 'meal'], true)) {
            return response()->json(['message' => 'Invalid plan type.'], 422);
        }

        $model = $type === 'workout' ? WorkoutPlan::class : MealPlan::class;

        $plan = $model::query()
            ->where('coach_id', $coach->id)
            ->findOrFail($id);

        $plan->forceFill(['status' => 'active'])->save();

        return response()->json(['plan' => $plan->fresh()]);
    }

    public function deletePlan(Request $request, string $type, int $id)
    {
        /** @var \App\Models\Coach $coach */
        $coach = $request->user();

        if (! in_array($type, ['workout', 'meal'], true)) {
            return response()->json(['message' => 'Invalid plan type.'], 422);
        }

        $model = $type === 'workout' ? WorkoutPlan::class : MealPlan::class;

        $plan = $model::query()
            ->where('coach_id', $coach->id)
            ->findOrFail($id);

        $plan->delete();

        return response()->json(['message' => 'Plan deleted.']);
    }

    public function getPlanTemplates(Request $request, string $type)
    {
        if (! in_array($type, ['workout', 'meal'], true)) {
            return response()->json(['message' => 'Invalid plan type.'], 422);
        }

        $model = $type === 'workout' ? WorkoutPlan::class : MealPlan::class;
        $search = trim((string) $request->query('search', ''));

        $query = $model::query()
            ->where('coach_id', $request->user()->id)
            ->orderByDesc('updated_at');

        if ($search !== '') {
            $query->where('title', 'like', "%{$search}%");
        }

        return response()->json(['templates' => $query->limit(50)->get()]);
    }

    public function getExerciseLibrary()
    {
        $exercises = Exercise::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json(['exercises' => $exercises]);
    }

    public function getFoodLibrary()
    {
        $foods = Food::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json(['foods' => $foods]);
    }

    private function ensureCoachCanAccessClient(int $coachId, int $clientId, ?int $matchId = null): void
    {
        $matchQuery = UserMatch::query()
            ->where('coach_id', $coachId)
            ->where('client_id', $clientId)
            ->whereIn('status', ['active', 'pending']);

        if ($matchId !== null) {
            $matchQuery->where('id', $matchId);
        }

        $matchExists = $matchQuery->exists();

        if (! $matchExists) {
            abort(response()->json(['message' => 'Coach is not assigned to this client.'], 403));
        }
    }
}
