<?php

namespace App\Http\Controllers;

use App\Models\WorkoutLog;
use App\Models\WorkoutPlan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClientWorkoutController extends Controller
{
    public function getMyWorkoutPlans(Request $request)
    {
        $userId = $request->user()->id;

        $plans = WorkoutPlan::query()
            ->where('client_id', $userId)
            ->where('status', 'active')
            ->withCount('logs')
            ->orderByDesc('start_date')
            ->get()
            ->map(function (WorkoutPlan $plan) {
                return [
                    'plan' => $plan,
                    'progress' => [
                        'logs_count' => $plan->logs_count,
                        'completed' => $plan->status === 'completed',
                    ],
                ];
            });

        return response()->json(['workout_plans' => $plans]);
    }

    public function getWorkoutPlan(Request $request, int $id)
    {
        $plan = WorkoutPlan::query()
            ->where('client_id', $request->user()->id)
            ->with(['coach'])
            ->findOrFail($id);

        return response()->json(['workout_plan' => $plan]);
    }

    public function logWorkout(Request $request)
    {
        $userId = $request->user()->id;

        $validated = $request->validate([
            'workout_plan_id' => ['nullable', 'integer', 'exists:workout_plans,id'],
            'date' => ['required', 'date'],
            'exercises' => ['required', 'array'],
            'performance' => ['nullable', 'array'],
            'duration' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'calories_burned' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'mood' => ['nullable', Rule::in(['great', 'good', 'okay', 'tired', 'exhausted'])],
            'fatigue_level' => ['nullable', 'integer', 'min:1', 'max:10'],
            'notes' => ['nullable', 'string'],
        ]);

        if (! empty($validated['workout_plan_id'])) {
            WorkoutPlan::query()
                ->where('id', $validated['workout_plan_id'])
                ->where('client_id', $userId)
                ->firstOrFail();
        }

        $log = WorkoutLog::create([
            'client_id' => $userId,
            'workout_plan_id' => $validated['workout_plan_id'] ?? null,
            'date' => $validated['date'],
            'exercises' => $validated['exercises'],
            'performance' => $validated['performance'] ?? null,
            'duration' => $validated['duration'] ?? null,
            'calories_burned' => $validated['calories_burned'] ?? null,
            'mood' => $validated['mood'] ?? null,
            'fatigue_level' => $validated['fatigue_level'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json(['workout_log' => $log], 201);
    }

    public function getMyWorkoutLogs(Request $request)
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'workout_plan_id' => ['nullable', 'integer', 'exists:workout_plans,id'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = WorkoutLog::query()
            ->where('client_id', $request->user()->id)
            ->with(['workoutPlan:id,title,coach_id,client_id,status'])
            ->orderByDesc('date');

        if (! empty($validated['workout_plan_id'])) {
            $query->where('workout_plan_id', $validated['workout_plan_id']);
        }

        if (! empty($validated['from'])) {
            $query->whereDate('date', '>=', $validated['from']);
        }

        if (! empty($validated['to'])) {
            $query->whereDate('date', '<=', $validated['to']);
        }

        return response()->json($query->paginate($validated['per_page'] ?? 20));
    }

    public function getWorkoutStats(Request $request)
    {
        $userId = $request->user()->id;

        $totalWorkouts = WorkoutLog::query()->where('client_id', $userId)->count();
        $totalDuration = (int) WorkoutLog::query()->where('client_id', $userId)->sum('duration');
        $totalCalories = (int) WorkoutLog::query()->where('client_id', $userId)->sum('calories_burned');

        $last30Days = WorkoutLog::query()
            ->where('client_id', $userId)
            ->whereDate('date', '>=', now()->subDays(30)->toDateString())
            ->count();

        $logs = WorkoutLog::query()
            ->where('client_id', $userId)
            ->whereDate('date', '>=', now()->subDays(90)->toDateString())
            ->orderBy('date')
            ->get(['date']);

        $weekly = [];
        foreach ($logs as $log) {
            $weekKey = $log->date->format('o-\WW');
            $weekly[$weekKey] = ($weekly[$weekKey] ?? 0) + 1;
        }

        $byWeek = collect($weekly)->map(fn ($count, $week) => [
            'week' => $week,
            'workouts' => $count,
        ])->values();

        return response()->json([
            'totals' => [
                'workouts' => $totalWorkouts,
                'duration_minutes' => $totalDuration,
                'calories_burned' => $totalCalories,
            ],
            'last_30_days' => [
                'workouts' => $last30Days,
            ],
            'trends' => [
                'workouts_by_week' => $byWeek,
            ],
        ]);
    }
}

