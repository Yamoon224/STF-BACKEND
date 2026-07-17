<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'MfaChallengeResponse',
    title: 'Double authentification requise',
    properties: [
        new OA\Property(property: 'mfa_required', type: 'boolean', example: true),
        new OA\Property(property: 'mfa_challenge', type: 'string', example: '21b98ac6-13e1-4162-b1f9-0040862bf2e1'),
    ]
)]
class MfaChallengeResponseSchema {}
