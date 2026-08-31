<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TelegramAuthController extends Controller
{
    /**
     * Redirect or render Telegram OAuth login.
     * GET /api/auth/telegram
     */
    public function redirect(Request $request)
    {
        $botName  = config('services.telegram.bot_name', 'MyAppBot');
        $callback = route('auth.telegram.callback');

        if ($request->wantsJson() || $request->has('json') || $request->is('api/*')) {
            return response()->json([
                'success'      => true,
                'bot_name'     => $botName,
                'callback_url' => $callback,
            ]);
        }

        return response()->view('telegram.login');
    }

    public function login(Request $request): JsonResponse
    {
        // ── Step 1: Basic field validation ────────────────────────────────────
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'id'        => 'required',
            'first_name'=> 'nullable|string',
            'auth_date' => 'required|integer',
            'hash'      => 'required|string',
        ], [
            'id.required'         => 'Telegram user ID (id) is required.',
            'auth_date.required'  => 'Authentication timestamp (auth_date) is required.',
            'hash.required'       => 'Telegram HMAC signature (hash) is required.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Missing Telegram authentication parameters. When testing via Telegram Widget or API, id, first_name, auth_date, and hash must be provided.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $data = $request->only([
            'id', 'first_name', 'last_name',
            'username', 'photo_url', 'auth_date', 'hash',
        ]);

        $phone = $request->input('phone') ?? $request->input('phone_number');
        if ($phone) {
            $data['phone'] = $phone;
        }

        // ── Step 2: Verify Telegram hash signature ────────────────────────────
        if (! $this->validateTelegramHash($data)) {
            Log::warning('Telegram API auth: invalid hash', [
                'ip'          => $request->ip(),
                'telegram_id' => $data['id'] ?? null,
            ]);

            return response()->json([
                'message' => 'Invalid Telegram authentication data.',
            ], 401);
        }

        // ── Step 3: Reject stale data (replay attack protection) ──────────────
        if ((time() - (int) $data['auth_date']) > 86400) {
            return response()->json([
                'message' => 'Telegram session has expired. Please try again.',
            ], 401);
        }

        // ── Step 4: Find or create user ───────────────────────────────────────
        [$user, $isNewUser] = $this->findOrCreateUser($data);

        // ── Step 5: Issue Sanctum token ───────────────────────────────────────
        // Delete old telegram tokens to avoid accumulation
        $user->tokens()->where('name', 'telegram')->delete();

        $token = $user->createToken('telegram')->plainTextToken;

        Log::info('Telegram API auth: success', [
            'user_id'      => $user->id,
            'telegram_id'  => $user->telegram_id,
            'is_new_user'  => $isNewUser,
        ]);

        // ── Step 6: Return response ───────────────────────────────────────────
        return response()->json([
            'token'       => $token,
            'token_type'  => 'Bearer',
            'is_new_user' => $isNewUser,  // frontend can show "Welcome!" for new users
            'user'        => [
                'id'               => $user->id,
                'name'             => $user->name,
                'email'            => $user->email,
                'phone'            => $user->phone,
                'role'             => $user->role,
                'avatar'           => $user->avatar_url,
                'telegram_id'      => $user->telegram_id,
                'telegram_username'=> $user->telegram_username,
                'has_email'        => ! is_null($user->email),
            ],
        ], $isNewUser ? 201 : 200);
    }

    /**
     * POST /api/auth/telegram/email
     *
     * Called after Telegram login if the user wants to add their email.
     * Requires: Authorization: Bearer {token}
     *
     * Body: { "email": "user@example.com" }
     */
    public function addEmail(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
        ]);

        $user = $request->user();

        $user->update(['email' => $request->email]);

        return response()->json([
            'message' => 'Email saved successfully.',
            'user'    => [
                'id'        => $user->id,
                'name'      => $user->name,
                'email'     => $user->email,
                'role'      => $user->role,
                'avatar'    => $user->avatar_url,
                'has_email' => true,
            ],
        ]);
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

    /**
     * Find existing user or create a new one.
     * Returns [$user, $isNewUser].
     */
    private function findOrCreateUser(array $data): array
    {
        $user = User::where('telegram_id', $data['id'])->first();

        if ($user) {
            // Refresh mutable Telegram fields
            $user->update([
                'telegram_username' => $data['username'] ?? $user->telegram_username,
                'telegram_photo'    => $data['photo_url'] ?? $user->telegram_photo,
                'phone'             => $data['phone'] ?? $user->phone,
            ]);

            return [$user, false];
        }

        // New user — auto-register
        $user = User::create([
            'name'              => $this->buildName($data),
            'email'             => null,
            'phone'             => $data['phone'] ?? null,
            'password'          => null,
            'role'              => 'user',
            'telegram_id'       => (string) $data['id'],
            'telegram_username' => $data['username'] ?? null,
            'telegram_photo'    => $data['photo_url'] ?? null,
            'email_verified_at' => now(), // Telegram already verified via phone
        ]);

        return [$user, true];
    }

    /**
     * Verify HMAC-SHA256 hash from Telegram.
     * @see https://core.telegram.org/widgets/login#checking-authorization
     */
    private function validateTelegramHash(array $data): bool
    {
        $receivedHash = $data['hash'] ?? null;

        if (! $receivedHash) {
            return false;
        }

        // Developer shortcut: Allow "test_hash" in local dev environment for easy Bruno testing
        if ((config('app.env') === 'local' || config('app.debug')) && $receivedHash === 'test_hash') {
            return true;
        }

        unset($data['hash']);

        // Remove null/empty — Telegram omits optional fields
        $data = array_filter($data, fn($v) => $v !== null && $v !== '');

        // Sort alphabetically, build "key=value\n..." string
        ksort($data);
        $checkString = implode("\n", array_map(
            fn($k, $v) => "{$k}={$v}",
            array_keys($data),
            array_values($data)
        ));

        // Secret = raw binary SHA256 of bot token
        $secret = hash('sha256', config('services.telegram.bot_token'), binary: true);

        $computedHash = hash_hmac('sha256', $checkString, $secret);

        return hash_equals($computedHash, $receivedHash);
    }

    /**
     * Build display name from Telegram fields.
     */
    private function buildName(array $data): string
    {
        $full = trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));

        return $full ?: ($data['username'] ?? 'Telegram User');
    }
}