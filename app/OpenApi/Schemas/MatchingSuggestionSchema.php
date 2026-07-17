<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'MatchingSuggestion',
    properties: [
        new OA\Property(property: 'pairing_id', type: 'integer', example: 3),
        new OA\Property(property: 'mentee', ref: '#/components/schemas/UserRef'),
        new OA\Property(property: 'program', ref: '#/components/schemas/Program'),
        new OA\Property(property: 'suggested_mentor', ref: '#/components/schemas/UserRef', nullable: true),
        new OA\Property(property: 'score', type: 'integer', nullable: true, example: 75),
    ]
)]
class MatchingSuggestionSchema {}
