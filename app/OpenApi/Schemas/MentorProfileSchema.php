<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'MentorProfile',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'user_id', type: 'integer', example: 3),
        new OA\Property(property: 'expertise', type: 'string', example: 'Ingénieure logiciel'),
        new OA\Property(property: 'bio', type: 'string', nullable: true),
        new OA\Property(property: 'capacity', type: 'integer', example: 4),
        new OA\Property(property: 'validated_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'validated_by', type: 'integer', nullable: true),
    ]
)]
class MentorProfileSchema {}
