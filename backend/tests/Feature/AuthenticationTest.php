<?php

namespace Tests\Feature;

use App\Models\PasswordOTp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_with_a_supported_role(): void
    {
        Notification::fake();

        $response = $this->postJson('/api/auth/register', [
            'name' => 'Recruiter',
            'email' => 'recruiter@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'hr',
        ]);

        $response->assertCreated()->assertJsonPath('user_id', User::first()->id);
        $this->assertDatabaseHas('users', [
            'email' => 'recruiter@example.com',
            'role' => 'hr',
            'email_verified_at' => null,
        ]);
        $this->assertDatabaseHas('password_otps', [
            'user_id' => User::first()->id,
            'purpose' => 'email_verification',
            'used' => false,
        ]);
    }

    public function test_email_otp_can_be_verified_only_once(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create(['email' => 'user@example.com']);
        $otp = PasswordOTp::create([
            'user_id' => $user->id,
            'code' => '123456',
            'purpose' => 'email_verification',
            'expires_at' => now()->addMinutes(5),
        ]);

        $this->postJson('/api/auth/verify-otp', [
            'email' => $user->email,
            'code' => $otp->code,
        ])->assertOk();

        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->postJson('/api/auth/verify-otp', [
            'email' => $user->email,
            'code' => $otp->code,
        ])->assertUnprocessable()->assertJsonValidationErrors('code');
    }

    public function test_password_reset_changes_password_and_revokes_existing_tokens(): void
    {
        Notification::fake();
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('old-password'),
        ]);
        $user->createToken('existing-token');
        $otp = PasswordOTp::create([
            'user_id' => $user->id,
            'code' => '654321',
            'purpose' => 'password_reset',
            'expires_at' => now()->addMinutes(5),
        ]);

        $this->postJson('/api/auth/reset-password', [
            'email' => $user->email,
            'code' => $otp->code,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertOk();

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->assertDatabaseHas('password_otps', ['id' => $otp->id, 'used' => true]);
    }

    public function test_forgot_password_returns_a_validation_error_for_unknown_email(): void
    {
        $this->postJson('/api/auth/forgot-password', [
            'email' => 'missing@example.com',
        ])->assertUnprocessable()->assertJsonValidationErrors('email');
    }

    public function test_local_login_allows_unverified_users_when_flag_is_enabled(): void
    {
        config()->set('app.allow_unverified_login', true);

        $user = User::factory()->unverified()->create([
            'email' => 'dev@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ])->assertOk();
    }

    public function test_logout_is_successful_without_a_current_token(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/auth/logout')
            ->assertOk()
            ->assertJson(['message' => 'Logged out.']);
    }
}
