<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Role',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', enum: ['admin', 'staff', 'mentor', 'mentee', 'donor']),
        new OA\Property(property: 'permissions', type: 'array', items: new OA\Items(type: 'string')),
    ]
)]
class RoleSchema {}
