<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'User',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 7),
        new OA\Property(property: 'name', type: 'string', example: 'Aïcha Diallo'),
        new OA\Property(property: 'email', type: 'string', format: 'email'),
        new OA\Property(property: 'status', type: 'string', enum: ['pending', 'active', 'suspended']),
        new OA\Property(property: 'country', type: 'string', nullable: true, example: "Côte d'Ivoire"),
        new OA\Property(property: 'phone', type: 'string', nullable: true),
        new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string'), example: ['mentee']),
        new OA\Property(property: 'permissions', type: 'array', items: new OA\Items(type: 'string')),
        new OA\Property(property: 'mfa_enabled', type: 'boolean'),
        new OA\Property(property: 'last_login_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'mentor_profile', ref: '#/components/schemas/MentorProfile', nullable: true),
        new OA\Property(property: 'mentee_profile', ref: '#/components/schemas/MenteeProfile', nullable: true),
        new OA\Property(property: 'badges', type: 'array', items: new OA\Items(ref: '#/components/schemas/Badge')),
        new OA\Property(property: 'certificates', type: 'array', items: new OA\Items(ref: '#/components/schemas/Certificate')),
    ]
)]
class UserSchema {}
