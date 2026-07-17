<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'QuizQuestionInput',
    required: ['question', 'type', 'options'],
    properties: [
        new OA\Property(property: 'question', type: 'string'),
        new OA\Property(property: 'type', type: 'string', enum: ['unique', 'multiple']),
        new OA\Property(property: 'options', type: 'array', items: new OA\Items(ref: '#/components/schemas/QuizOptionInput'), minItems: 2),
    ]
)]
class QuizQuestionInputSchema {}
