<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Badge',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'title', type: 'string', example: 'Fondations STIM'),
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(property: 'icon', type: 'string', nullable: true),
        new OA\Property(property: 'criteria', type: 'string', nullable: true),
        new OA\Property(
            property: 'pivot',
            type: 'object',
            nullable: true,
            properties: [
                new OA\Property(property: 'awarded_at', type: 'string', format: 'date-time'),
                new OA\Property(property: 'awarded_by', type: 'integer', nullable: true),
            ]
        ),
    ]
)]
class BadgeSchema {}
