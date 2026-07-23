<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Services\MfaService;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class AuthTest extends TestCase
{
    public function test_mentee_can_register(): void
    {
        $missingGoals = $this->postJson('/api/auth/register', [
            'name' => 'Aïcha Diallo',
            'email' => 'aicha@example.org',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'mentee',
            'level' => 'Terminale',
        ]);
        $missingGoals->assertUnprocessable();

        $response = $this->postJson('/api/auth/register', [
            'name' => 'Aïcha Diallo',
            'email' => 'aicha@example.org',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'mentee',
            'level' => 'Terminale',
            'goals' => 'Je recherche une formation en développement web pour me réorienter vers la tech.',
        ]);

        $response->assertCreated()->assertJsonPath('user.roles.0', 'mentee');
        $this->assertNotEmpty($response->json('token'));

        $user = User::where('email', 'aicha@example.org')->first();
        $this->assertNotNull($user);
        $this->assertSame('active', $user->status);
        $this->assertNotNull($user->menteeProfile);
        $this->assertNotNull($user->menteeProfile->goals);
    }

    public function test_mentor_registration_starts_pending_and_requires_expertise(): void
    {
        $missingExpertise = $this->postJson('/api/auth/register', [
            'name' => 'Fatou Konaté',
            'email' => 'fatou@example.org',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'mentor',
        ]);
        $missingExpertise->assertUnprocessable();

        $response = $this->postJson('/api/auth/register', [
            'name' => 'Fatou Konaté',
            'email' => 'fatou@example.org',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'mentor',
            'expertise' => 'Ingénieure logiciel',
            'bio' => 'Ingénieure logiciel avec 8 ans d\'expérience, je peux accompagner sur la montée en compétences techniques et la préparation aux entretiens.',
        ]);

        $response->assertCreated();
        $user = User::where('email', 'fatou@example.org')->first();
        $this->assertSame('pending', $user->status);
        $this->assertNotNull($user->mentorProfile);
        $this->assertNull($user->mentorProfile->validated_at);
    }

    public function test_register_rejects_admin_and_staff_roles(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Intruder',
            'email' => 'intruder@example.org',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
        ]);

        $response->assertUnprocessable();
    }

    public function test_login_with_valid_credentials_returns_token(): void
    {
        $user = $this->makeUser('mentee', ['email' => 'user@example.org', 'password' => 'password123']);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'user@example.org',
            'password' => 'password123',
        ]);

        $response->assertOk()->assertJsonStructure(['user', 'token']);
        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_login_with_invalid_password_is_rejected(): void
    {
        $this->makeUser('mentee', ['email' => 'user@example.org', 'password' => 'password123']);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'user@example.org',
            'password' => 'wrong-password',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors('email');
    }

    public function test_suspended_user_cannot_login(): void
    {
        $this->makeUser('mentee', [
            'email' => 'suspended@example.org',
            'password' => 'password123',
            'status' => 'suspended',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'suspended@example.org',
            'password' => 'password123',
        ]);

        $response->assertUnprocessable();
    }

    public function test_login_with_mfa_enabled_requires_challenge(): void
    {
        $user = $this->makeUser('admin', [
            'email' => 'admin@example.org',
            'password' => 'password123',
            'mfa_enabled' => true,
            'mfa_secret' => 'ABCDEFGHIJ234567',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'admin@example.org',
            'password' => 'password123',
        ]);

        $response->assertOk()->assertJson(['mfa_required' => true]);
        $this->assertNotEmpty($response->json('mfa_challenge'));
        $this->assertNull($user->fresh()->last_login_at);
    }

    public function test_mfa_verify_completes_login_with_valid_code(): void
    {
        $secret = app(MfaService::class)->generateSecret();

        $this->makeUser('admin', [
            'email' => 'admin@example.org',
            'password' => 'password123',
            'mfa_enabled' => true,
            'mfa_secret' => $secret,
        ]);

        $login = $this->postJson('/api/auth/login', [
            'email' => 'admin@example.org',
            'password' => 'password123',
        ])->json();

        $code = (new Google2FA)->getCurrentOtp($secret);

        $response = $this->postJson('/api/auth/mfa/verify', [
            'mfa_challenge' => $login['mfa_challenge'],
            'code' => $code,
        ]);

        $response->assertOk()->assertJsonStructure(['user', 'token']);
    }

    public function test_mfa_verify_rejects_invalid_code(): void
    {
        $secret = app(MfaService::class)->generateSecret();

        $this->makeUser('admin', [
            'email' => 'admin@example.org',
            'password' => 'password123',
            'mfa_enabled' => true,
            'mfa_secret' => $secret,
        ]);

        $login = $this->postJson('/api/auth/login', [
            'email' => 'admin@example.org',
            'password' => 'password123',
        ])->json();

        $response = $this->postJson('/api/auth/mfa/verify', [
            'mfa_challenge' => $login['mfa_challenge'],
            'code' => '000000',
        ]);

        $response->assertUnprocessable();
    }

    public function test_mfa_verify_rejects_expired_or_unknown_challenge(): void
    {
        $response = $this->postJson('/api/auth/mfa/verify', [
            'mfa_challenge' => 'not-a-real-challenge',
            'code' => '123456',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors('mfa_challenge');
    }

    public function test_me_returns_current_user(): void
    {
        $user = $this->makeUser('mentee');

        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson('/api/auth/me');

        $response->assertOk()->assertJsonPath('user.email', $user->email);
    }

    public function test_me_requires_authentication(): void
    {
        $this->getJson('/api/auth/me')->assertUnauthorized();
    }

    public function test_logout_revokes_current_token(): void
    {
        $user = $this->makeUser('mentee');
        $token = $user->createToken('api')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")->postJson('/api/auth/logout');

        $response->assertNoContent();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    protected function tearDown(): void
    {
        Cache::flush();
        parent::tearDown();
    }
}
