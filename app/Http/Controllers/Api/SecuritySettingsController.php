<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Services\MfaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Manages security settings for the currently authenticated user (MFA enrollment,
 * password changes) — distinct from AuthController, which owns the session lifecycle
 * (register/login/logout).
 */
class SecuritySettingsController extends Controller
{
    public function __construct(protected MfaService $mfa) {}

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
