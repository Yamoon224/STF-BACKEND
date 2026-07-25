<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

/**
 * Self-service "forgot password" flow for public site accounts (mentee/mentor/donor) —
 * distinct from SecuritySettingsController::changePassword (authenticated) and from
 * UserController::resetPassword (admin-triggered reset to the default password).
 */
class PasswordResetController extends Controller
{
    private const GENERIC_MESSAGE = "Si un compte existe pour cet e-mail, un lien de réinitialisation vient d'être envoyé.";

    #[OA\Post(
        path: '/auth/forgot-password',
        summary: 'Demander un lien de réinitialisation de mot de passe',
        description: 'Renvoie toujours le même message, que le compte existe ou non, pour ne pas révéler quels e-mails sont enregistrés.',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(required: ['email'], properties: [new OA\Property(property: 'email', type: 'string', format: 'email')])
        ),
        responses: [
            new OA\Response(response: 200, description: 'Message générique', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'message', type: 'string'),
            ])),
        ]
    )]
    public function forgotPassword(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'email']]);

        $user = User::where('email', $data['email'])->first();

        if ($user) {
            Password::broker('users')->sendResetLink(['email' => $data['email']]);
            AuditLog::record($user, 'mot_de_passe.oubli_demande', $user);
        }

        return response()->json(['message' => self::GENERIC_MESSAGE]);
    }

    #[OA\Post(
        path: '/auth/reset-password',
        summary: 'Réinitialiser le mot de passe avec un jeton reçu par e-mail',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['token', 'email', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'token', type: 'string'),
                    new OA\Property(property: 'email', type: 'string', format: 'email'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 8),
                    new OA\Property(property: 'password_confirmation', type: 'string', format: 'password'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 204, description: 'Mot de passe réinitialisé'),
            new OA\Response(response: 422, description: 'Jeton invalide ou expiré', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function resetPassword(Request $request)
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::broker('users')->reset(
            $data,
            function (User $user, string $password) {
                $user->forceFill(['password' => $password])->save();
                $user->tokens()->delete();

                AuditLog::record($user, 'mot_de_passe.reinitialise', $user);
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'token' => ['Ce lien de réinitialisation est invalide ou a expiré.'],
            ]);
        }

        return response()->noContent();
    }
}
