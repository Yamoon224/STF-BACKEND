<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Partner',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Fondation Numérique'),
        new OA\Property(property: 'logo_path', type: 'string', nullable: true),
        new OA\Property(property: 'url', type: 'string', nullable: true),
        new OA\Property(property: 'order', type: 'integer', example: 0),
    ]
)]
class PartnerSchema {}
