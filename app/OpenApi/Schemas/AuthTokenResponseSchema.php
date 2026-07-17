<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AuthTokenResponse',
    title: 'Session établie',
    properties: [
        new OA\Property(property: 'user', ref: '#/components/schemas/User'),
        new OA\Property(property: 'token', type: 'string', example: '1|CuFNHApjWS9wUpWUgquhKOd8MVPdFhZKd9OwpZSk69ca1c4f'),
    ]
)]
class AuthTokenResponseSchema {}
