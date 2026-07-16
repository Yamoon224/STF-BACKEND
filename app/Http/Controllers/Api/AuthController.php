<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\MenteeProfile;
use App\Models\MentorProfile;
use App\Models\User;
use App\Services\MfaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(protected MfaService $mfa) {}

    /**
     * Public self-registration, restricted to the roles that may sign themselves up.
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(['mentee', 'mentor', 'donor'])],
            'country' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            // Mentor-only
            'expertise' => ['required_if:role,mentor', 'nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string'],
            // Mentee-only
            'level' => ['nullable', 'string', 'max:255'],
            'school' => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'country' => $data['country'] ?? null,
            'phone' => $data['phone'] ?? null,
            'status' => $data['role'] === 'mentor' ? 'pending' : 'active',
        ]);

        $user->assignRole($data['role']);

        if ($data['role'] === 'mentor') {
            MentorProfile::create([
                'user_id' => $user->id,
                'expertise' => $data['expertise'],
                'bio' => $data['bio'] ?? null,
            ]);
        } elseif ($data['role'] === 'mentee') {
            MenteeProfile::create([
                'user_id' => $user->id,
                'level' => $data['level'] ?? null,
                'school' => $data['school'] ?? null,
            ]);
        }

        AuditLog::record($user, 'compte.cree', $user, ['role' => $data['role']]);

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'user' => $this->transformUser($user),
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Identifiants invalides.'],
            ]);
        }

        if ($user->status === 'suspended') {
            throw ValidationException::withMessages([
                'email' => ['Ce compte est suspendu.'],
            ]);
        }

        if ($user->mfa_enabled) {
            $challenge = (string) Str::uuid();
            Cache::put("mfa-challenge:{$challenge}", $user->id, now()->addMinutes(5));

            return response()->json([
                'mfa_required' => true,
                'mfa_challenge' => $challenge,
            ]);
        }

        return $this->issueTokenResponse($user);
    }

    public function verifyMfa(Request $request)
    {
        $data = $request->validate([
            'mfa_challenge' => ['required', 'string'],
            'code' => ['required', 'string'],
        ]);

        $userId = Cache::get("mfa-challenge:{$data['mfa_challenge']}");

        if (! $userId) {
            throw ValidationException::withMessages([
                'mfa_challenge' => ['Ce challenge a expiré, reconnectez-vous.'],
            ]);
        }

        $user = User::findOrFail($userId);

        if (! $this->mfa->verify($user->mfa_secret, $data['code'])
            && ! $this->consumeRecoveryCode($user, $data['code'])) {
            throw ValidationException::withMessages([
                'code' => ['Code de vérification invalide.'],
            ]);
        }

        Cache::forget("mfa-challenge:{$data['mfa_challenge']}");

        return $this->issueTokenResponse($user);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        AuditLog::record($request->user(), 'auth.logout');

        return response()->noContent();
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $this->transformUser($request->user()),
        ]);
    }

    protected function consumeRecoveryCode(User $user, string $code): bool
    {
        $codes = $user->mfa_recovery_codes ?? [];

        if (! in_array(strtoupper($code), $codes, true)) {
            return false;
        }

        $user->forceFill([
            'mfa_recovery_codes' => array_values(array_diff($codes, [strtoupper($code)])),
        ])->save();

        return true;
    }

    protected function issueTokenResponse(User $user)
    {
        $user->forceFill(['last_login_at' => now()])->save();

        AuditLog::record($user, 'auth.login', $user);

        return response()->json([
            'user' => $this->transformUser($user),
            'token' => $user->createToken('api')->plainTextToken,
        ]);
    }

    protected function transformUser(User $user): array
    {
        $user->loadMissing(['mentorProfile', 'menteeProfile', 'badges', 'certificates']);

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'status' => $user->status,
            'country' => $user->country,
            'phone' => $user->phone,
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'mfa_enabled' => $user->mfa_enabled,
            'last_login_at' => $user->last_login_at,
            'mentor_profile' => $user->mentorProfile,
            'mentee_profile' => $user->menteeProfile,
            'badges' => $user->badges,
            'certificates' => $user->certificates,
        ];
    }
}
