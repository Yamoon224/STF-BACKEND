<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Report',
    title: 'Signalement',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'reporter_id', type: 'integer', example: 7),
        new OA\Property(property: 'context_type', type: 'string', example: 'messagerie_pairing'),
        new OA\Property(property: 'context_id', type: 'integer', nullable: true),
        new OA\Property(property: 'description', type: 'string'),
        new OA\Property(property: 'status', type: 'string', enum: ['nouveau', 'en_cours', 'resolu']),
        new OA\Property(property: 'resolved_by', type: 'integer', nullable: true),
        new OA\Property(property: 'resolved_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'reporter', ref: '#/components/schemas/UserRef', nullable: true),
    ]
)]
class ReportSchema {}
