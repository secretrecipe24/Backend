<?php

namespace App\Http\Controllers;

use App\Models\SleepLog;
use Illuminate\Http\Request;

class ClientSleepController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'bedtime' => ['nullable', 'date_format:H:i'],
            'wake_time' => ['nullable', 'date_format:H:i'],
            'duration_minutes' => ['required', 'integer', 'min:0', 'max:1440'],
            'quality' => ['required', 'integer', 'min:1', 'max:5'],
            'interruptions' => ['nullable', 'integer', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $attributes = array_merge($validated, [
            'client_id' => $request->user()->id,
            'interruptions' => $validated['interruptions'] ?? 0,
        ]);

        $log = SleepLog::query()
            ->where('client_id', $request->user()->id)
            ->whereDate('date', $validated['date'])
            ->first();

        if ($log) {
            $log->fill($attributes)->save();
        } else {
            $log = SleepLog::create($attributes);
        }

        return response()->json(['sleep_log' => $log], 201);
    }

    public function index(Request $request)
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = SleepLog::query()
            ->where('client_id', $request->user()->id)
            ->orderByDesc('date');

        if (! empty($validated['from'])) $query->whereDate('date', '>=', $validated['from']);
        if (! empty($validated['to'])) $query->whereDate('date', '<=', $validated['to']);

        return response()->json($query->paginate($validated['per_page'] ?? 30));
    }

    public function summary(Request $request)
    {
        $logs = SleepLog::query()
            ->where('client_id', $request->user()->id)
            ->whereDate('date', '>=', now()->subDays(29)->toDateString())
            ->orderBy('date')
            ->get();

        return response()->json([
            'days' => 30,
            'logged_days' => $logs->count(),
            'average_duration_minutes' => $logs->count() ? (int) round($logs->avg('duration_minutes')) : null,
            'average_quality' => $logs->count() ? round($logs->avg('quality'), 1) : null,
            'average_interruptions' => $logs->count() ? round($logs->avg('interruptions'), 1) : null,
            'chart' => $logs->map(fn (SleepLog $log) => [
                'date' => $log->date->toDateString(),
                'duration_minutes' => $log->duration_minutes,
                'quality' => $log->quality,
            ])->values(),
        ]);
    }
}
