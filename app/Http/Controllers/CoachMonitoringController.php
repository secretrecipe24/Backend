<?php

namespace App\Http\Controllers;

use App\Models\CoachNote;
use App\Models\MealLog;
use App\Models\MealPlan;
use App\Models\Measurement;
use App\Models\ProgressPhoto;
use App\Models\User;
use App\Models\UserMatch;
use App\Models\WorkoutLog;
use App\Models\WorkoutPlan;
use App\Models\SleepLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CoachMonitoringController extends Controller
{
    public function getClientProgress(Request $request, int $clientId)
    {
        $coachId = $request->user()->id;
        $this->ensureCoachAssigned($coachId, $clientId);

        $client = User::query()
            ->select(['id', 'name', 'email', 'age', 'gender', 'fitness_goals', 'dietary_preferences', 'restrictions', 'goal_weight'])
            ->findOrFail($clientId);

        $match = UserMatch::query()
            ->where('coach_id', $coachId)
            ->where('client_id', $clientId)
            ->whereIn('status', ['pending', 'active'])
            ->orderByDesc('created_at')
            ->first();

        $activeWorkoutPlans = WorkoutPlan::query()
            ->where('coach_id', $coachId)
            ->where('client_id', $clientId)
            ->where('status', 'active')
            ->orderByDesc('start_date')
            ->get();

        $activeMealPlans = MealPlan::query()
            ->where('coach_id', $coachId)
            ->where('client_id', $clientId)
            ->where('status', 'active')
            ->orderByDesc('start_date')
            ->get();

        $recentWorkoutLogs = WorkoutLog::query()
            ->where('client_id', $clientId)
            ->whereDate('date', '>=', now()->subDays(7)->toDateString())
            ->orderByDesc('date')
            ->limit(50)
            ->get();

        $recentMealLogs = MealLog::query()
            ->where('client_id', $clientId)
            ->whereDate('date', '>=', now()->subDays(7)->toDateString())
            ->orderByDesc('date')
            ->limit(100)
            ->get();

        $recentSleepLogs = SleepLog::query()
            ->where('client_id', $clientId)
            ->whereDate('date', '>=', now()->subDays(7)->toDateString())
            ->orderByDesc('date')
            ->limit(7)
            ->get();

        $workoutAdherence = $this->workoutAdherenceData($coachId, $clientId, 7);
        $mealAdherence = $this->mealAdherenceData($coachId, $clientId, 7);

        return response()->json([
            'client' => $client,
            'match' => $match,
            'active_plans' => [
                'workouts' => $activeWorkoutPlans,
                'meals' => $activeMealPlans,
            ],
            'recent_logs' => [
                'workouts' => $recentWorkoutLogs,
                'meals' => $recentMealLogs,
                'sleep' => $recentSleepLogs,
            ],
            'adherence' => [
                'workouts' => $workoutAdherence,
                'meals' => $mealAdherence,
            ],
        ]);
    }

    public function getClientWorkoutAdherence(Request $request, int $clientId)
    {
        $coachId = $request->user()->id;
        $this->ensureCoachAssigned($coachId, $clientId);

        $days = (int) ($request->query('days', 30));
        $days = max(1, min(365, $days));

        return response()->json($this->workoutAdherenceData($coachId, $clientId, $days));
    }

    public function getClientMealAdherence(Request $request, int $clientId)
    {
        $coachId = $request->user()->id;
        $this->ensureCoachAssigned($coachId, $clientId);

        $days = (int) ($request->query('days', 30));
        $days = max(1, min(365, $days));

        return response()->json($this->mealAdherenceData($coachId, $clientId, $days));
    }

    public function getClientMeasurements(Request $request, int $clientId)
    {
        $coachId = $request->user()->id;
        $this->ensureCoachAssigned($coachId, $clientId);

        $measurements = Measurement::query()
            ->where('user_id', $clientId)
            ->orderByDesc('measurement_date')
            ->get();

        $photos = ProgressPhoto::query()
            ->where('user_id', $clientId)
            ->orderByDesc('photo_date')
            ->get();

        return response()->json([
            'measurements' => $measurements,
            'photos' => $photos,
        ]);
    }

    public function getClientPhotos(Request $request, int $clientId)
    {
        $coachId = $request->user()->id;
        $this->ensureCoachAssigned($coachId, $clientId);

        $photos = ProgressPhoto::query()
            ->where('user_id', $clientId)
            ->where('is_approved', true)
            ->orderBy('photo_date')
            ->get();

        $byType = $photos->groupBy('photo_type');

        $beforeAfter = $byType->map(function ($typePhotos) {
            /** @var \Illuminate\Support\Collection $typePhotos */
            $before = $typePhotos->first();
            $after = $typePhotos->last();

            return [
                'before' => $before,
                'after' => $after,
            ];
        });

        return response()->json([
            'photos' => $photos,
            'before_after' => $beforeAfter,
        ]);
    }

    public function getClientLogs(Request $request, int $clientId, string $type)
    {
        $coachId = $request->user()->id;
        $this->ensureCoachAssigned($coachId, $clientId);

        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $perPage = $validated['per_page'] ?? 20;

        if ($type === 'workout') {
            $query = WorkoutLog::query()
                ->where('client_id', $clientId)
                ->orderByDesc('date')
                ->orderByDesc('id');

            if (! empty($validated['from'])) {
                $query->whereDate('date', '>=', $validated['from']);
            }
            if (! empty($validated['to'])) {
                $query->whereDate('date', '<=', $validated['to']);
            }

            return response()->json($query->paginate($perPage));
        }

        if ($type === 'meal') {
            $query = MealLog::query()
                ->where('client_id', $clientId)
                ->orderByDesc('date')
                ->orderByDesc('id');

            if (! empty($validated['from'])) {
                $query->whereDate('date', '>=', $validated['from']);
            }
            if (! empty($validated['to'])) {
                $query->whereDate('date', '<=', $validated['to']);
            }

            return response()->json($query->paginate($perPage));
        }

        if ($type === 'sleep') {
            $query = SleepLog::query()
                ->where('client_id', $clientId)
                ->orderByDesc('date')
                ->orderByDesc('id');

            if (! empty($validated['from'])) $query->whereDate('date', '>=', $validated['from']);
            if (! empty($validated['to'])) $query->whereDate('date', '<=', $validated['to']);

            return response()->json($query->paginate($perPage));
        }

        return response()->json(['message' => 'Invalid type. Use workout, meal, or sleep.'], 422);
    }

    public function addCoachNote(Request $request, int $clientId)
    {
        $coachId = $request->user()->id;
        $this->ensureCoachAssigned($coachId, $clientId);

        $validated = $request->validate([
            'note' => ['required', 'string', 'max:5000'],
        ]);

        $note = CoachNote::create([
            'coach_id' => $coachId,
            'client_id' => $clientId,
            'note' => $validated['note'],
        ]);

        return response()->json([
            'message' => 'Note added.',
            'coach_note' => $note,
        ], 201);
    }

    private function ensureCoachAssigned(int $coachId, int $clientId): void
    {
        $ok = UserMatch::query()
            ->where('coach_id', $coachId)
            ->where('client_id', $clientId)
            ->whereIn('status', ['active', 'pending'])
            ->exists();

        if (! $ok) {
            abort(response()->json(['message' => 'Coach is not assigned to this client.'], 403));
        }
    }

    private function workoutAdherenceData(int $coachId, int $clientId, int $days): array
    {
        $from = now()->subDays($days - 1)->startOfDay();
        $to = now()->endOfDay();

        $activePlans = WorkoutPlan::query()
            ->where('coach_id', $coachId)
            ->where('client_id', $clientId)
            ->where('status', 'active')
            ->get(['id', 'plan_type', 'schedule', 'start_date', 'end_date']);

        $plannedDays = $this->plannedDaysCount($activePlans->map(fn ($p) => [
            'plan_type' => $p->plan_type,
            'schedule' => $p->schedule,
        ])->all(), $days);

        $logs = WorkoutLog::query()
            ->where('client_id', $clientId)
            ->whereDate('date', '>=', $from->toDateString())
            ->whereDate('date', '<=', $to->toDateString())
            ->get(['date']);

        $actualDays = $logs
            ->map(fn (WorkoutLog $l) => (string) $l->getRawOriginal('date'))
            ->unique()
            ->count();

        $rate = $plannedDays > 0 ? ($actualDays / $plannedDays) : 0.0;

        $chart = $this->dailyPresenceChart($logs, $days, 'date');

        return [
            'days' => $days,
            'planned_days' => $plannedDays,
            'actual_days_logged' => $actualDays,
            'completion_rate' => round(min(1, $rate), 3),
            'chart' => $chart,
        ];
    }

    private function mealAdherenceData(int $coachId, int $clientId, int $days): array
    {
        $from = now()->subDays($days - 1)->startOfDay();
        $to = now()->endOfDay();

        $activePlan = MealPlan::query()
            ->where('coach_id', $coachId)
            ->where('client_id', $clientId)
            ->where('status', 'active')
            ->orderByDesc('start_date')
            ->first(['id', 'target_calories', 'macros', 'start_date', 'end_date']);

        $logs = MealLog::query()
            ->where('client_id', $clientId)
            ->whereDate('date', '>=', $from->toDateString())
            ->whereDate('date', '<=', $to->toDateString())
            ->get(['date', 'calories', 'protein', 'carbs', 'fat', 'water_intake']);

        $daysLogged = $logs
            ->map(fn (MealLog $l) => (string) $l->getRawOriginal('date'))
            ->unique()
            ->count();

        $plannedDays = $days;
        $consistency = $plannedDays > 0 ? ($daysLogged / $plannedDays) : 0.0;

        $dailyTotals = $logs->groupBy(fn (MealLog $l) => (string) $l->getRawOriginal('date'))->map(function ($dayLogs, $date) {
            return [
                'date' => $date,
                'calories' => (float) $dayLogs->sum('calories'),
                'protein' => (float) $dayLogs->sum('protein'),
                'carbs' => (float) $dayLogs->sum('carbs'),
                'fat' => (float) $dayLogs->sum('fat'),
                'water_intake' => (float) $dayLogs->sum('water_intake'),
            ];
        })->values();

        $targetCalories = $activePlan?->target_calories;
        $calorieAdherence = null;
        if ($targetCalories) {
            $diffs = $dailyTotals->map(fn ($d) => abs(($d['calories'] ?? 0) - $targetCalories));
            $avgDiff = $diffs->count() ? $diffs->avg() : null;
            $calorieAdherence = $avgDiff === null ? null : max(0.0, 1.0 - ($avgDiff / max(1, $targetCalories)));
        }

        $chart = $this->dailyPresenceChart($logs, $days, 'date');

        return [
            'days' => $days,
            'days_logged' => $daysLogged,
            'consistency_rate' => round(min(1, $consistency), 3),
            'target_calories' => $targetCalories,
            'calorie_adherence' => $calorieAdherence === null ? null : round(min(1, $calorieAdherence), 3),
            'daily_totals' => $dailyTotals,
            'chart' => $chart,
        ];
    }

    private function plannedDaysCount(array $plans, int $days): int
    {
        // If any plan is daily, treat as daily schedule.
        foreach ($plans as $p) {
            if (($p['plan_type'] ?? null) === 'daily') {
                return $days;
            }
        }

        // If schedule provided (days of week booleans), count true days within range.
        $scheduleDays = null;
        foreach ($plans as $p) {
            if (is_array($p['schedule'] ?? null)) {
                $scheduleDays = $p['schedule'];
                break;
            }
        }

        if (is_array($scheduleDays)) {
            $trueDays = 0;
            $cursor = now()->subDays($days - 1)->startOfDay();
            for ($i = 0; $i < $days; $i++) {
                $dow = strtolower($cursor->format('l')); // monday, tuesday...
                if (! empty($scheduleDays[$dow])) {
                    $trueDays++;
                }
                $cursor->addDay();
            }
            return max(0, $trueDays);
        }

        // Fallback: weekly = 3 planned sessions/week, monthly = 1 planned session/month.
        $assumed = 0;
        foreach ($plans as $p) {
            if (($p['plan_type'] ?? null) === 'weekly') {
                $assumed = max($assumed, (int) round(($days / 7) * 3));
            }
            if (($p['plan_type'] ?? null) === 'monthly') {
                $assumed = max($assumed, (int) ceil($days / 30));
            }
        }

        return max(0, $assumed);
    }

    private function dailyPresenceChart($logs, int $days, string $dateField): array
    {
        $datesWithLogs = collect($logs)->map(function ($l) use ($dateField) {
            if (is_object($l) && method_exists($l, 'getRawOriginal')) {
                return (string) $l->getRawOriginal($dateField);
            }

            if (is_array($l) && array_key_exists($dateField, $l)) {
                return (string) $l[$dateField];
            }

            if (is_object($l) && isset($l->{$dateField})) {
                return (string) $l->{$dateField};
            }

            return '';
        })->filter()->unique()->flip();

        $points = [];
        $cursor = now()->subDays($days - 1)->startOfDay();
        for ($i = 0; $i < $days; $i++) {
            $key = $cursor->toDateString();
            $points[] = [
                'date' => $key,
                'has_log' => $datesWithLogs->has($key),
            ];
            $cursor->addDay();
        }

        return $points;
    }
}
