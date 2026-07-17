<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Program',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Mentorat STIM'),
        new OA\Property(property: 'slug', type: 'string', example: 'mentorat-stim'),
        new OA\Property(property: 'audience', type: 'string', nullable: true, example: 'Collège · Lycée · Université'),
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(property: 'color', type: 'string', nullable: true, example: 'blue'),
        new OA\Property(property: 'status', type: 'string', enum: ['a_venir', 'en_cours', 'archive']),
        new OA\Property(property: 'cycle_start', type: 'string', format: 'date', nullable: true),
        new OA\Property(property: 'cycle_end', type: 'string', format: 'date', nullable: true),
        new OA\Property(property: 'cohorts_count', type: 'integer', nullable: true),
        new OA\Property(property: 'mentees_count', type: 'integer', nullable: true),
    ]
)]
class ProgramSchema {}
