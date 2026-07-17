<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Testimonial',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Aïcha D.'),
        new OA\Property(property: 'role', type: 'string', example: 'Mentée — Programme Mentorat STIM'),
        new OA\Property(property: 'quote', type: 'string'),
        new OA\Property(property: 'program_id', type: 'integer', nullable: true),
        new OA\Property(property: 'order', type: 'integer', example: 0),
    ]
)]
class TestimonialSchema {}
