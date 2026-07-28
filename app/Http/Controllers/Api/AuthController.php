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
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    public function __construct(protected MfaService $mfa) {}

    /**
     * Public self-registration, restricted to the roles that may sign themselves up.
     */
    #[OA\Post(
        path: '/auth/register',
        summary: 'Créer un compte (mentée, mentore ou bailleur)',
        description: "Les comptes admin/staff ne peuvent pas être créés par cette route (voir `POST /users`). "
            .'Un compte mentore ou mentée démarre `pending` (en attente de vérification de la pièce d\'identité, '
            .'et du diplôme/bulletin pour une mentée) ; bailleur démarre `active`.',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    type: 'object',
                    required: ['name', 'email', 'password', 'password_confirmation', 'role'],
                    properties: [
                        new OA\Property(property: 'name', type: 'string', example: 'Aïcha Diallo'),
                        new OA\Property(property: 'email', type: 'string', format: 'email'),
                        new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 8),
                        new OA\Property(property: 'password_confirmation', type: 'string', format: 'password'),
                        new OA\Property(property: 'role', type: 'string', enum: ['mentee', 'mentor', 'donor']),
                        new OA\Property(property: 'country', type: 'string', nullable: true),
                        new OA\Property(property: 'phone', type: 'string', nullable: true),
                        new OA\Property(property: 'expertise', type: 'string', nullable: true, description: 'Requis si role=mentor'),
                        new OA\Property(property: 'bio', type: 'string', nullable: true, description: 'Requis si role=mentor — filière et apport de la candidate'),
                        new OA\Property(property: 'level', type: 'string', nullable: true, description: 'mentee uniquement'),
                        new OA\Property(property: 'school', type: 'string', nullable: true, description: 'mentee uniquement'),
                        new OA\Property(property: 'interests', type: 'string', nullable: true, description: "mentee uniquement — domaine et métier STEM choisis, ex. « Science — Biologie »"),
                        new OA\Property(property: 'goals', type: 'string', nullable: true, description: 'Requis si role=mentee — formation recherchée par la candidate'),
                        new OA\Property(property: 'identity_document', type: 'string', format: 'binary', description: 'Requis si role=mentor ou mentee — pièce d\'identité (PNG, JPG ou WEBP).'),
                        new OA\Property(property: 'diploma_document', type: 'string', format: 'binary', description: 'Requis si role=mentee — diplôme ou bulletin (image ou PDF).'),
                        new OA\Property(property: 'cv_document', type: 'string', format: 'binary', description: 'Requis si role=mentor — CV (PDF, DOC ou DOCX).'),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Compte créé', content: new OA\JsonContent(ref: '#/components/schemas/AuthTokenResponse')),
            new OA\Response(response: 422, description: 'Validation échouée', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
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
            'bio' => ['required_if:role,mentor', 'nullable', 'string', 'max:2000'],
            // Mentee-only
            'level' => ['nullable', 'string', 'max:255'],
            'school' => ['nullable', 'string', 'max:255'],
            'interests' => ['nullable', 'string', 'max:255'],
            'goals' => ['required_if:role,mentee', 'nullable', 'string', 'max:2000'],
            // Mentor & mentee: identity verification documents
            'identity_document' => [
                Rule::requiredIf(in_array($request->input('role'), ['mentor', 'mentee'], true)),
                'nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:8192',
            ],
            'diploma_document' => [
                Rule::requiredIf($request->input('role') === 'mentee'),
                'nullable', 'file', 'mimes:png,jpg,jpeg,webp,pdf', 'max:8192',
            ],
            'cv_document' => [
                Rule::requiredIf($request->input('role') === 'mentor'),
                'nullable', 'file', 'mimes:pdf,doc,docx', 'max:8192',
            ],
        ]);

        $needsVerification = in_array($data['role'], ['mentor', 'mentee'], true);

        $identityDocumentPath = $request->hasFile('identity_document')
            ? $request->file('identity_document')->store('identity-documents', 'local')
            : null;

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'country' => $data['country'] ?? null,
            'phone' => $data['phone'] ?? null,
            'status' => $needsVerification ? 'pending' : 'active',
            'identity_document_path' => $identityDocumentPath,
        ]);

        $user->assignRole($data['role']);

        if ($data['role'] === 'mentor') {
            $cvDocumentPath = $request->hasFile('cv_document')
                ? $request->file('cv_document')->store('cv-documents', 'local')
                : null;

            MentorProfile::create([
                'user_id' => $user->id,
                'expertise' => $data['expertise'],
                'bio' => $data['bio'] ?? null,
                'cv_document_path' => $cvDocumentPath,
            ]);
        } elseif ($data['role'] === 'mentee') {
            $diplomaDocumentPath = $request->hasFile('diploma_document')
                ? $request->file('diploma_document')->store('diplomas', 'local')
                : null;

            MenteeProfile::create([
                'user_id' => $user->id,
                'level' => $data['level'] ?? null,
                'school' => $data['school'] ?? null,
                'interests' => $data['interests'] ?? null,
                'goals' => $data['goals'] ?? null,
                'diploma_document_path' => $diplomaDocumentPath,
            ]);
        }

        AuditLog::record($user, 'compte.cree', $user, ['role' => $data['role']]);

        // A pending account (mentor/mentee awaiting identity verification) must not be able
        // to use the API before an admin approves it, so no session token is issued yet.
        $token = $needsVerification ? null : $user->createToken('api')->plainTextToken;

        return response()->json([
            'user' => $this->transformUser($user),
            'token' => $token,
            'pending' => $needsVerification,
        ], 201);
    }

    #[OA\Post(
        path: '/auth/login',
        summary: 'Se connecter',
        description: "Renvoie soit un jeton (`AuthTokenResponse`), soit un défi MFA (`MfaChallengeResponse`) si la double authentification est activée sur ce compte.",
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email'),
                    new OA\Property(property: 'password', type: 'string', format: 'password'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Connectée, ou défi MFA à compléter',
                content: new OA\JsonContent(
                    oneOf: [
                        new OA\Schema(ref: '#/components/schemas/AuthTokenResponse'),
                        new OA\Schema(ref: '#/components/schemas/MfaChallengeResponse'),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Identifiants invalides ou compte suspendu', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
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

        if ($user->status !== 'active') {
            throw ValidationException::withMessages([
                'email' => [match ($user->status) {
                    'pending' => 'Votre inscription est en cours de vérification.',
                    'rejected' => 'Votre inscription a été refusée.'
                        .($user->identity_rejected_reason ? " Motif : {$user->identity_rejected_reason}" : ''),
                    default => 'Ce compte est suspendu.',
                }],
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

    #[OA\Post(
        path: '/auth/mfa/verify',
        summary: 'Compléter la connexion avec un code MFA',
        description: 'Accepte un code TOTP courant ou un code de récupération à usage unique.',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['mfa_challenge', 'code'],
                properties: [
                    new OA\Property(property: 'mfa_challenge', type: 'string'),
                    new OA\Property(property: 'code', type: 'string', example: '123456'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Connectée', content: new OA\JsonContent(ref: '#/components/schemas/AuthTokenResponse')),
            new OA\Response(response: 422, description: 'Code invalide ou défi expiré', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
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

    #[OA\Post(
        path: '/auth/logout',
        summary: 'Révoquer le jeton courant',
        security: [['bearerAuth' => []]],
        tags: ['Auth'],
        responses: [new OA\Response(response: 204, description: 'Déconnectée')]
    )]
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        AuditLog::record($request->user(), 'auth.logout');

        return response()->noContent();
    }

    #[OA\Get(
        path: '/auth/me',
        summary: 'Session courante',
        security: [['bearerAuth' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Utilisatrice connectée',
                content: new OA\JsonContent(properties: [new OA\Property(property: 'user', ref: '#/components/schemas/User')])
            ),
            new OA\Response(response: 401, description: 'Non authentifiée'),
        ]
    )]
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
            'identity_document_available' => $user->identity_document_available,
            'identity_rejected_reason' => $user->identity_rejected_reason,
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
