<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password as PasswordBroker;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\Rule;
use Google\Client as GoogleClient;

class UserAuthController extends Controller
{
    public function redirectToGoogle()
    {
        $client = $this->googleClient();
        $state = Str::random(64);

        Cache::put('google-password-reset:' . hash('sha256', $state), true, now()->addMinutes(10));

        $client->setState($state);
        $client->setAccessType('online');
        $client->setPrompt('select_account');
        $client->setScopes(['openid', 'email', 'profile']);

        return redirect()->away($client->createAuthUrl());
    }

    public function handleGoogleCallback(Request $request)
    {
        $frontendUrl = rtrim((string) env('FRONTEND_URL', 'http://localhost:5173'), '/');
        $state = (string) $request->query('state');

        if (! $state || ! Cache::pull('google-password-reset:' . hash('sha256', $state))) {
            return redirect()->away($frontendUrl . '/forgot-password?error=Google%20verification%20expired.%20Please%20try%20again.');
        }

        try {
            $client = $this->googleClient();
            $tokens = $client->fetchAccessTokenWithAuthCode((string) $request->query('code'));

            if (! empty($tokens['error']) || empty($tokens['id_token'])) {
                throw new \RuntimeException('Google token exchange failed.');
            }

            $payload = $client->verifyIdToken($tokens['id_token']);
            if (! $payload || empty($payload['email']) || $payload['email_verified'] !== true) {
                throw new \RuntimeException('Google email is not verified.');
            }

            $user = User::query()->where('email', $payload['email'])->first();
            if (! $user) {
                return redirect()->away($frontendUrl . '/forgot-password?error=No%20Corevia%20account%20was%20found%20for%20that%20Google%20email.');
            }

            $token = PasswordBroker::broker()->createToken($user);

            return redirect()->away($frontendUrl . '/reset-password?token=' . urlencode($token) . '&email=' . urlencode($user->email));
        } catch (\Throwable $exception) {
            Log::warning('Google password reset verification failed.', [
                'message' => $exception->getMessage(),
            ]);

            return redirect()->away($frontendUrl . '/forgot-password?error=Google%20verification%20failed.%20Please%20try%20again.');
        }
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
            'age' => ['required', 'integer', 'min:1', 'max:120'],
            'gender' => ['required', Rule::in(['male', 'female', 'other', 'prefer-not-to-say'])],
            'fitness_goals' => ['required', 'array'],
        ]);

        /** @var \App\Models\User $user */
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'age' => $validated['age'],
            'gender' => $validated['gender'],
            'fitness_goals' => $validated['fitness_goals'],
        ]);

        $token = $user->createToken('user')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user,
        ], 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], (string) $user->password)) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        $token = $user->createToken('user')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user,
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
            'user' => $request->user(),
        ]);
    }

    public function updateProfile(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['sometimes', 'confirmed', PasswordRule::defaults()],
            'age' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:120'],
            'gender' => ['sometimes', 'nullable', Rule::in(['male', 'female', 'other', 'prefer-not-to-say'])],
            'fitness_goals' => ['sometimes', 'nullable', 'array'],
            'dietary_preferences' => ['sometimes', 'nullable', 'array'],
            'restrictions' => ['sometimes', 'nullable', 'string'],
            'goal_weight' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:2000'],
            'dark_mode' => ['sometimes', 'boolean'],
        ]);

        $user->fill($validated);
        $user->save();

        return response()->json([
            'user' => $user->fresh(),
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user) {
            return response()->json(['message' => 'We could not find a user with that email address.'], 404);
        }

        $token = PasswordBroker::broker()->createToken($user);
        $frontendUrl = rtrim((string) env('FRONTEND_URL', 'http://localhost:5173'), '/');
        $resetUrl = $frontendUrl . '/reset-password?token=' . urlencode($token) . '&email=' . urlencode($user->email);

        return response()->json([
            'message' => 'Password reset link generated.',
            'reset_url' => $resetUrl,
            'email' => $user->email,
        ]);
    }

    public function sendPasswordResetCode(Request $request)
    {
        $validated = $request->validate(['email' => ['required', 'email']]);
        $email = strtolower($validated['email']);
        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            return response()->json(['message' => 'We could not find a user with that email address.'], 404);
        }

        $code = (string) random_int(100000, 999999);
        DB::table('password_reset_codes')->updateOrInsert(
            ['email' => $email],
            ['code_hash' => Hash::make($code), 'created_at' => now(), 'attempts' => 0]
        );

        Mail::raw("Your Corevia password reset code is {$code}. It expires in 15 minutes.", function ($message) use ($email) {
            $message->to($email)->subject('Corevia password reset code');
        });

        return response()->json(['message' => 'A verification code was sent to your email address.']);
    }

    public function verifyPasswordResetCode(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'digits:6'],
        ]);
        $email = strtolower($validated['email']);
        $record = DB::table('password_reset_codes')->where('email', $email)->first();

        if (! $record || ! $record->created_at || now()->diffInMinutes($record->created_at) >= 15) {
            return response()->json(['message' => 'This code has expired. Request a new code.'], 422);
        }

        if ((int) $record->attempts >= 5 || ! Hash::check($validated['code'], $record->code_hash)) {
            DB::table('password_reset_codes')->where('email', $email)->increment('attempts');
            return response()->json(['message' => 'Invalid verification code.'], 422);
        }

        $user = User::query()->where('email', $email)->first();
        if (! $user) {
            return response()->json(['message' => 'Unable to verify this account.'], 422);
        }

        DB::table('password_reset_codes')->where('email', $email)->delete();
        $token = PasswordBroker::broker()->createToken($user);

        return response()->json(['token' => $token, 'email' => $email]);
    }

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        $status = PasswordBroker::reset(
            $validated,
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
                DB::table('password_reset_codes')->where('email', $user->email)->delete();
            }
        );

        if ($status !== PasswordBroker::PASSWORD_RESET) {
            return response()->json(['message' => __($status)], 422);
        }

        return response()->json([
            'message' => 'Password reset successfully.',
        ]);
    }

    private function googleClient(): GoogleClient
    {
        $client = new GoogleClient();
        $client->setClientId((string) config('services.google.client_id'));
        $client->setClientSecret((string) config('services.google.client_secret'));
        $client->setRedirectUri((string) config('services.google.redirect'));

        return $client;
    }
}
