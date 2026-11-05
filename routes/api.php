<?php

/**
 * API Routes (role-based)
 *
 * Standard response envelope is applied via `api.response` middleware:
 * { success, data, message, errors }
 */

use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\CoachAuthController;
use App\Http\Controllers\Auth\UserAuthController;
use App\Http\Controllers\AdminPaymentController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\CoachPlanController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\ClientWorkoutController;
use App\Http\Controllers\ClientMealController;
use App\Http\Controllers\ClientSleepController;
use App\Http\Controllers\ClientProgressController;
use App\Http\Controllers\CoachMonitoringController;
use App\Http\Controllers\CoachCheckInController;
use App\Http\Controllers\MessageController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminCoachController;
use App\Http\Controllers\Admin\AdminContentController;
use App\Http\Controllers\Admin\AdminReportController;

Route::get('auth/google', [UserAuthController::class, 'redirectToGoogle']);
Route::get('auth/google/callback', [UserAuthController::class, 'handleGoogleCallback']);

Route::middleware(['api.response'])->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Public Routes (no auth)
    |--------------------------------------------------------------------------
    */
    Route::prefix('user')->group(function () {
        Route::post('register', [UserAuthController::class, 'register']);
        Route::post('login', [UserAuthController::class, 'login']);
        Route::post('forgot-password', [UserAuthController::class, 'forgotPassword']);
        Route::post('forgot-password/send-code', [UserAuthController::class, 'sendPasswordResetCode']);
        Route::post('forgot-password/verify-code', [UserAuthController::class, 'verifyPasswordResetCode']);
        Route::post('reset-password', [UserAuthController::class, 'resetPassword']);
    });

    Route::prefix('coach')->group(function () {
        Route::post('login', [CoachAuthController::class, 'login']);
    });

    Route::prefix('admin')->group(function () {
        Route::post('login', [AdminAuthController::class, 'login']);
    });

    Route::prefix('subscriptions')->group(function () {
        Route::get('plans', [SubscriptionController::class, 'getPlans']);
    });

    Route::get('coaches/featured', [MatchController::class, 'getAllCoaches']);

    /*
    |--------------------------------------------------------------------------
    | User Routes (auth:sanctum)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::prefix('user')->group(function () {
            Route::post('logout', [UserAuthController::class, 'logout']);
            Route::get('me', [UserAuthController::class, 'me']);
            Route::put('profile', [UserAuthController::class, 'updateProfile']);
        });

        Route::prefix('subscriptions')->group(function () {
            Route::post('submit-payment', [SubscriptionController::class, 'submitPayment']);
            Route::get('my-submissions', [SubscriptionController::class, 'getMySubmissions']);
            Route::get('status', [SubscriptionController::class, 'getSubscriptionStatus']);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Client Routes (auth:sanctum)
    |--------------------------------------------------------------------------
    |
    | All client features are available without a paid subscription.
    */
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::prefix('matches')->group(function () {
            Route::post('preferences', [MatchController::class, 'savePreferences']);
            Route::get('suggestions', [MatchController::class, 'getSuggestions']);
            Route::get('coaches', [MatchController::class, 'getAllCoaches']);
            Route::get('coaches/{id}', [MatchController::class, 'getCoachDetails']);
            Route::post('select', [MatchController::class, 'selectCoach']);
            Route::post('replace', [MatchController::class, 'replaceCoach']);
            Route::get('my', [MatchController::class, 'getMyMatch']);
            Route::post('my/rating', [MatchController::class, 'rateCoach']);
        });

        Route::prefix('client/messages')->group(function () {
            Route::get('/', [MessageController::class, 'clientConversation']);
            Route::post('/', [MessageController::class, 'sendToCoach']);
        });

        Route::prefix('client')->group(function () {
            Route::get('sessions', [CoachCheckInController::class, 'clientIndex']);
            Route::post('sessions', [CoachCheckInController::class, 'clientStore']);
            Route::prefix('workouts')->group(function () {
                Route::get('plans', [ClientWorkoutController::class, 'getMyWorkoutPlans']);
                Route::get('plans/{id}', [ClientWorkoutController::class, 'getWorkoutPlan']);
                Route::post('logs', [ClientWorkoutController::class, 'logWorkout']);
                Route::get('logs', [ClientWorkoutController::class, 'getMyWorkoutLogs']);
                Route::get('stats', [ClientWorkoutController::class, 'getWorkoutStats']);
            });

            Route::prefix('meals')->group(function () {
                Route::get('plans', [ClientMealController::class, 'getMyMealPlans']);
                Route::get('plans/{id}', [ClientMealController::class, 'getMealPlan']);
                Route::post('logs', [ClientMealController::class, 'logMeal']);
                Route::post('water', [ClientMealController::class, 'logWater']);
                Route::get('logs', [ClientMealController::class, 'getMyMealLogs']);
                Route::get('stats', [ClientMealController::class, 'getNutritionStats']);
            });

            Route::prefix('progress')->group(function () {
                Route::get('measurements', [ClientProgressController::class, 'getMyMeasurements']);
                Route::post('measurements', [ClientProgressController::class, 'storeMeasurement']);
                Route::put('measurements/{id}', [ClientProgressController::class, 'updateMeasurement']);
                Route::post('photos', [ClientProgressController::class, 'uploadProgressPhoto']);
                Route::get('photos', [ClientProgressController::class, 'getMyProgressPhotos']);
                Route::get('summary', [ClientProgressController::class, 'getProgressSummary']);
            });
            Route::prefix('sleep')->group(function () {
                Route::post('logs', [ClientSleepController::class, 'store']);
                Route::get('logs', [ClientSleepController::class, 'index']);
                Route::get('summary', [ClientSleepController::class, 'summary']);
            });
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Coach Routes (auth:sanctum + coach)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:sanctum', 'coach'])->group(function () {
        Route::prefix('coach')->group(function () {
            Route::post('logout', [CoachAuthController::class, 'logout']);
            Route::get('me', [CoachAuthController::class, 'me']);
            Route::put('profile', [CoachAuthController::class, 'updateProfile']);

            Route::prefix('monitoring')->group(function () {
                Route::get('clients/{clientId}/overview', [CoachMonitoringController::class, 'getClientProgress']);
                Route::get('clients/{clientId}/workout-adherence', [CoachMonitoringController::class, 'getClientWorkoutAdherence']);
                Route::get('clients/{clientId}/meal-adherence', [CoachMonitoringController::class, 'getClientMealAdherence']);
                Route::get('clients/{clientId}/measurements', [CoachMonitoringController::class, 'getClientMeasurements']);
                Route::get('clients/{clientId}/photos', [CoachMonitoringController::class, 'getClientPhotos']);
                Route::get('clients/{clientId}/logs/{type}', [CoachMonitoringController::class, 'getClientLogs']);
                Route::post('clients/{clientId}/notes', [CoachMonitoringController::class, 'addCoachNote']);
            });

            Route::prefix('plans')->group(function () {
                Route::get('clients', [CoachPlanController::class, 'getMyClients']);
                Route::get('upcoming-schedules', [CoachPlanController::class, 'getUpcomingSchedules']);
                Route::get('clients/{clientId}/workouts', [CoachPlanController::class, 'getClientWorkoutPlans']);
                Route::get('clients/{clientId}/meals', [CoachPlanController::class, 'getClientMealPlans']);
                Route::get('templates/{type}', [CoachPlanController::class, 'getPlanTemplates']);

                Route::post('workouts', [CoachPlanController::class, 'storeWorkoutPlan']);
                Route::put('workouts/{id}', [CoachPlanController::class, 'updateWorkoutPlan']);

                Route::post('meals', [CoachPlanController::class, 'storeMealPlan']);
                Route::put('meals/{id}', [CoachPlanController::class, 'updateMealPlan']);

                Route::post('{type}/{id}/activate', [CoachPlanController::class, 'activatePlan']);
                Route::delete('{type}/{id}', [CoachPlanController::class, 'deletePlan']);

                Route::get('library/exercises', [CoachPlanController::class, 'getExerciseLibrary']);
                Route::get('library/foods', [CoachPlanController::class, 'getFoodLibrary']);
            });

            Route::prefix('messages')->group(function () {
                Route::get('/', [MessageController::class, 'coachConversations']);
                Route::get('{clientId}', [MessageController::class, 'coachConversation']);
                Route::post('{clientId}', [MessageController::class, 'sendToClient']);
            });

            Route::prefix('check-ins')->group(function () {
                Route::get('/', [CoachCheckInController::class, 'index']);
                Route::post('/', [CoachCheckInController::class, 'store']);
                Route::post('{id}/complete', [CoachCheckInController::class, 'complete']);
            });

            Route::prefix('matches')->group(function () {
                Route::post('{matchId}/accept', [CoachPlanController::class, 'acceptMatch']);
                Route::post('{matchId}/reject', [CoachPlanController::class, 'rejectMatch']);
            });
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Admin Routes (auth:sanctum + admin)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::prefix('admin')->group(function () {
            Route::post('logout', [AdminAuthController::class, 'logout']);
            Route::get('me', [AdminAuthController::class, 'me']);

            Route::prefix('payments')->group(function () {
                Route::get('pending', [AdminPaymentController::class, 'getPendingVerifications']);
                Route::post('{id}/approve', [AdminPaymentController::class, 'approvePayment']);
                Route::post('{id}/reject', [AdminPaymentController::class, 'rejectPayment']);
                Route::get('history', [AdminPaymentController::class, 'getVerifiedHistory']);
                Route::post('manual-subscription', [AdminPaymentController::class, 'manualCreateSubscription']);
                Route::get('stats', [AdminPaymentController::class, 'getDashboardStats']);
            });

            Route::prefix('users')->group(function () {
                Route::get('/', [AdminUserController::class, 'index']);
                Route::get('{id}', [AdminUserController::class, 'show']);
                Route::put('{id}', [AdminUserController::class, 'update']);
                Route::delete('{id}', [AdminUserController::class, 'destroy']);
                Route::post('{id}/restore', [AdminUserController::class, 'restore']);
                Route::post('{id}/subscription', [AdminUserController::class, 'updateSubscription']);
            });

            Route::prefix('coaches')->group(function () {
                Route::get('/', [AdminCoachController::class, 'index']);
                Route::post('/', [AdminCoachController::class, 'store']);
                Route::get('{id}', [AdminCoachController::class, 'show']);
                Route::put('{id}', [AdminCoachController::class, 'update']);
                Route::delete('{id}', [AdminCoachController::class, 'destroy']);
                Route::post('{id}/suspend', [AdminCoachController::class, 'suspend']);
            });

            Route::prefix('content')->group(function () {
                Route::get('/', [AdminContentController::class, 'index']);
                Route::post('/', [AdminContentController::class, 'store']);
                Route::put('{id}', [AdminContentController::class, 'update']);
                Route::delete('{id}', [AdminContentController::class, 'destroy']);
                Route::post('bulk-upload', [AdminContentController::class, 'bulkUpload']);
            });

            Route::prefix('reports')->group(function () {
                Route::get('users', [AdminReportController::class, 'userReport']);
                Route::get('revenue', [AdminReportController::class, 'revenueReport']);
                Route::get('coaches', [AdminReportController::class, 'coachPerformanceReport']);
                Route::get('export/{type}', [AdminReportController::class, 'exportReport']);
            });
        });
    });
});
