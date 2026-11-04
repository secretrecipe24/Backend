<?php

namespace App\Http\Controllers;

use App\Models\Measurement;
use App\Models\ProgressPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ClientProgressController extends Controller
{
    public function getMyMeasurements(Request $request)
    {
        $measurements = Measurement::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('measurement_date')
            ->get();

        return response()->json(['measurements' => $measurements]);
    }

    public function storeMeasurement(Request $request)
    {
        $userId = $request->user()->id;

        $validated = $request->validate([
            'measurement_date' => ['required', 'date'],
            'weight' => ['required', 'numeric', 'min:0', 'max:2000'],
            'height' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'bmi' => ['nullable', 'numeric', 'min:0', 'max:200'],
            'body_fat_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'chest' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'waist' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'hips' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'thighs' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'arms' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'forearms' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'calves' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'neck' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'shoulders' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'strength_records' => ['nullable', 'array'],
            'notes' => ['nullable', 'string'],
        ]);

        $height = $validated['height'] ?? null;
        $weight = $validated['weight'];

        if (! isset($validated['bmi']) && $height && $height > 0) {
            $meters = $height / 100;
            $validated['bmi'] = round($weight / ($meters * $meters), 2);
        }

        $measurement = Measurement::query()
            ->where('user_id', $userId)
            ->whereDate('measurement_date', $validated['measurement_date'])
            ->first();

        if ($measurement) {
            $measurement->fill($validated)->save();
        } else {
            $measurement = Measurement::create(array_merge($validated, ['user_id' => $userId]));
        }

        return response()->json(['measurement' => $measurement], 201);
    }

    public function updateMeasurement(Request $request, int $id)
    {
        $userId = $request->user()->id;

        $measurement = Measurement::query()
            ->where('user_id', $userId)
            ->findOrFail($id);

        $validated = $request->validate([
            'measurement_date' => ['sometimes', 'date'],
            'weight' => ['sometimes', 'numeric', 'min:0', 'max:2000'],
            'height' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:300'],
            'bmi' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:200'],
            'body_fat_percentage' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100'],
            'chest' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:300'],
            'waist' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:300'],
            'hips' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:300'],
            'thighs' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:300'],
            'arms' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:300'],
            'forearms' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:300'],
            'calves' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:300'],
            'neck' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:300'],
            'shoulders' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:300'],
            'strength_records' => ['sometimes', 'nullable', 'array'],
            'notes' => ['sometimes', 'nullable', 'string'],
        ]);

        $measurement->fill($validated)->save();

        return response()->json(['measurement' => $measurement->fresh()]);
    }

    public function uploadProgressPhoto(Request $request)
    {
        $userId = $request->user()->id;

        $validated = $request->validate([
            'photo' => ['required', 'image', 'max:4096'],
            'photo_type' => ['required', Rule::in(['front', 'side', 'back'])],
            'photo_date' => ['required', 'date'],
            'weight' => ['nullable', 'numeric', 'min:0', 'max:2000'],
            'notes' => ['nullable', 'string'],
            'is_private' => ['nullable', 'boolean'],
        ]);

        $path = $request->file('photo')->store('progress_photos', 'public');

        $photo = ProgressPhoto::create([
            'user_id' => $userId,
            'coach_id' => null,
            'photo_path' => $path,
            'photo_type' => $validated['photo_type'],
            'photo_date' => $validated['photo_date'],
            'weight' => $validated['weight'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'is_private' => $validated['is_private'] ?? true,
            'is_approved' => false,
        ]);

        return response()->json(['progress_photo' => $photo], 201);
    }

    public function getMyProgressPhotos(Request $request)
    {
        $photos = ProgressPhoto::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('photo_date')
            ->orderByDesc('id')
            ->get();

        return response()->json(['progress_photos' => $photos]);
    }

    public function getProgressSummary(Request $request)
    {
        $userId = $request->user()->id;

        $first = Measurement::query()
            ->where('user_id', $userId)
            ->orderBy('measurement_date')
            ->first();

        $latest = Measurement::query()
            ->where('user_id', $userId)
            ->orderByDesc('measurement_date')
            ->first();

        $weightChange = null;
        if ($first && $latest) {
            $weightChange = round((float) $latest->weight - (float) $first->weight, 2);
        }

        $photosCount = ProgressPhoto::query()->where('user_id', $userId)->count();

        return response()->json([
            'first_measurement' => $first,
            'latest_measurement' => $latest,
            'weight_change' => $weightChange,
            'goal_weight' => $request->user()->goal_weight,
            'photos_count' => $photosCount,
        ]);
    }
}

