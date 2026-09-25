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
    public function register(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $cleanEmail = strtolower(trim($data['email']));

            // Normalize role aliases so the DB constraint is never violated.
            $rawRole  = $data['role'] ?? 'user';
            $roleMap  = [
                'job_seeker' => 'user',
                'candidate'  => 'user',
            ];
            $role = $roleMap[$rawRole] ?? $rawRole;

            // Only allow known safe roles; default to 'user'.
            if (! in_array($role, ['admin', 'hr', 'company', 'user'], true)) {
                $role = 'user';
            }

            $user = User::where('email', $cleanEmail)->first();

            if ($user) {
                if (!empty($user->password)) {
                    throw ValidationException::withMessages([
                        'email' => ['The email address has already been taken.'],
                    ]);
                }

                // If user existed via OAuth without password, attach password & update profile
                $user->update([
                    'name'              => trim($data['name']) ?: $user->name,
                    'phone'             => !empty($data['phone']) ? trim($data['phone']) : $user->phone,
                    'password'          => Hash::make($data['password']),
                    'role'              => ($role !== 'user') ? $role : ($user->role ?? 'user'),
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ]);
            } else {
                $user = User::create([
                    'name'              => trim($data['name']),
                    'email'             => $cleanEmail,
                    'phone'             => !empty($data['phone']) ? trim($data['phone']) : null,
                    'password'          => Hash::make($data['password']),
                    'role'              => $role,
                    'email_verified_at' => now(),
                ]);
            }

            // Still try to send a welcome OTP, but never block on failure.
            try {
                $this->generateAndSendOtp($user, 'email_verification');
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
     */
    public function login(
        string $email,
        string $password
    ): array {
        $cleanEmail = strtolower(trim($email));

        $user = User::where('email', $cleanEmail)->first();

        if (!$user) {
            if (app()->environment('local')) {
                // Auto-create account for seamless local dev testing
                $namePart = explode('@', $cleanEmail)[0];
                $user = User::create([
                    'name'              => ucfirst($namePart),
                    'email'             => $cleanEmail,
                    'password'          => Hash::make($password),
                    'role'              => 'user',
                    'email_verified_at' => now(),
                    'is_active'         => true,
                ]);
            } else {
                throw new HttpException(
                    401,
                    'Invalid email address or password.'
                );
            }
        }

        if (empty($user->password) || (app()->environment('local') && !Hash::check($password, $user->password))) {
            // In local environment or OAuth accounts, sync password on login for smooth testing
            $user->update([
                'password' => Hash::make($password),
            ]);
        } elseif (!Hash::check($password, $user->password)) {
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
         * Auto-verify email on first login if not yet verified
         * (covers users registered before this fix, Google OAuth users, etc.)
         */
        if (! $user->email_verified_at) {
            $user->update(['email_verified_at' => now()]);
        }

        /*
         * Create Sanctum token.
         */
        $token = $user
            ->createToken('auth_token')
            ->plainTextToken;

        return [
            'user'  => $user->fresh(),
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