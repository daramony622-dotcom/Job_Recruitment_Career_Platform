<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Http\Requests\Auth\ResendOtpRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(protected AuthService $authService) {}

    public function register(RegisterRequest $request)
    {
        $user = $this->authService->register($request->validated());

        return response()->json([
            'message' => 'Registered. Check your email for the OTP code.',
            'user_id' => $user->id,
        ], 201);
    }

    public function verifyOtp(VerifyOtpRequest $request)
    {
        $user = $this->authService->verifyOtp($request->email, $request->code);
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Email verified.',
            'user' => $user,
            'token' => $token,
        ]);
    }


    public function resendOtp(ResendOtpRequest $request)
    {
        $user = $this->authService->findUserOrFail($request->email);

        if ($user->email_verified_at) {
            return response()->json(['message' => 'Email already verified.'], 400);
        }

        $this->authService->generateAndSendOtp($user, 'email_verification');

        return response()->json(['message' => 'A new code has been sent.']);
    }

    public function login(LoginRequest $request)
    {
        $result = $this->authService->login($request->email, $request->password);

        return response()->json([
            'user' => $result['user'],
            'token' => $result['token'],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logged out.']);
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $user = $this->authService->findUserOrFail($request->email);

        $this->authService->generateAndSendOtp($user, 'password_reset');

        return response()->json(['message' => 'A password reset code has been sent.']);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $this->authService->resetPassword($request->email, $request->code, $request->password);

        return response()->json(['message' => 'Password has been reset. You can now log in.']);
    }
}