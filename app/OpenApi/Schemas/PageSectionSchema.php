<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'PageSection',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'page_key', type: 'string', example: 'a-propos'),
        new OA\Property(property: 'section_key', type: 'string', example: 'hero'),
        new OA\Property(property: 'type', type: 'string', example: 'hero'),
        new OA\Property(property: 'payload', type: 'object'),
        new OA\Property(property: 'order', type: 'integer', example: 0),
    ]
)]
class PageSectionSchema {}
