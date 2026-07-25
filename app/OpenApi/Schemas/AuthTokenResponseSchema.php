<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AuthTokenResponse',
    title: 'Session établie',
    properties: [
        new OA\Property(property: 'user', ref: '#/components/schemas/User'),
        new OA\Property(property: 'token', type: 'string', nullable: true, description: 'Absent si le compte est `pending` (vérification en cours).', example: '1|CuFNHApjWS9wUpWUgquhKOd8MVPdFhZKd9OwpZSk69ca1c4f'),
        new OA\Property(property: 'pending', type: 'boolean', description: "Vrai si l'inscription attend une vérification d'identité avant de pouvoir se connecter."),
    ]
)]
class AuthTokenResponseSchema {}
