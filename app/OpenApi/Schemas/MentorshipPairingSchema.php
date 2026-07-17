<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'MentorshipPairing',
    title: 'Binôme de mentorat',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'mentee_id', type: 'integer', example: 7),
        new OA\Property(property: 'mentor_id', type: 'integer', nullable: true, example: 3),
        new OA\Property(property: 'program_id', type: 'integer', example: 1),
        new OA\Property(property: 'cohort_id', type: 'integer', nullable: true),
        new OA\Property(property: 'status', type: 'string', enum: ['en_attente', 'actif', 'pause', 'termine']),
        new OA\Property(property: 'match_score', type: 'integer', nullable: true, example: 92),
        new OA\Property(property: 'matched_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'ended_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'notes', type: 'string', nullable: true),
        new OA\Property(property: 'mentee', ref: '#/components/schemas/UserRef'),
        new OA\Property(property: 'mentor', ref: '#/components/schemas/UserRef', nullable: true),
        new OA\Property(property: 'program', ref: '#/components/schemas/Program'),
        new OA\Property(property: 'sessions_realisees_count', type: 'integer', nullable: true),
    ]
)]
class MentorshipPairingSchema {}
