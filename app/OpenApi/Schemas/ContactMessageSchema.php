<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ContactMessage',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Visiteur'),
        new OA\Property(property: 'email', type: 'string', format: 'email'),
        new OA\Property(property: 'audience', type: 'string', enum: ['generale', 'mentorat', 'partenaire', 'institution', 'media']),
        new OA\Property(property: 'subject', type: 'string'),
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'status', type: 'string', enum: ['nouveau', 'traite']),
    ]
)]
class ContactMessageSchema {}
