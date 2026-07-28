<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'MemberProfile',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'user_id', type: 'integer', example: 9),
        new OA\Property(property: 'validated_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'validated_by', type: 'integer', nullable: true),
        new OA\Property(property: 'payment_proof_available', type: 'boolean', example: true),
    ]
)]
class MemberProfileSchema {}
