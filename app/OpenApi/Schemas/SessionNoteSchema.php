<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'SessionNote',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'session_id', type: 'integer', example: 1),
        new OA\Property(property: 'author_id', type: 'integer', example: 3),
        new OA\Property(property: 'content', type: 'string'),
        new OA\Property(property: 'visibility', type: 'string', enum: ['partagee', 'privee']),
        new OA\Property(property: 'author', ref: '#/components/schemas/UserRef', nullable: true),
    ]
)]
class SessionNoteSchema {}
