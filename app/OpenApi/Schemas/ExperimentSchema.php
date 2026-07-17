<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Experiment',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'subject_id', type: 'integer', example: 1),
        new OA\Property(property: 'level_id', type: 'integer', nullable: true),
        new OA\Property(property: 'course_id', type: 'integer', nullable: true),
        new OA\Property(property: 'title', type: 'string', example: 'Dosage acide-base'),
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(property: 'instructions', type: 'string', nullable: true),
        new OA\Property(property: 'order', type: 'integer', example: 1),
        new OA\Property(property: 'status', type: 'string', enum: ['brouillon', 'publie']),
    ]
)]
class ExperimentSchema {}
