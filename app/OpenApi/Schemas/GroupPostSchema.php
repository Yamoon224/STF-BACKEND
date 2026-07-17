<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'GroupPost',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'group_id', type: 'integer', example: 1),
        new OA\Property(property: 'author_id', type: 'integer', example: 7),
        new OA\Property(property: 'content', type: 'string'),
        new OA\Property(property: 'author', ref: '#/components/schemas/UserRef', nullable: true),
    ]
)]
class GroupPostSchema {}
