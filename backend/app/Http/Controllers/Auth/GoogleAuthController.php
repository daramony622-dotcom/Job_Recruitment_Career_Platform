<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to Google's OAuth page or return the redirect URL.
     * GET /api/auth/google
     */
    public function redirect(Request $request): JsonResponse|RedirectResponse
    {
        /** @var \Laravel\Socialite\Two\AbstractProvider $driver */
        $driver = Socialite::driver('google')->stateless();

        // Bypass SSL certificate verification on local Windows dev environment if cURL CA bundle is missing
        if (config('app.env') === 'local' || config('app.debug')) {
            $driver->setHttpClient(new \GuzzleHttp\Client([
                'verify' => false,
            ]));
        }

        if ($request->has('json') || ($request->expectsJson() && ! $request->acceptsHtml())) {
            return response()->json([
                'success'      => true,
                'redirect_url' => $driver->redirect()->getTargetUrl(),
            ]);
        }

        return $driver->redirect();
    }

    /**
     * Handle the callback from Google or verify a Google token directly.
     * GET|POST /api/auth/google/callback
     */
    public function callback(Request $request): JsonResponse
    {
        $inputToken = $request->input('access_token') ?? $request->input('token');
        $code       = $request->input('code') ?? $request->query('code');

        // Detect if user passed a Sanctum API token (e.g., "4|fghlnYjer...")
        if ($inputToken && str_contains($inputToken, '|')) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid token type. The token provided (' . substr($inputToken, 0, 8) . '...) is a Laravel Sanctum API token, not a Google OAuth token or code.',
            ], 422);
        }

        // Detect if user mistakenly passed the OAuth authorization URL
        if ($inputToken && str_contains($inputToken, 'accounts.google.com')) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid token format. You passed the Google OAuth redirect URL instead of a valid access token or authorization code.',
            ], 422);
        }

        // If the token starts with "4/" it is actually a Google authorization code
        if ($inputToken && str_starts_with($inputToken, '4/')) {
            $code       = $inputToken;
            $inputToken = null;
        }

        if (!$inputToken && !$code) {
            return response()->json([
                'success' => false,
                'message' => 'Missing authorization code or Google access token.',
            ], 422);
        }

        try {
            /** @var \Laravel\Socialite\Two\AbstractProvider $driver */
            $driver = Socialite::driver('google')->stateless();

            // Bypass SSL certificate verification on local Windows dev environment if cURL CA bundle is missing
            if (config('app.env') === 'local' || config('app.debug')) {
                $driver->setHttpClient(new \GuzzleHttp\Client([
                    'verify' => false,
                ]));
            }

            if ($inputToken) {
                // Front-end / Mobile app provided an access token directly (e.g. ya29...)
                $googleUser = $driver->userFromToken($inputToken);
            } else {
                // Standard browser redirect code exchange
                // Inject code into request if provided via JSON payload
                if ($code && !$request->has('code')) {
                    $request->merge(['code' => $code]);
                }
                $googleUser = $driver->user();
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Google authentication failed: ' . $e->getMessage(),
            ], 401);
        }

        if (!$googleUser || !$googleUser->getEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to retrieve valid Google user profile.',
            ], 400);
        }

        // Find existing user by google_id OR email
        $user = User::where('google_id', $googleUser->getId())
                    ->orWhere('email', strtolower($googleUser->getEmail()))
                    ->first();

        if ($user) {
            // Update existing user with google_id and avatar if missing
            $user->update([
                'google_id'         => $googleUser->getId(),
                'avatar'            => $googleUser->getAvatar() ?? $user->avatar,
                'email_verified_at' => $user->email_verified_at ?? now(),
            ]);
        } else {
            // Register new user via Google
            $user = User::create([
                'name'              => $googleUser->getName() ?? 'Google User',
                'email'             => strtolower($googleUser->getEmail()),
                'google_id'         => $googleUser->getId(),
                'avatar'            => $googleUser->getAvatar(),
                'password'          => null,
                'role'              => 'user',
                'email_verified_at' => now(),
            ]);
        }

        // Issue Sanctum token
        $token = $user->createToken('google_auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Authenticated via Google successfully.',
            'data'    => [
                'user'  => [
                    'id'                => $user->id,
                    'name'              => $user->name,
                    'email'             => $user->email,
                    'role'              => $user->role,
                    'avatar'            => $user->avatar,
                    'email_verified_at' => $user->email_verified_at,
                ],
                'token' => $token,
            ],
        ]);
    }
}

