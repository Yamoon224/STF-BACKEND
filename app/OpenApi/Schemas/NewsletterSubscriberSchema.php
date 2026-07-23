<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'NewsletterSubscriber',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'email', type: 'string', format: 'email'),
        new OA\Property(property: 'name', type: 'string', nullable: true),
        new OA\Property(property: 'status', type: 'string', enum: ['actif', 'desabonne']),
        new OA\Property(property: 'subscribed_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'unsubscribed_at', type: 'string', format: 'date-time', nullable: true),
    ]
)]
class NewsletterSubscriberSchema {}
