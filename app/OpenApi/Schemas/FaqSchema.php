<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Faq',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'question', type: 'string', example: 'Qui peut devenir mentée ?'),
        new OA\Property(property: 'answer', type: 'string'),
        new OA\Property(property: 'category', type: 'string', nullable: true, example: 'mentorat'),
        new OA\Property(property: 'order', type: 'integer', example: 0),
    ]
)]
class FaqSchema {}
