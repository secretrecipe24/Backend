<?php

namespace App\Http\Controllers;

use App\Models\Coach;
use App\Models\CoachReview;
use App\Models\UserMatch;
use App\Models\UserPreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MatchController extends Controller
{
    public function savePreferences(Request $request)
    {
        $validated = $request->validate([
            'fitness_goals' => ['required', 'array'],
            'coaching_style' => ['required', Rule::in(['motivational', 'strict', 'educational', 'supportive', 'no_preference'])],
            'preferred_specialties' => ['required', 'array'],
            'communication_frequency' => ['required', Rule::in(['daily', 'few_times_week', 'weekly', 'bi_weekly', 'as_needed'])],
            'communication_style' => ['required', Rule::in(['chat_only', 'chat_with_checkins', 'video_calls', 'mixed'])],
            'experience_level' => ['required', Rule::in(['beginner', 'intermediate', 'advanced', 'any'])],
        ]);

        $preference = UserPreference::updateOrCreate(
            ['user_id' => $request->user()->id],
            array_merge($validated, ['is_completed' => true])
        );

        return response()->json([
            'message' => 'Preferences saved.',
            'preferences' => $preference,
        ]);
    }

    public function getSuggestions(Request $request)
    {
        $prefs = UserPreference::query()
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $prefs) {
            return response()->json(['message' => 'Please complete preferences first.'], 422);
        }

        $coaches = Coach::query()
            ->where('status', 'active')
            ->whereColumn('current_clients', '<', 'max_clients')
            ->get();

        $ranked = $coaches->map(function (Coach $coach) use ($prefs) {
            $score = $this->calculateMatchScore($prefs->toArray(), $coach);

            return [
                'coach' => $coach,
                'match_score' => $score,
                'match_percentage' => round($score * 100, 1),
            ];
        })
            ->sortByDesc('match_score')
            ->take(10)
            ->values();

        return response()->json([
            'suggestions' => $ranked,
        ]);
    }

    public function getAllCoaches(Request $request)
    {
        $validated = $request->validate([
            'specialty' => ['nullable', 'string', 'max:100'],
            'min_rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'available_only' => ['nullable', 'boolean'],
            'sort_by' => ['nullable', Rule::in(['rating', 'experience', 'price'])],
            'sort_dir' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $query = Coach::query()->where('status', 'active');

        if (! empty($validated['specialty'])) {
            $query->whereJsonContains('specialties', $validated['specialty']);
        }

        if (! empty($validated['min_rating'])) {
            $query->where('rating', '>=', $validated['min_rating']);
        }

        if (($validated['available_only'] ?? false) === true) {
            $query->whereColumn('current_clients', '<', 'max_clients');
        }

        $sortBy = $validated['sort_by'] ?? 'rating';
        $sortDir = $validated['sort_dir'] ?? 'desc';

        $sortColumn = match ($sortBy) {
            'experience' => 'years_experience',
            'price' => 'monthly_rate',
            default => 'rating',
        };

        $query->orderBy($sortColumn, $sortDir);

        return response()->json(
            $query->paginate($validated['per_page'] ?? 10)
        );
    }

    public function getCoachDetails(int $id)
    {
        $coach = Coach::query()
            ->where('status', 'active')
            ->findOrFail($id);

        // Reviews are not yet modeled in the database schema; return empty placeholder.
        return response()->json([
            'coach' => $coach,
            'reviews' => [],
        ]);
    }

    public function selectCoach(Request $request)
    {
        $validated = $request->validate([
            'coach_id' => ['required', 'integer', 'exists:coaches,id'],
        ]);

        $userId = $request->user()->id;

        return DB::transaction(function () use ($validated, $userId) {
            $existing = UserMatch::query()
                ->where('client_id', $userId)
                ->whereIn('status', ['pending', 'active'])
                ->first();

            if ($existing) {
                return response()->json(['message' => 'You already have an active or pending match.'], 409);
            }

            /** @var \App\Models\Coach $coach */
            $coach = Coach::query()
                ->lockForUpdate()
                ->where('status', 'active')
                ->findOrFail($validated['coach_id']);

            if ($coach->current_clients >= $coach->max_clients) {
                return response()->json(['message' => 'Coach is currently full.'], 409);
            }

            $match = UserMatch::create([
                'client_id' => $userId,
                'coach_id' => $coach->id,
                'status' => 'pending',
                'start_date' => now()->toDateString(),
                'match_score' => null,
                'selected_by_user' => true,
            ]);

            $coach->increment('current_clients');

            return response()->json([
                'message' => 'Coach selected. Match is pending.',
                'match' => $match->load('coach'),
            ], 201);
        });
    }

    public function replaceCoach(Request $request)
    {
        $validated = $request->validate([
            'coach_id' => ['required', 'integer', 'exists:coaches,id'],
        ]);

        $userId = $request->user()->id;

        return DB::transaction(function () use ($validated, $userId) {
            $existing = UserMatch::query()
                ->lockForUpdate()
                ->where('client_id', $userId)
                ->whereIn('status', ['pending', 'active'])
                ->latest('id')
                ->first();

            if (! $existing) {
                return response()->json(['message' => 'You do not have an active or pending coach.'], 409);
            }

            if ((int) $existing->coach_id === (int) $validated['coach_id']) {
                return response()->json(['message' => 'This is already your selected coach.'], 409);
            }

            /** @var \App\Models\Coach $coach */
            $coach = Coach::query()
                ->lockForUpdate()
                ->where('status', 'active')
                ->findOrFail($validated['coach_id']);

            if ($coach->current_clients >= $coach->max_clients) {
                return response()->json(['message' => 'Coach is currently full.'], 409);
            }

            $existing->forceFill([
                'status' => 'cancelled',
                'end_date' => now()->toDateString(),
            ])->save();

            Coach::query()
                ->where('id', $existing->coach_id)
                ->where('current_clients', '>', 0)
                ->decrement('current_clients');

            $match = UserMatch::create([
                'client_id' => $userId,
                'coach_id' => $coach->id,
                'status' => 'pending',
                'start_date' => now()->toDateString(),
                'match_score' => null,
                'selected_by_user' => true,
            ]);

            $coach->increment('current_clients');

            return response()->json([
                'message' => 'Replacement coach selected. Match is pending.',
                'match' => $match->load('coach'),
            ], 201);
        });
    }

    public function getMyMatch(Request $request)
    {
        $match = UserMatch::query()
            ->where('client_id', $request->user()->id)
            ->whereIn('status', ['pending', 'active'])
            ->with('coach')
            ->orderByDesc('created_at')
            ->first();

        $review = $match
            ? CoachReview::query()->where('coach_id', $match->coach_id)->where('client_id', $request->user()->id)->first()
            : null;

        return response()->json(['match' => $match, 'review' => $review]);
    }

    public function rateCoach(Request $request)
    {
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);
        $client = $request->user();
        $match = UserMatch::query()
            ->where('client_id', $client->id)
            ->where('status', 'active')
            ->latest()
            ->firstOrFail();

        $review = CoachReview::updateOrCreate(
            ['coach_id' => $match->coach_id, 'client_id' => $client->id],
            $validated
        );
        $average = CoachReview::where('coach_id', $match->coach_id)->avg('rating');
        Coach::whereKey($match->coach_id)->update(['rating' => round((float) $average, 2)]);

        return response()->json(['review' => $review, 'coach_rating' => round((float) $average, 2)]);
    }

    /**
     * Weights:
     * - specialties: 35%
     * - coaching style: 25%
     * - experience level: 20%
     * - fitness goals: 20%
     */
    private function calculateMatchScore(array $preferences, Coach $coach): float
    {
        $specialtiesScore = $this->overlapScore(
            (array) ($preferences['preferred_specialties'] ?? []),
            (array) ($coach->specialties ?? [])
        );

        $coachingStyleScore = $this->coachingStyleScore(
            (string) ($preferences['coaching_style'] ?? 'no_preference'),
            (array) ($coach->coaching_styles ?? [])
        );

        $experienceScore = $this->experienceScore(
            (string) ($preferences['experience_level'] ?? 'any'),
            (array) ($coach->client_demographics ?? [])
        );

        $fitnessGoalsScore = $this->fitnessGoalsScore(
            (array) ($preferences['fitness_goals'] ?? []),
            (array) ($coach->expertise ?? []),
            (array) ($coach->expertise_areas ?? [])
        );

        $score = (0.35 * $specialtiesScore)
            + (0.25 * $coachingStyleScore)
            + (0.20 * $experienceScore)
            + (0.20 * $fitnessGoalsScore);

        return max(0.0, min(1.0, $score));
    }

    private function overlapScore(array $desired, array $offered): float
    {
        $desired = array_values(array_filter(array_map('strval', $desired)));
        $offered = array_values(array_filter(array_map('strval', $offered)));

        if (count($desired) === 0) {
            return 0.0;
        }

        $intersection = array_intersect($desired, $offered);

        return count($intersection) / count($desired);
    }

    private function coachingStyleScore(string $preferred, array $coachStyles): float
    {
        if ($preferred === 'no_preference') {
            return 1.0;
        }

        $coachStyles = array_values(array_filter(array_map('strval', $coachStyles)));

        return in_array($preferred, $coachStyles, true) ? 1.0 : 0.0;
    }

    private function experienceScore(string $preferred, array $coachDemographics): float
    {
        if ($preferred === 'any') {
            return 1.0;
        }

        $coachDemographics = array_values(array_filter(array_map('strval', $coachDemographics)));

        return in_array($preferred, $coachDemographics, true) ? 1.0 : 0.0;
    }

    private function fitnessGoalsScore(array $fitnessGoals, array $coachExpertise, array $coachExpertiseAreas): float
    {
        $desired = [];

        foreach ($fitnessGoals as $key => $value) {
            if ($value === true || $value === 1 || $value === '1') {
                $desired[] = (string) $key;
            }
        }

        $desired = array_values(array_filter($desired));

        if (count($desired) === 0) {
            return 0.0;
        }

        $offered = array_merge(
            array_values(array_filter(array_map('strval', $coachExpertise))),
            array_values(array_filter(array_map('strval', $coachExpertiseAreas)))
        );

        $intersection = array_intersect($desired, $offered);

        return count($intersection) / count($desired);
    }
}
