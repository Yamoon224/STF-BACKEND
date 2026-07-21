<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Scholarship',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'title', type: 'string', example: "Bourse d'excellence STF"),
        new OA\Property(property: 'provider', type: 'string', nullable: true, description: 'Organisme qui octroie la bourse.'),
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(property: 'amount', type: 'string', nullable: true, example: '500 000 FCFA'),
        new OA\Property(property: 'audience', type: 'string', nullable: true, example: 'Licence · Master'),
        new OA\Property(property: 'deadline', type: 'string', format: 'date', nullable: true),
        new OA\Property(property: 'application_url', type: 'string', nullable: true),
        new OA\Property(property: 'image_path', type: 'string', nullable: true),
        new OA\Property(property: 'image_url', type: 'string', nullable: true, description: 'URL publique absolue de la bourse, ou null.'),
        new OA\Property(property: 'status', type: 'string', enum: ['ouverte', 'fermee', 'a_venir'], example: 'ouverte'),
        new OA\Property(property: 'order', type: 'integer', example: 0),
    ]
)]
class ScholarshipSchema {}
