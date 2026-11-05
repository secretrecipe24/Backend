<?php

namespace App\Http\Controllers;

use App\Models\MealLog;
use App\Models\MealPlan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClientMealController extends Controller
{
    public function getMyMealPlans(Request $request)
    {
        $plans = MealPlan::query()
            ->where('client_id', $request->user()->id)
            ->where('status', 'active')
            ->with(['coach'])
            ->orderByDesc('start_date')
            ->get();

        return response()->json(['meal_plans' => $plans]);
    }

    public function getMealPlan(Request $request, int $id)
    {
        $plan = MealPlan::query()
            ->where('client_id', $request->user()->id)
            ->with(['coach'])
            ->findOrFail($id);

        return response()->json(['meal_plan' => $plan]);
    }

    public function logMeal(Request $request)
    {
        $userId = $request->user()->id;

        $validated = $request->validate([
            'meal_plan_id' => ['nullable', 'integer', 'exists:meal_plans,id'],
            'date' => ['required', 'date'],
            'meal_time' => ['nullable', Rule::in(['breakfast', 'lunch', 'dinner', 'snack'])],
            'meals' => ['required', 'array'],
            'food_items' => ['nullable', 'array'],
            'calories' => ['nullable', 'integer', 'min:0', 'max:200000'],
            'protein' => ['nullable', 'numeric', 'min:0', 'max:20000'],
            'carbs' => ['nullable', 'numeric', 'min:0', 'max:20000'],
            'fat' => ['nullable', 'numeric', 'min:0', 'max:20000'],
            'water_intake' => ['nullable', 'numeric', 'min:0', 'max:100000'],
            'hunger_level' => ['nullable', 'integer', 'min:1', 'max:10'],
            'notes' => ['nullable', 'string'],
        ]);

        if (! empty($validated['meal_plan_id'])) {
            MealPlan::query()
                ->where('id', $validated['meal_plan_id'])
                ->where('client_id', $userId)
                ->firstOrFail();
        }

        $log = MealLog::create([
            'client_id' => $userId,
            'meal_plan_id' => $validated['meal_plan_id'] ?? null,
            'date' => $validated['date'],
            'meal_time' => $validated['meal_time'] ?? null,
            'meals' => $validated['meals'],
            'food_items' => $validated['food_items'] ?? null,
            'calories' => $validated['calories'] ?? null,
            'protein' => $validated['protein'] ?? null,
            'carbs' => $validated['carbs'] ?? null,
            'fat' => $validated['fat'] ?? null,
            'water_intake' => $validated['water_intake'] ?? null,
            'hunger_level' => $validated['hunger_level'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json(['meal_log' => $log], 201);
    }

    public function logWater(Request $request)
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'water_intake' => ['required', 'numeric', 'min:0.1', 'max:100000'],
        ]);

        $userId = $request->user()->id;

        $log = MealLog::query()
            ->where('client_id', $userId)
            ->whereDate('date', $validated['date'])
            ->whereNull('meal_time')
            ->orderByDesc('id')
            ->first();

        if (! $log) {
            $log = MealLog::create([
                'client_id' => $userId,
                'meal_plan_id' => null,
                'date' => $validated['date'],
                'meal_time' => null,
                'meals' => [],
                'food_items' => null,
                'calories' => null,
                'protein' => null,
                'carbs' => null,
                'fat' => null,
                'water_intake' => (float) $validated['water_intake'],
                'hunger_level' => null,
                'meal_skipped' => false,
                'notes' => null,
            ]);
        } else {
            $log->water_intake = (float) (($log->water_intake ?? 0) + $validated['water_intake']);
            $log->save();
        }

        return response()->json([
            'message' => 'Water intake logged.',
            'meal_log' => $log->fresh(),
        ], 201);
    }

    public function getMyMealLogs(Request $request)
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'meal_plan_id' => ['nullable', 'integer', 'exists:meal_plans,id'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = MealLog::query()
            ->where('client_id', $request->user()->id)
            ->with(['mealPlan:id,title,coach_id,client_id,status'])
            ->orderByDesc('date')
            ->orderByDesc('id');

        if (! empty($validated['meal_plan_id'])) {
            $query->where('meal_plan_id', $validated['meal_plan_id']);
        }

        if (! empty($validated['from'])) {
            $query->whereDate('date', '>=', $validated['from']);
        }

        if (! empty($validated['to'])) {
            $query->whereDate('date', '<=', $validated['to']);
        }

        return response()->json($query->paginate($validated['per_page'] ?? 20));
    }

    public function getNutritionStats(Request $request)
    {
        $userId = $request->user()->id;

        $logs = MealLog::query()
            ->where('client_id', $userId)
            ->whereDate('date', '>=', now()->subDays(30)->toDateString())
            ->get(['calories', 'protein', 'carbs', 'fat', 'water_intake', 'date']);

        $days = $logs->groupBy(fn (MealLog $l) => (string) $l->getRawOriginal('date'));

        $daily = $days->map(function ($dayLogs, $day) {
            return [
                'date' => $day,
                'calories' => (int) $dayLogs->sum('calories'),
                'protein' => (float) $dayLogs->sum('protein'),
                'carbs' => (float) $dayLogs->sum('carbs'),
                'fat' => (float) $dayLogs->sum('fat'),
                'water_intake' => (float) $dayLogs->sum('water_intake'),
            ];
        })->values();

        $avgCalories = $daily->count() ? $daily->avg('calories') : 0;
        $avgProtein = $daily->count() ? $daily->avg('protein') : 0;
        $avgCarbs = $daily->count() ? $daily->avg('carbs') : 0;
        $avgFat = $daily->count() ? $daily->avg('fat') : 0;

        return response()->json([
            'period_days' => 30,
            'averages' => [
                'calories' => round((float) $avgCalories, 1),
                'protein' => round((float) $avgProtein, 1),
                'carbs' => round((float) $avgCarbs, 1),
                'fat' => round((float) $avgFat, 1),
            ],
            'daily' => $daily,
        ]);
    }
}

