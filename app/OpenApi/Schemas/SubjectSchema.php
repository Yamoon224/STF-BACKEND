<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Subject',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Mathématiques'),
        new OA\Property(property: 'slug', type: 'string', example: 'mathematiques'),
    ]
)]
class SubjectSchema {}
