<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Project',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'mentee_id', type: 'integer', example: 7),
        new OA\Property(property: 'pairing_id', type: 'integer', nullable: true),
        new OA\Property(property: 'title', type: 'string', example: 'Application de suivi des devoirs'),
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(property: 'status', type: 'string', enum: ['brouillon', 'soumis', 'en_validation', 'valide', 'rejete']),
        new OA\Property(property: 'file_path', type: 'string', nullable: true),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
class ProjectSchema {}
