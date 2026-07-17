<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Level',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: '6e & 5e'),
        new OA\Property(property: 'slug', type: 'string', example: '6e-5e'),
        new OA\Property(property: 'order', type: 'integer', example: 1),
    ]
)]
class LevelSchema {}
