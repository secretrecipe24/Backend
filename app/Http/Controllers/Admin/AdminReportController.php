<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coach;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\Rule;

class AdminReportController extends Controller
{
    public function userReport(Request $request)
    {
        $validated = $request->validate([
            'days' => ['nullable', 'integer', 'min:7', 'max:3650'],
        ]);

        $days = $validated['days'] ?? 365;
        $from = now()->subDays($days - 1)->startOfDay();

        $users = User::query()
            ->whereDate('created_at', '>=', $from->toDateString())
            ->get(['created_at', 'subscription_status']);

        $byDay = [];
        foreach ($users as $u) {
            $day = $u->created_at->toDateString();
            $byDay[$day] = ($byDay[$day] ?? 0) + 1;
        }

        $active = User::query()->where('subscription_status', 'active')->count();
        $expired = User::query()->where('subscription_status', 'expired')->count();
        $inactive = User::query()->where('subscription_status', 'inactive')->count();

        $total = max(1, $active + $expired + $inactive);

        return response()->json([
            'period_days' => $days,
            'growth_by_day' => collect($byDay)->map(fn ($c, $d) => ['date' => $d, 'new_users' => $c])->values(),
            'status_breakdown' => [
                'active' => $active,
                'inactive' => $inactive,
                'expired' => $expired,
            ],
            // Simple approximations (definitions can be refined later):
            'retention_rate_estimate' => round($active / $total, 3),
            'churn_rate_estimate' => round($expired / $total, 3),
        ]);
    }

    public function revenueReport(Request $request)
    {
        $validated = $request->validate([
            'days' => ['nullable', 'integer', 'min:7', 'max:3650'],
        ]);

        $days = $validated['days'] ?? 365;
        $from = now()->subDays($days - 1)->startOfDay();

        $payments = Payment::query()
            ->where('payment_status', 'completed')
            ->whereDate('verified_at', '>=', $from->toDateString())
            ->get(['amount', 'verified_at']);

        $daily = [];
        $monthly = [];
        $yearly = [];

        foreach ($payments as $p) {
            if (! $p->verified_at) {
                continue;
            }
            $d = $p->verified_at->toDateString();
            $m = $p->verified_at->format('Y-m');
            $y = $p->verified_at->format('Y');

            $daily[$d] = ($daily[$d] ?? 0) + (float) $p->amount;
            $monthly[$m] = ($monthly[$m] ?? 0) + (float) $p->amount;
            $yearly[$y] = ($yearly[$y] ?? 0) + (float) $p->amount;
        }

        return response()->json([
            'period_days' => $days,
            'daily' => collect($daily)->map(fn ($v, $k) => ['date' => $k, 'revenue' => round($v, 2)])->values(),
            'monthly' => collect($monthly)->map(fn ($v, $k) => ['month' => $k, 'revenue' => round($v, 2)])->values(),
            'yearly' => collect($yearly)->map(fn ($v, $k) => ['year' => $k, 'revenue' => round($v, 2)])->values(),
        ]);
    }

    public function coachPerformanceReport(Request $request)
    {
        $validated = $request->validate([
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $limit = $validated['limit'] ?? 20;

        $coaches = Coach::query()
            ->withCount([
                'matches as active_clients_count' => function ($q) {
                    $q->where('status', 'active');
                },
            ])
            ->orderByDesc('active_clients_count')
            ->orderByDesc('rating')
            ->limit($limit)
            ->get(['id', 'name', 'email', 'rating', 'years_experience', 'monthly_rate', 'current_clients', 'max_clients', 'status']);

        return response()->json([
            'top_coaches' => $coaches,
        ]);
    }

    public function exportReport(Request $request, string $type)
    {
        $validated = $request->validate([
            'format' => ['nullable', Rule::in(['csv', 'pdf'])],
        ]);

        $format = $validated['format'] ?? 'csv';

        if ($format === 'pdf') {
            return response()->json(['message' => 'PDF export not implemented yet.'], 501);
        }

        $rows = match ($type) {
            'users' => User::query()->orderByDesc('created_at')->limit(5000)->get(['id', 'name', 'email', 'subscription_tier', 'subscription_status', 'subscription_expiry', 'created_at'])->toArray(),
            'revenue' => Payment::query()->where('payment_status', 'completed')->orderByDesc('verified_at')->limit(5000)->get(['id', 'user_id', 'amount', 'gcash_reference_number', 'verified_at'])->toArray(),
            'coaches' => Coach::query()->orderByDesc('rating')->limit(5000)->get(['id', 'name', 'email', 'rating', 'years_experience', 'monthly_rate', 'status'])->toArray(),
            default => null,
        };

        if ($rows === null) {
            return response()->json(['message' => 'Invalid report type.'], 422);
        }

        $csv = $this->toCsv($rows);

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"report-{$type}.csv\"",
        ]);
    }

    private function toCsv(array $rows): string
    {
        if (count($rows) === 0) {
            return '';
        }

        $out = fopen('php://temp', 'r+');
        fputcsv($out, array_keys($rows[0]));
        foreach ($rows as $row) {
            fputcsv($out, $row);
        }
        rewind($out);
        return stream_get_contents($out) ?: '';
    }
}

