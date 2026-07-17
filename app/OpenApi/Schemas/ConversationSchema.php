<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Conversation',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'subject', type: 'string', nullable: true),
        new OA\Property(property: 'context_type', type: 'string', nullable: true),
        new OA\Property(property: 'context_id', type: 'integer', nullable: true),
        new OA\Property(
            property: 'participants',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/UserRef')
        ),
        new OA\Property(
            property: 'messages',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/Message'),
            description: 'Dernier message uniquement dans le listing'
        ),
    ]
)]
class ConversationSchema {}
