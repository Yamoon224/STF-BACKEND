<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'LiveSession',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'course_id', type: 'integer', example: 1),
        new OA\Property(property: 'title', type: 'string', example: 'Session de révision — Fonctions et dérivées'),
        new OA\Property(property: 'scheduled_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'duration_minutes', type: 'integer', example: 60),
        new OA\Property(property: 'meeting_link', type: 'string', nullable: true),
        new OA\Property(property: 'status', type: 'string', enum: ['a_venir', 'en_cours', 'termine']),
    ]
)]
class LiveSessionSchema {}
