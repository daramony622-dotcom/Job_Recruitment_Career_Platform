<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResendOtpRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->authService->register(
            $request->validated()
        );

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful.',
            'user' => $user,
            'token' => $token,
            'redirect_url' => $this->getRoleRedirectUrl($user),
        ], 201);
    }

    /**
     * Verify email OTP.
     */
    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = $this->authService->verifyOtp(
            $data['email'],
            $data['code']
        );

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Email verified successfully.',
            'user' => $user,
            'token' => $token,
            'redirect_url' => $this->getRoleRedirectUrl($user),
        ]);
    }

    /**
     * Resend email verification OTP.
     */
    public function resendOtp(ResendOtpRequest $request): JsonResponse
    {
        $email = $request->validated()['email'];

        $user = $this->authService->findUserOrFail($email);

        if ($user->email_verified_at) {
            return response()->json([
                'message' => 'Email already verified.',
            ], 400);
        }

        $this->authService->generateAndSendOtp(
            $user,
            'email_verification'
        );

        return response()->json([
            'message' => 'A new verification code has been sent.',
        ]);
    }

    /**
     * Login.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $data = $request->validated();

        $result = $this->authService->login(
            $data['email'],
            $data['password']
        );

        $user = $result['user'];

        return response()->json([
            'message' => 'Login successful.',
            'user' => $user,
            'token' => $result['token'],
            'redirect_url' => $this->getRoleRedirectUrl($user),
        ]);
    }

    /**
     * Logout.
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user) {
            $currentToken = $user->currentAccessToken();

            if ($currentToken) {
                $currentToken->delete();
            }
        }

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * Send password reset OTP.
     */
    public function forgotPassword(
        ForgotPasswordRequest $request
    ): JsonResponse {
        $email = $request->validated()['email'];

        $user = $this->authService->findUserOrFail($email);

        $this->authService->generateAndSendOtp(
            $user,
            'password_reset'
        );

        return response()->json([
            'message' => 'A password reset code has been sent.',
        ]);
    }

    /**
     * Reset password.
     */
    public function resetPassword(
        ResetPasswordRequest $request
    ): JsonResponse {
        $data = $request->validated();

        $this->authService->resetPassword(
            $data['email'],
            $data['code'],
            $data['password']
        );

        return response()->json([
            'message' => 'Password has been reset successfully. You can now log in.',
        ]);
    }

    /**
     * Determine frontend destination based on user role.
     *
     * Must match the routes used by the Vue frontend.
     */
    protected function getRoleRedirectUrl(User $user): string
    {
        $role = strtolower(trim((string) $user->role));

        return match ($role) {
            'admin' => '/admin/dashboard',

            'hr',
            'company' => '/company/dashboard',

            'user',
            'job_seeker' => '/user/dashboard',

            default => '/user/dashboard',
        };
    }
}