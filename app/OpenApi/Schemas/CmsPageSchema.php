<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CmsPage',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'title', type: 'string', example: 'Lancement de la cohorte 2026'),
        new OA\Property(property: 'slug', type: 'string', example: 'lancement-cohorte-2026'),
        new OA\Property(property: 'type', type: 'string', enum: ['page', 'article']),
        new OA\Property(property: 'body', type: 'string', nullable: true),
        new OA\Property(property: 'excerpt', type: 'string', nullable: true),
        new OA\Property(property: 'category', type: 'string', nullable: true),
        new OA\Property(property: 'status', type: 'string', enum: ['brouillon', 'publie']),
        new OA\Property(property: 'author_id', type: 'integer', nullable: true),
        new OA\Property(property: 'published_at', type: 'string', format: 'date-time', nullable: true),
    ]
)]
class CmsPageSchema {}
