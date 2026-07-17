<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'MenteeProfile',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'user_id', type: 'integer', example: 7),
        new OA\Property(property: 'level', type: 'string', nullable: true, example: 'Terminale scientifique'),
        new OA\Property(property: 'school', type: 'string', nullable: true),
        new OA\Property(property: 'interests', type: 'string', nullable: true),
        new OA\Property(property: 'guardian_name', type: 'string', nullable: true),
        new OA\Property(property: 'guardian_contact', type: 'string', nullable: true),
    ]
)]
class MenteeProfileSchema {}
