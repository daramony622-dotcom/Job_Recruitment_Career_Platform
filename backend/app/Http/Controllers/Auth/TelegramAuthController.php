<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TelegramAuthController extends Controller
{
    /** Where to send the user after a successful login. Change to your own route. */
    private const AFTER_LOGIN_ROUTE = 'dashboard';

    public function initApi(Request $request): JsonResponse
    {
        $token = Str::random(40);

        Cache::put("tg_login:$token", [
            'status' => 'pending',
            'ip' => $request->ip(),
            'agent' => Str::limit((string) $request->userAgent(), 60),
        ], now()->addMinutes(5));

        $username = ltrim((string) config('services.telegram.username'), '@');

        return response()->json([
            'token' => $token,
            'url' => "https://t.me/{$username}?start={$token}",
        ]);
    }

    public function statusApi(string $token): JsonResponse
    {
        $key = "tg_login:$token";
        $data = Cache::get($key);
        $response = ['status' => 'expired'];

        if ($data && $data['status'] !== 'approved') {
            $response = ['status' => $data['status']];
        } elseif ($data) {
            $user = User::find($data['user_id'] ?? null);

            if ($user) {
                Cache::forget($key);
                $response = [
                    'status' => 'approved',
                    'token' => $user->createToken('telegram')->plainTextToken,
                    'user' => $user->load('profile'),
                ];
            } else {
                Cache::forget($key);
            }
        }

        return response()->json($response);
    }

    /** Step 1: user clicked "Login / Register with Telegram". */
    public function start(Request $request): RedirectResponse
    {
        $token = Str::random(40);

        Cache::put("tg_login:$token", [
            'status'  => 'pending',
            'session' => $request->session()->getId(), // only THIS browser can finish the login
            'ip'      => $request->ip(),
            'agent'   => Str::limit((string) $request->userAgent(), 60),
        ], now()->addMinutes(5));

        return redirect()->route('telegram.wait', $token);
    }

    /** Step 2: page with the "Open Telegram" button, polls until approved. */
    public function wait(string $token): View
    {
        $link = 'https://t.me/' . config('services.telegram.username') . '?start=' . $token;

        return view('auth.telegram-wait', compact('token', 'link'));
    }

    /** Step 4: browser polls this endpoint every 2 seconds. */
    public function status(Request $request, string $token): JsonResponse
    {
        $key  = "tg_login:$token";
        $data = Cache::get($key);

        if (! $data) {
            return response()->json(['status' => 'expired']);
        }

        // Token is bound to the browser session that created it.
        abort_unless(hash_equals($data['session'], $request->session()->getId()), 403);

        if ($data['status'] === 'approved') {
            Cache::forget($key); // single use

            Auth::loginUsingId($data['user_id'], remember: true);
            $request->session()->regenerate();

            return response()->json([
                'status'   => 'ok',
                'redirect' => route(self::AFTER_LOGIN_ROUTE),
            ]);
        }

        return response()->json(['status' => $data['status']]);
    }
}
