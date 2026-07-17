<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Message',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'conversation_id', type: 'integer', example: 1),
        new OA\Property(property: 'sender_id', type: 'integer', example: 3),
        new OA\Property(property: 'body', type: 'string', example: 'Bravo pour ta présentation, on en reparle jeudi.'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'sender', ref: '#/components/schemas/UserRef', nullable: true),
    ]
)]
class MessageSchema {}
