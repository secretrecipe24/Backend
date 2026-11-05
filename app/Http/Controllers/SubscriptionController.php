<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    private const FREE_ACCESS = [
        'label' => 'Free access',
        'price' => 0,
        'duration_months' => null,
        'features' => [
            'Default workout programs',
            'Default meal plans',
            'Progress tracking',
            'Coach matching',
        ],
    ];

    private const DEFAULT_PROGRAMS = [
        [
            'key' => 'workouts',
            'label' => 'Starter workout program',
            'description' => 'Use the default workout plan as your starting point, then track progress over time.',
            'action' => 'Open workouts',
            'href' => '/client/workouts',
        ],
        [
            'key' => 'meals',
            'label' => 'Starter meal guide',
            'description' => 'Use the default meal plan as your starting point for simple, repeatable nutrition.',
            'action' => 'Open meals',
            'href' => '/client/meals',
        ],
    ];

    public function getPlans()
    {
        return response()->json([
            'currency' => 'PHP',
            'plans' => [
                'free' => self::FREE_ACCESS,
            ],
            'defaults' => self::DEFAULT_PROGRAMS,
        ]);
    }

    public function submitPayment(Request $request)
    {
        return response()->json([
            'message' => 'Subscriptions are disabled. All client features are free now.',
        ], 410);
    }

    public function getMySubmissions(Request $request)
    {
        return response()->json([
            'payments' => [],
        ]);
    }

    public function getSubscriptionStatus(Request $request)
    {
        return response()->json([
            'user_subscription_status' => 'active',
            'user_subscription_tier' => 'free',
            'user_subscription_expiry' => null,
            'active_subscription' => null,
            'latest_payment' => null,
        ]);
    }
}

