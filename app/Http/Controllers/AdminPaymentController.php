<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminPaymentController extends Controller
{
    private const PLAN_MONTHS = [
        'monthly' => 1,
        'quarterly' => 3,
        'yearly' => 12,
    ];

    public function getPendingVerifications()
    {
        $payments = Payment::query()
            ->where('payment_status', 'pending_verification')
            ->with([
                'user:id,name,email,subscription_tier,subscription_status,subscription_expiry',
                'subscription:id,user_id,plan_type,start_date,end_date,status,amount,gcash_reference_number',
            ])
            ->orderBy('submitted_at', 'asc')
            ->get();

        return response()->json([
            'payments' => $payments,
        ]);
    }

    public function approvePayment(Request $request, int $id)
    {
        $validated = $request->validate([
            'admin_notes' => ['nullable', 'string'],
        ]);

        $admin = $request->user();

        return DB::transaction(function () use ($admin, $validated, $id) {
            /** @var \App\Models\Payment $payment */
            $payment = Payment::query()
                ->lockForUpdate()
                ->with(['subscription', 'user'])
                ->findOrFail($id);

            if ($payment->payment_status !== 'pending_verification') {
                return response()->json(['message' => 'Payment is not pending verification.'], 409);
            }

            $planType = $payment->subscription?->plan_type;
            if (! $planType || ! isset(self::PLAN_MONTHS[$planType])) {
                return response()->json(['message' => 'Invalid or missing subscription plan type.'], 422);
            }

            $start = now();
            $end = $start->copy()->addMonthsNoOverflow(self::PLAN_MONTHS[$planType]);

            /** @var \App\Models\Subscription $subscription */
            $subscription = $payment->subscription ?? Subscription::create([
                'user_id' => $payment->user_id,
                'plan_type' => $planType,
                'start_date' => $start,
                'end_date' => $end,
                'status' => 'active',
                'amount' => $payment->amount,
                'payment_method' => $payment->payment_method ?? 'gcash_manual',
                'gcash_reference_number' => $payment->gcash_reference_number,
                'verified_at' => now(),
                'verified_by' => $admin->id,
            ]);

            // Update subscription record to active + verified
            $subscription->forceFill([
                'start_date' => $start,
                'end_date' => $end,
                'status' => 'active',
                'amount' => $subscription->amount ?? $payment->amount,
                'payment_method' => $subscription->payment_method ?? ($payment->payment_method ?? 'gcash_manual'),
                'gcash_reference_number' => $subscription->gcash_reference_number ?? $payment->gcash_reference_number,
                'verified_by' => $admin->id,
                'verified_at' => now(),
            ])->save();

            // Update payment to completed + verified
            $payment->forceFill([
                'payment_status' => 'completed',
                'verified_by' => $admin->id,
                'verified_at' => now(),
                'admin_notes' => $validated['admin_notes'] ?? $payment->admin_notes,
            ])->save();

            /** @var \App\Models\User $user */
            $user = $payment->user;
            $user->forceFill([
                'subscription_tier' => 'premium',
                'subscription_status' => 'active',
                'subscription_expiry' => $end,
            ])->save();

            return response()->json([
                'message' => 'Payment approved and subscription activated.',
                'payment' => $payment->fresh(['user', 'subscription']),
            ]);
        });
    }

    public function rejectPayment(Request $request, int $id)
    {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:2000'],
        ]);

        $admin = $request->user();

        /** @var \App\Models\Payment $payment */
        $payment = Payment::query()->findOrFail($id);

        if ($payment->payment_status !== 'pending_verification') {
            return response()->json(['message' => 'Payment is not pending verification.'], 409);
        }

        $payment->forceFill([
            'payment_status' => 'rejected',
            'verified_by' => $admin->id,
            'verified_at' => now(),
            'admin_notes' => $validated['rejection_reason'],
        ])->save();

        return response()->json([
            'message' => 'Payment rejected.',
            'payment' => $payment->fresh(),
        ]);
    }

    public function getVerifiedHistory(Request $request)
    {
        $validated = $request->validate([
            'status' => ['nullable', Rule::in(['pending_verification', 'completed', 'failed', 'rejected'])],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $perPage = $validated['per_page'] ?? 20;

        $query = Payment::query()
            ->with([
                'user:id,name,email',
                'subscription:id,user_id,plan_type,start_date,end_date,status,amount',
                'verifier:id,name,email,role',
            ])
            ->orderByDesc('submitted_at');

        if (! empty($validated['status'])) {
            $query->where('payment_status', $validated['status']);
        }

        if (! empty($validated['user_id'])) {
            $query->where('user_id', $validated['user_id']);
        }

        if (! empty($validated['from'])) {
            $query->whereDate('submitted_at', '>=', $validated['from']);
        }

        if (! empty($validated['to'])) {
            $query->whereDate('submitted_at', '<=', $validated['to']);
        }

        return response()->json($query->paginate($perPage));
    }

    public function manualCreateSubscription(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'plan_type' => ['required', Rule::in(array_keys(self::PLAN_MONTHS))],
            'gcash_reference_number' => [
                'required',
                'string',
                'max:255',
                'unique:payments,gcash_reference_number',
                'unique:subscriptions,gcash_reference_number',
            ],
            'sender_name' => ['required', 'string', 'max:255'],
            'sender_number' => ['required', 'string', 'max:50'],
            'amount' => ['required', 'numeric', 'min:0'],
            'admin_notes' => ['nullable', 'string'],
        ]);

        $admin = $request->user();

        return DB::transaction(function () use ($admin, $validated) {
            /** @var \App\Models\User $user */
            $user = User::query()->lockForUpdate()->findOrFail($validated['user_id']);

            $start = now();
            $end = $start->copy()->addMonthsNoOverflow(self::PLAN_MONTHS[$validated['plan_type']]);

            $subscription = Subscription::create([
                'user_id' => $user->id,
                'plan_type' => $validated['plan_type'],
                'start_date' => $start,
                'end_date' => $end,
                'status' => 'active',
                'amount' => $validated['amount'],
                'payment_method' => 'gcash_manual',
                'gcash_reference_number' => $validated['gcash_reference_number'],
                'verified_at' => now(),
                'verified_by' => $admin->id,
            ]);

            $payment = Payment::create([
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'amount' => $validated['amount'],
                'payment_date' => now(),
                'payment_status' => 'completed',
                'transaction_reference' => 'manual-'.$validated['gcash_reference_number'],
                'payment_method' => 'gcash_manual',
                'gcash_reference_number' => $validated['gcash_reference_number'],
                'gcash_sender_name' => $validated['sender_name'],
                'gcash_sender_number' => $validated['sender_number'],
                'submitted_at' => now(),
                'verified_by' => $admin->id,
                'verified_at' => now(),
                'admin_notes' => $validated['admin_notes'] ?? null,
            ]);

            $user->forceFill([
                'subscription_tier' => 'premium',
                'subscription_status' => 'active',
                'subscription_expiry' => $end,
            ])->save();

            return response()->json([
                'message' => 'Subscription created.',
                'subscription' => $subscription,
                'payment' => $payment,
            ], 201);
        });
    }

    public function getDashboardStats()
    {
        $pending = Payment::query()
            ->where('payment_status', 'pending_verification')
            ->count();

        $revenueToday = Payment::query()
            ->where('payment_status', 'completed')
            ->whereDate('verified_at', now()->toDateString())
            ->sum('amount');

        $revenueThisMonth = Payment::query()
            ->where('payment_status', 'completed')
            ->whereYear('verified_at', now()->year)
            ->whereMonth('verified_at', now()->month)
            ->sum('amount');

        $revenueAllTime = Payment::query()
            ->where('payment_status', 'completed')
            ->sum('amount');

        $activeSubscriptions = Subscription::query()
            ->where('status', 'active')
            ->where('end_date', '>=', now())
            ->count();

        return response()->json([
            'pending_verifications' => $pending,
            'revenue_today' => (float) $revenueToday,
            'revenue_this_month' => (float) $revenueThisMonth,
            'revenue_all_time' => (float) $revenueAllTime,
            'active_subscriptions' => $activeSubscriptions,
        ]);
    }
}

