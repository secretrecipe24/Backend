<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'subscription_status' => ['nullable', Rule::in(['active', 'inactive', 'expired'])],
            'subscription_tier' => ['nullable', Rule::in(['free', 'premium'])],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'include_deleted' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = User::query()->orderByDesc('created_at');

        if (($validated['include_deleted'] ?? false) === true) {
            $query->withTrashed();
        }

        if (! empty($validated['subscription_status'])) {
            $query->where('subscription_status', $validated['subscription_status']);
        }

        if (! empty($validated['subscription_tier'])) {
            $query->where('subscription_tier', $validated['subscription_tier']);
        }

        if (! empty($validated['from'])) {
            $query->whereDate('created_at', '>=', $validated['from']);
        }
        if (! empty($validated['to'])) {
            $query->whereDate('created_at', '<=', $validated['to']);
        }

        return response()->json($query->paginate($validated['per_page'] ?? 20));
    }

    public function show(int $id)
    {
        $user = User::withTrashed()
            ->with([
                'subscriptions' => fn ($q) => $q->orderByDesc('end_date'),
                'payments' => fn ($q) => $q->orderByDesc('submitted_at'),
                'matchesAsClient' => fn ($q) => $q->with('coach')->orderByDesc('created_at'),
                'preferences',
            ])
            ->findOrFail($id);

        return response()->json(['user' => $user]);
    }

    public function update(Request $request, int $id)
    {
        $user = User::withTrashed()->findOrFail($id);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'age' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:120'],
            'gender' => ['sometimes', 'nullable', Rule::in(['male', 'female', 'other', 'prefer-not-to-say'])],
            'fitness_goals' => ['sometimes', 'nullable', 'array'],
            'dietary_preferences' => ['sometimes', 'nullable', 'array'],
            'restrictions' => ['sometimes', 'nullable', 'string'],
            'dark_mode' => ['sometimes', 'boolean'],
        ]);

        $user->fill($validated)->save();

        return response()->json(['user' => $user->fresh()]);
    }

    public function destroy(int $id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'User deleted.']);
    }

    public function restore(int $id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();

        return response()->json(['message' => 'User restored.', 'user' => $user->fresh()]);
    }

    public function updateSubscription(Request $request, int $id)
    {
        $user = User::withTrashed()->findOrFail($id);

        $validated = $request->validate([
            'subscription_tier' => ['required', Rule::in(['free', 'premium'])],
            'subscription_status' => ['required', Rule::in(['active', 'inactive', 'expired'])],
            'subscription_expiry' => ['nullable', 'date'],
        ]);

        $user->forceFill([
            'subscription_tier' => $validated['subscription_tier'],
            'subscription_status' => $validated['subscription_status'],
            'subscription_expiry' => $validated['subscription_expiry'] ?? null,
        ])->save();

        return response()->json(['message' => 'Subscription updated.', 'user' => $user->fresh()]);
    }
}

