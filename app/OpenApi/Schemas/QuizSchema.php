<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Quiz',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'module_id', type: 'integer', example: 1),
        new OA\Property(property: 'title', type: 'string', example: 'Quiz Fondations STIM'),
        new OA\Property(property: 'passing_score', type: 'integer', example: 70),
    ]
)]
class QuizSchema {}
