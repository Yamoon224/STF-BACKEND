<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'QuizOptionInput',
    required: ['label'],
    properties: [
        new OA\Property(property: 'label', type: 'string', example: 'Vrai'),
        new OA\Property(property: 'is_correct', type: 'boolean', example: true),
    ]
)]
class QuizOptionInputSchema {}
