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
        $driver = Socialite::driver('google');
        $driver->stateless();

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
    public function callback(Request $request): JsonResponse|RedirectResponse
    {
        [$inputToken, $code, $validationError] = $this->resolveCredentials($request);

        if ($validationError !== null) {
            return $validationError;
        }

        try {
            $googleUser = $this->exchangeGoogleUser($request, $inputToken, $code);
        } catch (\Exception $e) {
            return $this->responseForBrowserOrApi($request, [
                'success' => false,
                'message' => 'Google authentication failed: ' . $e->getMessage(),
            ], 401);
        }

        if (!$googleUser || !$googleUser->getEmail()) {
            $response = [
                'success' => false,
                'message' => 'Unable to retrieve valid Google user profile.',
            ];
            $status = 400;
        } else {
            $user = $this->findOrCreateUser($googleUser);
            $token = $user->createToken('google_auth_token')->plainTextToken;

            $response = [
                'success' => true,
                'message' => 'Authenticated via Google successfully.',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'avatar' => $user->avatar,
                        'email_verified_at' => $user->email_verified_at,
                    ],
                    'token' => $token,
                ],
            ];
            $status = 200;
        }

        return $this->responseForBrowserOrApi($request, $response, $status);
    }

    private function responseForBrowserOrApi(Request $request, array $payload, int $status): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson() || $request->has('json')) {
            return response()->json($payload, $status);
        }

        if (!($payload['success'] ?? false)) {
            return redirect()->to(config('app.frontend_url') . '/login?oauth_error=' . urlencode($payload['message']));
        }

        return redirect()->to(config('app.frontend_url') . '/login?token=' . urlencode($payload['data']['token']));
    }

    private function resolveCredentials(Request $request): array
    {
        $inputToken = $request->input('access_token') ?? $request->input('token');
        $code = $request->input('code') ?? $request->query('code');
        $error = null;

        if ($inputToken && str_contains($inputToken, '|')) {
            $error = response()->json([
                'success' => false,
                'message' => 'Invalid token type. The token provided (' . substr($inputToken, 0, 8) . '...) is a Laravel Sanctum API token, not a Google OAuth token or code.',
            ], 422);
        }

        if ($error === null && $inputToken && str_contains($inputToken, 'accounts.google.com')) {
            $error = response()->json([
                'success' => false,
                'message' => 'Invalid token format. You passed the Google OAuth redirect URL instead of a valid access token or authorization code.',
            ], 422);
        }

        if ($error === null && $inputToken && str_starts_with($inputToken, '4/')) {
            $code = $inputToken;
            $inputToken = null;
        }

        if ($error === null && !$inputToken && !$code) {
            $error = response()->json([
                'success' => false,
                'message' => 'Missing authorization code or Google access token.',
            ], 422);
        }

        return [$inputToken, $code, $error];
    }

    private function exchangeGoogleUser(Request $request, ?string $inputToken, ?string $code): mixed
    {
        /** @var \Laravel\Socialite\Two\AbstractProvider $driver */
        $driver = Socialite::driver('google');
        $driver->stateless();

        if (config('app.env') === 'local' || config('app.debug')) {
            $driver->setHttpClient(new \GuzzleHttp\Client(['verify' => false]));
        }

        if ($inputToken) {
            return $driver->userFromToken($inputToken);
        }

        if ($code && !$request->has('code')) {
            $request->merge(['code' => $code]);
        }

        return $driver->user();
    }

    private function findOrCreateUser(mixed $googleUser): User
    {
        $email = strtolower($googleUser->getEmail());
        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $email)
            ->first();

        if (!$user) {
            return User::create([
                'name' => $googleUser->getName() ?? 'Google User',
                'email' => $email,
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'password' => null,
                'role' => 'user',
                'email_verified_at' => now(),
            ]);
        }

        $user->update([
            'google_id' => $googleUser->getId(),
            'avatar' => $googleUser->getAvatar() ?? $user->avatar,
            'email_verified_at' => $user->email_verified_at ?? now(),
        ]);

        return $user;
    }
}

