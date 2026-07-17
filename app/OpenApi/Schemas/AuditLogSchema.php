<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AuditLog',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'actor_id', type: 'integer', nullable: true),
        new OA\Property(property: 'action', type: 'string', example: 'mentore.validee'),
        new OA\Property(property: 'target_type', type: 'string', nullable: true),
        new OA\Property(property: 'target_id', type: 'integer', nullable: true),
        new OA\Property(property: 'meta', type: 'object', nullable: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'actor', ref: '#/components/schemas/UserRef', nullable: true),
    ]
)]
class AuditLogSchema {}
