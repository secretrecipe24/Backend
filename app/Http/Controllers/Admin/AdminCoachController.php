<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coach;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AdminCoachController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
            'min_rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'available_only' => ['nullable', 'boolean'],
            'include_deleted' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = Coach::query()->orderByDesc('rating');

        if (($validated['include_deleted'] ?? false) === true) {
            $query->withTrashed();
        }

        if (! empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (! empty($validated['min_rating'])) {
            $query->where('rating', '>=', $validated['min_rating']);
        }

        if (($validated['available_only'] ?? false) === true) {
            $query->whereColumn('current_clients', '<', 'max_clients');
        }

        return response()->json($query->paginate($validated['per_page'] ?? 20));
    }

    public function store(Request $request)
    {
        $admin = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:coaches,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'certifications' => ['nullable', 'string'],
            'expertise' => ['nullable', 'array'],
            'specialties' => ['nullable', 'array'],
            'bio' => ['nullable', 'string'],
            'availability' => ['nullable', 'array'],
            'expertise_areas' => ['nullable', 'array'],
            'coaching_styles' => ['nullable', 'array'],
            'years_experience' => ['nullable', 'integer', 'min:0', 'max:80'],
            'languages' => ['nullable', 'array'],
            'client_demographics' => ['nullable', 'array'],
            'monthly_rate' => ['nullable', 'numeric', 'min:0'],
            'max_clients' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'profile_complete' => ['nullable', 'boolean'],
            'profile_photo' => ['nullable', 'string', 'max:2048'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);

        $coach = Coach::create(array_merge($validated, [
            'created_by' => $admin->id,
            'current_clients' => 0,
            'rating' => 0,
            'status' => $validated['status'] ?? 'active',
        ]));

        return response()->json(['coach' => $coach], 201);
    }

    public function show(int $id)
    {
        $coach = Coach::withTrashed()
            ->withCount(['matches'])
            ->findOrFail($id);

        return response()->json(['coach' => $coach]);
    }

    public function update(Request $request, int $id)
    {
        $coach = Coach::withTrashed()->findOrFail($id);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('coaches', 'email')->ignore($coach->id)],
            'password' => ['sometimes', 'confirmed', Password::defaults()],
            'certifications' => ['sometimes', 'nullable', 'string'],
            'expertise' => ['sometimes', 'nullable', 'array'],
            'specialties' => ['sometimes', 'nullable', 'array'],
            'bio' => ['sometimes', 'nullable', 'string'],
            'availability' => ['sometimes', 'nullable', 'array'],
            'rating' => ['sometimes', 'numeric', 'min:0', 'max:5'],
            'status' => ['sometimes', Rule::in(['active', 'inactive'])],
            'expertise_areas' => ['sometimes', 'nullable', 'array'],
            'coaching_styles' => ['sometimes', 'nullable', 'array'],
            'years_experience' => ['sometimes', 'integer', 'min:0', 'max:80'],
            'languages' => ['sometimes', 'nullable', 'array'],
            'client_demographics' => ['sometimes', 'nullable', 'array'],
            'monthly_rate' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'max_clients' => ['sometimes', 'integer', 'min:1', 'max:10000'],
            'profile_complete' => ['sometimes', 'boolean'],
            'profile_photo' => ['sometimes', 'nullable', 'string', 'max:2048'],
        ]);

        $coach->fill($validated)->save();

        return response()->json(['coach' => $coach->fresh()]);
    }

    public function destroy(int $id)
    {
        $coach = Coach::findOrFail($id);
        $coach->delete();

        return response()->json(['message' => 'Coach deleted.']);
    }

    public function suspend(Request $request, int $id)
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:2000'],
            'until' => ['nullable', 'date'],
        ]);

        $coach = Coach::findOrFail($id);

        $coach->forceFill([
            'status' => 'inactive',
            'suspended_at' => now(),
            'suspension_reason' => $validated['reason'],
        ])->save();

        return response()->json([
            'message' => 'Coach suspended.',
            'coach' => $coach->fresh(),
        ]);
    }
}

