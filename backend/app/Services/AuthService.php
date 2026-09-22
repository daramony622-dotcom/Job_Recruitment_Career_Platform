<?php

namespace App\Services;

use App\Models\PasswordOTp;
use App\Models\User;
use App\Notifications\SendOtpNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AuthService
{
    /**
     * Register a new user.
     */
    public function register(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => trim($data['name']),
                'email' => strtolower(trim($data['email'])),
                'phone' => !empty($data['phone'])
                    ? trim($data['phone'])
                    : null,

                'password' => Hash::make($data['password']),

                'role' => $data['role'] ?? 'user',

                // Keep this NULL if email verification is required.
                'email_verified_at' => null,
            ]);

            try {
                $this->generateAndSendOtp(
                    $user,
                    'email_verification'
                );
            } catch (\Throwable $e) {
                report($e);
            }

            return $user;
        });
    }

    /**
     * Generate and send OTP.
     */
    public function generateAndSendOtp(
        User $user,
        string $purpose
    ): PasswordOTp {
        // Invalidate previous unused OTPs.
        PasswordOTp::where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->where('used', false)
            ->update([
                'used' => true,
            ]);

        $code = str_pad(
            (string) random_int(0, 999999),
            6,
            '0',
            STR_PAD_LEFT
        );

        $otp = PasswordOTp::create([
            'user_id' => $user->id,
            'code' => $code,
            'purpose' => $purpose,
            'expires_at' => now()->addMinutes(30),
            'used' => false,
        ]);

        try {
            $user->notify(
                new SendOtpNotification($code, $purpose)
            );
        } catch (\Throwable $e) {
            report($e);
        }

        return $otp;
    }

    /**
     * Verify an OTP.
     */
    public function verifyOtp(
        string $email,
        string $code,
        string $purpose = 'email_verification'
    ): User {
        $cleanEmail = strtolower(trim($email));
        $cleanCode = trim((string) $code);

        $user = $this->findUserOrFail($cleanEmail);

        // Development-only master codes.
        //
        // Remove this completely for production if you don't need
        // development/test OTPs.
        $isDevMasterCode =
            app()->environment('local') &&
            in_array($cleanCode, ['123456', '999999'], true);

        if ($isDevMasterCode) {
            if ($purpose === 'email_verification') {
                $user->update([
                    'email_verified_at' => now(),
                ]);
            }

            return $user;
        }

        $otp = PasswordOTp::where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->where('code', $cleanCode)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if (!$otp) {
            throw ValidationException::withMessages([
                'code' => [
                    'This code is invalid or has expired. Please request a new code.'
                ],
            ]);
        }

        $otp->update([
            'used' => true,
        ]);

        if ($purpose === 'email_verification') {
            $user->update([
                'email_verified_at' => now(),
            ]);
        }

        return $user;
    }

    /**
     * Login.
     *
     * 422 = invalid request/validation
     * 401 = incorrect credentials
     * 200 = successful login
     */
    public function login(
        string $email,
        string $password
    ): array {
        $cleanEmail = strtolower(trim($email));

        $user = User::where('email', $cleanEmail)->first();

        /*
         * IMPORTANT:
         *
         * Do NOT use ValidationException here.
         * ValidationException produces HTTP 422.
         *
         * Invalid credentials should return HTTP 401.
         */
        if (
            !$user ||
            !$user->password ||
            !Hash::check($password, $user->password)
        ) {
            throw new HttpException(
                401,
                'Invalid email address or password.'
            );
        }

        /*
         * If the password was created using an older/less secure
         * hashing configuration, automatically rehash it.
         */
        if (Hash::needsRehash($user->password)) {
            $user->update([
                'password' => Hash::make($password),
            ]);
        }

        /*
         * If your application requires email verification,
         * DON'T automatically verify here.
         *
         * If you want users to be allowed to log in without
         * email verification, remove this entire block.
         */
        if (!$user->email_verified_at) {
            throw new HttpException(
                403,
                'Please verify your email address before logging in.'
            );
        }

        /*
         * Create Sanctum token.
         */
        $token = $user
            ->createToken('auth_token')
            ->plainTextToken;

        return [
            'user' => $user->fresh(),
            'token' => $token,
        ];
    }

    /**
     * Reset password.
     */
    public function resetPassword(
        string $email,
        string $code,
        string $newPassword
    ): User {
        $user = $this->verifyOtp(
            $email,
            $code,
            'password_reset'
        );

        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        // Invalidate all existing login tokens after password reset.
        $user->tokens()->delete();

        return $user->fresh();
    }

    /**
     * Find a user by email or throw validation error.
     */
    public function findUserOrFail(string $email): User
    {
        $cleanEmail = strtolower(trim($email));

        $user = User::where('email', $cleanEmail)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => [
                    'We could not find an account with that email address.'
                ],
            ]);
        }

        return $user;
    }
}