<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Group',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Cohorte Lycée Abidjan 2026'),
        new OA\Property(property: 'type', type: 'string', enum: ['automatique', 'travail', 'mentorat']),
        new OA\Property(property: 'program_id', type: 'integer', nullable: true),
        new OA\Property(property: 'status', type: 'string', enum: ['en_validation', 'actif', 'archive']),
        new OA\Property(property: 'created_by', type: 'integer', nullable: true),
        new OA\Property(property: 'members_count', type: 'integer', nullable: true),
    ]
)]
class GroupSchema {}
