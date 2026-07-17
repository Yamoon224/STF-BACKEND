<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Module',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'program_id', type: 'integer', nullable: true),
        new OA\Property(property: 'title', type: 'string', example: 'Fondations STIM'),
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(property: 'order', type: 'integer', example: 1),
        new OA\Property(property: 'status', type: 'string', enum: ['brouillon', 'publie']),
        new OA\Property(property: 'my_progress', type: 'integer', nullable: true, example: 70, description: 'Présent uniquement pour une requête authentifiée'),
    ]
)]
class ModuleSchema {}
