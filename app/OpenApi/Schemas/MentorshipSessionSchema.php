<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'MentorshipSession',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'pairing_id', type: 'integer', example: 1),
        new OA\Property(property: 'scheduled_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'duration_minutes', type: 'integer', example: 60),
        new OA\Property(property: 'status', type: 'string', enum: ['en_attente', 'confirmee', 'realisee', 'annulee']),
        new OA\Property(property: 'topic', type: 'string', nullable: true),
        new OA\Property(property: 'location_or_link', type: 'string', nullable: true),
        new OA\Property(property: 'created_by', type: 'integer', nullable: true),
        new OA\Property(property: 'pairing', ref: '#/components/schemas/MentorshipPairing', nullable: true),
    ]
)]
class MentorshipSessionSchema {}
