<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'MessageResponse',
    title: 'Réponse simple',
    properties: [
        new OA\Property(property: 'message', type: 'string'),
    ]
)]
class MessageResponseSchema {}
