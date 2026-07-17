<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UserRef',
    title: 'Utilisatrice (référence courte)',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 7),
        new OA\Property(property: 'name', type: 'string', example: 'Aïcha Diallo'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'aicha.diallo@example.org'),
    ]
)]
class UserRefSchema {}
