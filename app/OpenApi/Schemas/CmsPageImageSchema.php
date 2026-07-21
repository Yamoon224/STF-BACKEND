<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CmsPageImage',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'cms_page_id', type: 'integer', example: 1),
        new OA\Property(property: 'image_path', type: 'string'),
        new OA\Property(property: 'image_url', type: 'string', nullable: true, description: 'URL publique absolue.'),
        new OA\Property(property: 'order', type: 'integer', example: 0),
    ]
)]
class CmsPageImageSchema {}
