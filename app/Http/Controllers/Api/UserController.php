<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

class UserController extends Controller
{
    /** Default password assigned to admin-created accounts and used to reset a forgotten one. */
    public const DEFAULT_PASSWORD = 'Sciencesaufeminin@2026';

    #[OA\Get(
        path: '/users',
        summary: 'Lister les utilisatrices',
        security: [['bearerAuth' => []]],
        tags: ['Utilisatrices'],
        parameters: [
            new OA\QueryParameter(name: 'role', description: 'Filtrer par rôle', schema: new OA\Schema(type: 'string', enum: ['admin', 'staff', 'mentor', 'mentee', 'donor'])),
            new OA\QueryParameter(name: 'status', description: 'Filtrer par statut', schema: new OA\Schema(type: 'string', enum: ['pending', 'active', 'suspended'])),
            new OA\QueryParameter(name: 'search', description: 'Recherche sur le nom ou l\'email', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Page paginée (10/page)', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/User')),
            ])),
            new OA\Response(response: 403, description: "Permission `users.view` requise"),
        ]
    )]
    public function index(Request $request)
    {
        return User::query()
            ->with(['mentorProfile', 'menteeProfile', 'roles'])
            ->when($request->query('role'), fn ($q, $role) => $q->role($role))
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->query('search'), function ($q, $search) {
                $q->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"));
            })
            ->orderBy('name')
            ->paginate(10);
    }

    #[OA\Get(
        path: '/users/{user}',
        summary: 'Consulter une utilisatrice',
        security: [['bearerAuth' => []]],
        tags: ['Utilisatrices'],
        parameters: [new OA\PathParameter(name: 'user', schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Utilisatrice', content: new OA\JsonContent(ref: '#/components/schemas/User')),
            new OA\Response(response: 403, description: "Permission `users.view` requise"),
            new OA\Response(response: 404, description: 'Introuvable'),
        ]
    )]
    public function show(User $user)
    {
        return $user->load(['mentorProfile', 'menteeProfile', 'roles', 'badges', 'certificates']);
    }

    /**
     * Admin-created account (e.g. "Inviter une collaboratrice"). Unlike self-registration,
     * this can create staff/admin accounts. The account starts with DEFAULT_PASSWORD; the
     * admin communicates it to the invitee, who should change it from her profile.
     */
    #[OA\Post(
        path: '/users',
        summary: 'Créer un compte (invitation) — peut créer admin/staff',
        security: [['bearerAuth' => []]],
        tags: ['Utilisatrices'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'role'],
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'email', type: 'string', format: 'email'),
                    new OA\Property(property: 'role', type: 'string', enum: ['admin', 'staff', 'mentor', 'mentee', 'donor']),
                    new OA\Property(property: 'country', type: 'string', nullable: true),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Compte créé', content: new OA\JsonContent(ref: '#/components/schemas/User')),
            new OA\Response(response: 403, description: "Permission `users.manage` requise"),
            new OA\Response(response: 422, description: 'Validation échouée', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(['admin', 'staff', 'mentor', 'mentee', 'donor'])],
            'country' => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => self::DEFAULT_PASSWORD,
            'country' => $data['country'] ?? null,
            'status' => 'active',
        ]);

        $user->assignRole($data['role']);

        AuditLog::record($request->user(), 'compte.invite', $user, ['role' => $data['role']]);

        return response()->json($user->load('roles'), 201);
    }

    #[OA\Patch(
        path: '/users/{user}',
        summary: 'Modifier ses informations de profil',
        security: [['bearerAuth' => []]],
        tags: ['Utilisatrices'],
        parameters: [new OA\PathParameter(name: 'user', schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(properties: [
            new OA\Property(property: 'name', type: 'string'),
            new OA\Property(property: 'email', type: 'string', format: 'email'),
            new OA\Property(property: 'country', type: 'string', nullable: true),
            new OA\Property(property: 'phone', type: 'string', nullable: true),
            new OA\Property(property: 'locale', type: 'string', maxLength: 5),
        ])),
        responses: [
            new OA\Response(response: 200, description: 'Utilisatrice mise à jour', content: new OA\JsonContent(ref: '#/components/schemas/User')),
            new OA\Response(response: 403, description: "Permission `users.manage` requise"),
            new OA\Response(response: 422, description: 'Validation échouée', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'country' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'locale' => ['sometimes', 'string', 'max:5'],
        ]);

        $user->update($data);

        return $user;
    }

    #[OA\Post(
        path: '/users/{user}/suspend',
        summary: 'Suspendre un compte',
        security: [['bearerAuth' => []]],
        tags: ['Utilisatrices'],
        parameters: [new OA\PathParameter(name: 'user', schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Compte suspendu', content: new OA\JsonContent(ref: '#/components/schemas/User')),
            new OA\Response(response: 403, description: "Permission `users.manage` requise"),
        ]
    )]
    public function suspend(Request $request, User $user)
    {
        $user->update(['status' => 'suspended']);

        AuditLog::record($request->user(), 'compte.suspendu', $user);

        return $user;
    }

    #[OA\Post(
        path: '/users/{user}/activate',
        summary: 'Réactiver un compte suspendu',
        security: [['bearerAuth' => []]],
        tags: ['Utilisatrices'],
        parameters: [new OA\PathParameter(name: 'user', schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Compte réactivé', content: new OA\JsonContent(ref: '#/components/schemas/User')),
            new OA\Response(response: 403, description: "Permission `users.manage` requise"),
        ]
    )]
    public function activate(Request $request, User $user)
    {
        $user->update(['status' => 'active']);

        AuditLog::record($request->user(), 'compte.active', $user);

        return $user;
    }

    #[OA\Post(
        path: '/users/{user}/validate-mentor',
        summary: 'Valider un profil mentore',
        description: 'Marque le profil mentore comme validé et active le compte.',
        security: [['bearerAuth' => []]],
        tags: ['Utilisatrices'],
        parameters: [new OA\PathParameter(name: 'user', schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Mentore validée', content: new OA\JsonContent(ref: '#/components/schemas/User')),
            new OA\Response(response: 403, description: "Permission `users.manage` requise"),
            new OA\Response(response: 422, description: "Cette utilisatrice n'a pas de profil mentore"),
        ]
    )]
    public function validateMentor(Request $request, User $user)
    {
        $profile = $user->mentorProfile;

        abort_if(! $profile, 422, "Cette utilisatrice n'a pas de profil mentore.");

        $profile->update([
            'validated_at' => now(),
            'validated_by' => $request->user()->id,
        ]);
        $user->update(['status' => 'active']);

        AuditLog::record($request->user(), 'mentore.validee', $user);

        return $user->load('mentorProfile');
    }

    #[OA\Post(
        path: '/users/{user}/role',
        summary: 'Changer le rôle RBAC',
        security: [['bearerAuth' => []]],
        tags: ['Utilisatrices'],
        parameters: [new OA\PathParameter(name: 'user', schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(required: ['role'], properties: [
                new OA\Property(property: 'role', type: 'string', enum: ['admin', 'staff', 'mentor', 'mentee', 'donor']),
            ])
        ),
        responses: [
            new OA\Response(response: 200, description: 'Rôle modifié', content: new OA\JsonContent(ref: '#/components/schemas/User')),
            new OA\Response(response: 403, description: "Permission `settings.manage` requise"),
        ]
    )]
    public function assignRole(Request $request, User $user)
    {
        $data = $request->validate([
            'role' => ['required', Rule::in(['admin', 'staff', 'mentor', 'mentee', 'donor'])],
        ]);

        $user->syncRoles([$data['role']]);

        AuditLog::record($request->user(), 'role.modifie', $user, ['role' => $data['role']]);

        return $user->load('roles');
    }

    #[OA\Post(
        path: '/users/{user}/reset-password',
        summary: 'Réinitialiser le mot de passe',
        description: 'Réservé à l\'administratrice. Remet le mot de passe par défaut, à communiquer à l\'utilisatrice.',
        security: [['bearerAuth' => []]],
        tags: ['Utilisatrices'],
        parameters: [new OA\PathParameter(name: 'user', schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Mot de passe réinitialisé', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'password', type: 'string', example: 'Sciencesaufeminin@2026'),
            ])),
            new OA\Response(response: 403, description: "Permission `users.manage` requise"),
        ]
    )]
    public function resetPassword(Request $request, User $user)
    {
        $user->update(['password' => self::DEFAULT_PASSWORD]);
        $user->tokens()->delete();

        AuditLog::record($request->user(), 'mot-de-passe.reinitialise', $user);

        return response()->json(['password' => self::DEFAULT_PASSWORD]);
    }

    #[OA\Delete(
        path: '/users/{user}',
        summary: 'Supprimer un compte (suppression douce)',
        description: "Réservé à l'administratrice. Le compte est désactivé et n'apparaît plus dans les listes, mais reste conservé pour l'audit.",
        security: [['bearerAuth' => []]],
        tags: ['Utilisatrices'],
        parameters: [new OA\PathParameter(name: 'user', schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 204, description: 'Compte supprimé'),
            new OA\Response(response: 403, description: "Permission `users.manage` requise"),
            new OA\Response(response: 422, description: 'Impossible de supprimer son propre compte'),
        ]
    )]
    public function destroy(Request $request, User $user)
    {
        abort_if($user->is($request->user()), 422, 'Vous ne pouvez pas supprimer votre propre compte.');

        $user->tokens()->delete();
        $user->delete();

        AuditLog::record($request->user(), 'compte.supprime', $user);

        return response()->noContent();
    }
}
