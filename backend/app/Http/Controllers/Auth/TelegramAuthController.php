<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\TelegramLoginToken;
use App\Models\User;
use App\Services\Telegram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

class TelegramAuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | STEP 1 — Frontend calls POST /api/auth/telegram/init
    |--------------------------------------------------------------------------
    | Generates a one-time token, stores it, and returns the t.me deep-link
    | that the user will open in Telegram.
    */
    public function init(Request $request, Telegram $tg)
    {
        $token = Str::random(40);

        TelegramLoginToken::create([
            'token'      => $token,
            'status'     => 'pending',
            'user_id'    => Auth::id(), // null if registering, non-null if linking account
            'ip'         => $request->ip(),
            'expires_at' => now()->addMinutes(10),
        ]);

        $deepLink = $tg->deepLink($token);

        if (! $deepLink) {
            return response()->json([
                'message' => 'Telegram bot username is not configured. Please contact the administrator.',
            ], 503);
        }

        return response()->json([
            'token'     => $token,
            'deep_link' => $deepLink,
            'url'       => $deepLink,   // alias — frontend uses data.url
            'bot'       => config('services.telegram.username'),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | STEP 2 — Frontend polls GET /api/auth/telegram/status/{token}
    |--------------------------------------------------------------------------
    | Returns current status. When approved, issues a Sanctum token so the
    | frontend can log the user in without any extra step.
    */
    public function status(Request $request, string $token)
    {
        $row = TelegramLoginToken::where('token', $token)->first();

        if (! $row) {
            return response()->json(['status' => 'not_found'], 404);
        }

        if ($row->isExpired() && $row->status === 'pending') {
            $row->update(['status' => 'expired']);
            return response()->json(['status' => 'expired']);
        }

        if ($row->status === 'declined') {
            return response()->json(['status' => 'declined']);
        }

        if ($row->status === 'expired') {
            return response()->json(['status' => 'expired']);
        }

        if ($row->isApproved()) {
            $user = User::find($row->user_id);

            if (! $user) {
                return response()->json(['status' => 'error', 'message' => 'User not found.'], 404);
            }

            // Mark token consumed so it cannot be reused
            $row->update(['status' => 'consumed']);

            $accessToken = $user->createToken('telegram-auth')->plainTextToken;

            return response()->json([
                'status' => 'approved',
                'token'  => $accessToken,
                'user'   => [
                    'id'                => $user->id,
                    'name'              => $user->name,
                    'email'             => $user->email,
                    'role'              => $user->role,
                    'telegram_id'       => $user->telegram_id,
                    'telegram_username' => $user->telegram_username,
                    'avatar'            => $user->telegram_photo ?? $user->avatar,
                ],
            ]);
        }

        return response()->json(['status' => $row->status ?? 'pending']);
    }

    /*
    |--------------------------------------------------------------------------
    | OPTIONAL — Telegram Login Widget callback (hash-verified)
    |--------------------------------------------------------------------------
    | Used if you place the official Telegram Login Widget on your website.
    | Verifies the HMAC from Telegram's servers and issues a token directly.
    */
    public function callback(Request $request)
    {
        $data = $request->all();

        // Remove the hash from the data before verifying
        $receivedHash = $data['hash'] ?? null;
        unset($data['hash']);

        if (! $receivedHash) {
            return response()->json(['message' => 'Missing hash.'], 422);
        }

        // Build the check string as Telegram requires
        ksort($data);
        $checkString = implode("\n", array_map(
            fn ($k, $v) => "{$k}={$v}",
            array_keys($data),
            array_values($data)
        ));

        $botToken  = config('services.telegram.bot_token');
        $secretKey = hash('sha256', $botToken, true);
        $hash      = hash_hmac('sha256', $checkString, $secretKey);

        if (! hash_equals($hash, $receivedHash)) {
            return response()->json(['message' => 'Invalid Telegram data.'], 403);
        }

        // Check auth_date freshness (max 24 hours)
        if (time() - ($data['auth_date'] ?? 0) > 86400) {
            return response()->json(['message' => 'Telegram auth data is outdated.'], 422);
        }

        $telegramId = $data['id'];
        $firstName  = $data['first_name'] ?? '';
        $lastName   = $data['last_name'] ?? '';
        $username   = $data['username'] ?? null;
        $photoUrl   = $data['photo_url'] ?? null;

        // Find or create the user
        $user = User::firstOrCreate(
            ['telegram_id' => $telegramId],
            [
                'name'              => trim("{$firstName} {$lastName}") ?: 'Telegram User',
                'telegram_username' => $username,
                'telegram_photo'    => $photoUrl,
                'role'              => 'job_seeker',
                'is_active'         => true,
                'email_verified_at' => now(),
            ]
        );

        // Update telegram fields if they changed
        $user->update([
            'telegram_username' => $username ?? $user->telegram_username,
            'telegram_photo'    => $photoUrl ?? $user->telegram_photo,
        ]);

        $accessToken = $user->createToken('telegram-widget')->plainTextToken;

        return response()->json([
            'status' => 'approved',
            'token'  => $accessToken,
            'user'   => [
                'id'                => $user->id,
                'name'              => $user->name,
                'email'             => $user->email,
                'role'              => $user->role,
                'telegram_id'       => $user->telegram_id,
                'telegram_username' => $user->telegram_username,
                'avatar'            => $user->telegram_photo ?? $user->avatar,
            ],
        ]);
    }
}