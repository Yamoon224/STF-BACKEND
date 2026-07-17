<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Cohort',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'program_id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Cohorte Lycée 2026'),
        new OA\Property(property: 'start_date', type: 'string', format: 'date', nullable: true),
        new OA\Property(property: 'end_date', type: 'string', format: 'date', nullable: true),
        new OA\Property(property: 'status', type: 'string', enum: ['a_venir', 'en_cours', 'termine']),
    ]
)]
class CohortSchema {}
