<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'QuizAttempt',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'quiz_id', type: 'integer', example: 1),
        new OA\Property(property: 'user_id', type: 'integer', example: 7),
        new OA\Property(property: 'score', type: 'integer', example: 100),
        new OA\Property(property: 'passed', type: 'boolean', example: true),
        new OA\Property(property: 'answers', type: 'object'),
        new OA\Property(property: 'submitted_at', type: 'string', format: 'date-time'),
    ]
)]
class QuizAttemptSchema {}
