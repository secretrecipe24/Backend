<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Coach;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class CoachAuthController extends Controller
{
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $coach = Coach::where('email', $validated['email'])->first();

        if (! $coach || ! Hash::check($validated['password'], (string) $coach->password)) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        if ($coach->status !== 'active') {
            return response()->json(['message' => 'Coach account is inactive.'], 403);
        }

        $token = $coach->createToken('coach')->plainTextToken;

        return response()->json([
            'token' => $token,
            'coach' => $coach,
        ]);
    }

    public function logout(Request $request)
    {
        $token = $request->user()?->currentAccessToken();
        if ($token) {
            $token->delete();
        }

        return response()->json(['message' => 'Logged out.']);
    }

    public function me(Request $request)
    {
        return response()->json([
            'coach' => $request->user(),
        ]);
    }

    public function updateProfile(Request $request)
    {
        /** @var \App\Models\Coach $coach */
        $coach = $request->user();

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'certifications' => ['sometimes', 'nullable', 'string'],
            'expertise' => ['sometimes', 'nullable', 'array'],
            'specialties' => ['sometimes', 'nullable', 'array'],
            'bio' => ['sometimes', 'nullable', 'string'],
            'availability' => ['sometimes', 'nullable', 'array'],
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

        $coach->fill($validated);
        $coach->save();

        return response()->json([
            'coach' => $coach->fresh(),
        ]);
    }
}

