<?php

namespace App\Services;

use App\Models\PasswordOTp;
use App\Models\User;
use App\Notifications\SendOtpNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function register(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => strtolower($data['email']),
                'password' => Hash::make($data['password']),
                'role' => $data['role'] ?? 'user',
            ]);

            $this->generateAndSendOtp($user, 'email_verification');

            return $user;
        });
    }

    public function generateAndSendOtp(User $user, string $purpose): PasswordOTp
    {
        // invalidate any previous unused OTPs of the same purpose
        PasswordOTp::where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->where('used', false)
            ->update(['used' => true]);

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $otp = PasswordOTp::create([
            'user_id' => $user->id,
            'code' => $code,
            'purpose' => $purpose,
            'expires_at' => now()->addMinutes(5),
        ]);

        $user->notify(new SendOtpNotification($code, $purpose));

        return $otp;
    }

    public function verifyOtp(string $email, string $code, string $purpose = 'email_verification'): User
    {
        $user = $this->findUserOrFail($email);

        $otp = PasswordOTp::where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->where('code', $code)
            ->where('used', false)
            ->latest()
            ->first();

        if (!$otp || !$otp->isValid()) {
            throw ValidationException::withMessages([
                'code' => 'This code is invalid or has expired.',
            ]);
        }

        $otp->update(['used' => true]);

        if ($purpose === 'email_verification') {
            $user->update(['email_verified_at' => now()]);
        }

        return $user;
    }

    public function login(string $email, string $password): array
    {
        $user = User::where('email', strtolower($email))->first();

        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'Invalid credentials.',
            ]);
        }

        if (!$user->email_verified_at && !config('app.allow_unverified_login', false)) {
            throw ValidationException::withMessages([
                'email' => 'Please verify your email before logging in.',
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }

    public function resetPassword(string $email, string $code, string $newPassword): User
    {
        $user = $this->verifyOtp($email, $code, 'password_reset');

        $user->update(['password' => Hash::make($newPassword)]);
        $user->tokens()->delete();

        return $user;
    }

    public function findUserOrFail(string $email): User
    {
        $user = User::where('email', strtolower($email))->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => 'We could not find an account with that email address.',
            ]);
        }

        return $user;
    }
}
