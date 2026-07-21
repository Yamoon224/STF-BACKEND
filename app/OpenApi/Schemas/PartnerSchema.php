<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Partner',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Fondation Numérique'),
        new OA\Property(property: 'logo_path', type: 'string', nullable: true),
        new OA\Property(property: 'logo_url', type: 'string', nullable: true, description: 'URL publique absolue du logo, ou null.'),
        new OA\Property(property: 'url', type: 'string', nullable: true),
        new OA\Property(property: 'order', type: 'integer', example: 0),
        new OA\Property(property: 'type', type: 'string', enum: ['confiance', 'partenaire'], example: 'confiance', description: "`confiance` : \"Ils nous font confiance\" (partenaires avec qui STF a déjà travaillé). `partenaire` : partenaires au sens large (ex. AFD, CEDD)."),
    ]
)]
class PartnerSchema {}
