<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Certificate',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'user_id', type: 'integer', example: 7),
        new OA\Property(property: 'program_id', type: 'integer', nullable: true),
        new OA\Property(property: 'title', type: 'string', example: 'Certificat — Fondations STIM'),
        new OA\Property(property: 'serial_number', type: 'string', example: 'STF-AB12CD34'),
        new OA\Property(property: 'issued_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'file_path', type: 'string', nullable: true),
    ]
)]
class CertificateSchema {}
