<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Services\MfaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

/**
 * Manages security settings for the currently authenticated user (MFA enrollment,
 * password changes) — distinct from AuthController, which owns the session lifecycle
 * (register/login/logout).
 */
class SecuritySettingsController extends Controller
{
    public function __construct(protected MfaService $mfa) {}

    #[OA\Post(
        path: '/auth/mfa/setup',
        summary: 'Initialiser la double authentification',
        description: "Génère un secret TOTP (non activé tant que `POST /auth/mfa/confirm` n'a pas été appelé avec un code valide).",
        security: [['bearerAuth' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Secret généré',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'secret', type: 'string', example: 'VTMFODYW6KFBLGTB'),
                    new OA\Property(property: 'otpauth_url', type: 'string', example: 'otpauth://totp/STF:admin%40stf-organisation.org?secret=...'),
                    new OA\Property(property: 'qr_code_svg', type: 'string', description: 'QR code au format SVG à afficher tel quel'),
                ])
            ),
        ]
    )]
    public function setupMfa(Request $request)
    {
        $user = $request->user();
        $secret = $this->mfa->generateSecret();

        $user->forceFill(['mfa_secret' => $secret])->save();

        return response()->json([
            'secret' => $secret,
            'otpauth_url' => $this->mfa->otpAuthUrl($user->email, $secret),
            'qr_code_svg' => $this->mfa->qrCodeSvg($user->email, $secret),
        ]);
    }

    #[OA\Post(
        path: '/auth/mfa/confirm',
        summary: 'Confirmer et activer la double authentification',
        description: 'Renvoie des codes de récupération à usage unique à conserver précieusement.',
        security: [['bearerAuth' => []]],
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(required: ['code'], properties: [new OA\Property(property: 'code', type: 'string', example: '123456')])
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'MFA activée',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'recovery_codes', type: 'array', items: new OA\Items(type: 'string'), example: ['HWF6-C4VD', 'JOUF-T3XZ']),
                ])
            ),
            new OA\Response(response: 422, description: 'Code invalide ou MFA non initialisée', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function confirmMfa(Request $request)
    {
        $data = $request->validate(['code' => ['required', 'string']]);

        $user = $request->user();

        if (! $user->mfa_secret) {
            throw ValidationException::withMessages([
                'code' => ['Veuillez d\'abord initialiser la double authentification.'],
            ]);
        }

        if (! $this->mfa->verify($user->mfa_secret, $data['code'])) {
            throw ValidationException::withMessages([
                'code' => ['Code de vérification invalide.'],
            ]);
        }

        $recoveryCodes = $this->mfa->generateRecoveryCodes();

        $user->forceFill([
            'mfa_enabled' => true,
            'mfa_recovery_codes' => $recoveryCodes,
        ])->save();

        AuditLog::record($user, 'mfa.active', $user);

        return response()->json(['recovery_codes' => $recoveryCodes]);
    }

    #[OA\Post(
        path: '/auth/mfa/disable',
        summary: 'Désactiver la double authentification',
        security: [['bearerAuth' => []]],
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(required: ['password'], properties: [new OA\Property(property: 'password', type: 'string', format: 'password')])
        ),
        responses: [
            new OA\Response(response: 204, description: 'MFA désactivée'),
            new OA\Response(response: 422, description: 'Mot de passe incorrect', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function disableMfa(Request $request)
    {
        $data = $request->validate(['password' => ['required', 'string']]);

        $user = $request->user();

        if (! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['Mot de passe incorrect.'],
            ]);
        }

        $user->forceFill([
            'mfa_enabled' => false,
            'mfa_secret' => null,
            'mfa_recovery_codes' => null,
        ])->save();

        AuditLog::record($user, 'mfa.desactivee', $user);

        return response()->noContent();
    }

    #[OA\Post(
        path: '/auth/password',
        summary: 'Changer son mot de passe',
        security: [['bearerAuth' => []]],
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['current_password', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'current_password', type: 'string', format: 'password'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 8),
                    new OA\Property(property: 'password_confirmation', type: 'string', format: 'password'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 204, description: 'Mot de passe modifié'),
            new OA\Response(response: 422, description: 'Mot de passe actuel incorrect', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function changePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();

        if (! Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Mot de passe actuel incorrect.'],
            ]);
        }

        $user->forceFill(['password' => $data['password']])->save();

        AuditLog::record($user, 'mot_de_passe.modifie', $user);

        return response()->noContent();
    }
}
