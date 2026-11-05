<?php

namespace App\Http\Controllers;

use App\Models\CheckIn;
use App\Models\UserMatch;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CoachCheckInController extends Controller
{
    public function clientIndex(Request $request)
    {
        return response()->json([
            'data' => CheckIn::query()
                ->with('coach:id,name,email')
                ->where('client_id', $request->user()->id)
                ->orderBy('scheduled_date')
                ->get(),
        ]);
    }

    public function clientStore(Request $request)
    {
        $client = $request->user();
        $match = UserMatch::query()
            ->where('client_id', $client->id)
            ->where('status', 'active')
            ->latest()
            ->firstOrFail();

        $validated = $request->validate([
            'scheduled_date' => ['required', 'date', 'after_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        return response()->json([
            'data' => CheckIn::create([
                ...$validated,
                'coach_id' => $match->coach_id,
                'client_id' => $client->id,
            ])->load('coach:id,name,email'),
        ], 201);
    }

    public function index(Request $request)
    {
        $coach = $request->user();

        return response()->json([
            'data' => CheckIn::query()
                ->with('client:id,name,email')
                ->where('coach_id', $coach->id)
                ->orderBy('scheduled_date')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $coach = $request->user();
        $validated = $request->validate([
            'client_id' => [
                'required',
                'integer',
                Rule::exists('matches', 'client_id')->where(
                    fn ($query) => $query->where('coach_id', $coach->id)->where('status', 'active')
                ),
            ],
            'scheduled_date' => ['required', 'date', 'after_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'action_items' => ['nullable', 'array'],
        ]);

        $checkIn = CheckIn::create([
            ...$validated,
            'coach_id' => $coach->id,
        ]);

        return response()->json(['data' => $checkIn->load('client:id,name,email')], 201);
    }

    public function complete(Request $request, int $id)
    {
        $checkIn = CheckIn::query()
            ->where('coach_id', $request->user()->id)
            ->findOrFail($id);

        $checkIn->update(['completed_date' => now()]);

        return response()->json(['data' => $checkIn->fresh()->load('client:id,name,email')]);
    }
}
