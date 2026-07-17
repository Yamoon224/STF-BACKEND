<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'GroupFile',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'group_id', type: 'integer', example: 1),
        new OA\Property(property: 'uploader_id', type: 'integer', example: 3),
        new OA\Property(property: 'name', type: 'string', example: 'guide.pdf'),
        new OA\Property(property: 'path', type: 'string'),
        new OA\Property(property: 'mime_type', type: 'string', nullable: true),
        new OA\Property(property: 'size_bytes', type: 'integer', nullable: true),
        new OA\Property(property: 'uploader', ref: '#/components/schemas/UserRef', nullable: true),
    ]
)]
class GroupFileSchema {}
