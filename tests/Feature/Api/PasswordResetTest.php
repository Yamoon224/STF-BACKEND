<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    public function test_forgot_password_sends_a_reset_link_for_an_existing_account(): void
    {
        Notification::fake();
        $user = $this->makeUser('mentee', ['email' => 'aicha@example.org']);

        $response = $this->postJson('/api/auth/forgot-password', ['email' => 'aicha@example.org']);

        $response->assertOk()->assertJsonStructure(['message']);
        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_forgot_password_returns_the_same_generic_message_for_an_unknown_email(): void
    {
        Notification::fake();

        $response = $this->postJson('/api/auth/forgot-password', ['email' => 'inconnue@example.org']);

        $response->assertOk()->assertJsonStructure(['message']);
        Notification::assertNothingSent();
    }

    public function test_reset_password_with_a_valid_token_changes_the_password_and_revokes_tokens(): void
    {
        $user = $this->makeUser('mentee', ['email' => 'aicha@example.org', 'password' => 'ancien-mdp-123']);
        $user->createToken('api');
        $token = Password::broker('users')->createToken($user);

        $response = $this->postJson('/api/auth/reset-password', [
            'token' => $token,
            'email' => 'aicha@example.org',
            'password' => 'nouveau-mdp-456',
            'password_confirmation' => 'nouveau-mdp-456',
        ]);

        $response->assertNoContent();
        $this->assertTrue(Hash::check('nouveau-mdp-456', $user->fresh()->password));
        $this->assertSame(0, $user->fresh()->tokens()->count());

        $login = $this->postJson('/api/auth/login', [
            'email' => 'aicha@example.org',
            'password' => 'nouveau-mdp-456',
        ]);
        $login->assertOk()->assertJsonStructure(['user', 'token']);
    }

    public function test_reset_password_with_an_invalid_token_is_rejected(): void
    {
        $this->makeUser('mentee', ['email' => 'aicha@example.org']);

        $response = $this->postJson('/api/auth/reset-password', [
            'token' => 'not-a-real-token',
            'email' => 'aicha@example.org',
            'password' => 'nouveau-mdp-456',
            'password_confirmation' => 'nouveau-mdp-456',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors('token');
    }
}
