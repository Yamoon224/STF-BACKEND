<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class TestimonialController extends Controller
{
    #[OA\Get(
        path: '/testimonials',
        summary: 'Lister les témoignages',
        description: 'Public.',
        tags: ['Témoignages'],
        parameters: [new OA\QueryParameter(name: 'program_id', schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Témoignages', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Testimonial')))]
    )]
    public function index(Request $request)
    {
        return Testimonial::query()
            ->when($request->query('program_id'), fn ($q, $id) => $q->where('program_id', $id))
            ->orderBy('order')
            ->get();
    }

    #[OA\Post(
        path: '/testimonials',
        summary: 'Ajouter un témoignage',
        security: [['bearerAuth' => []]],
        tags: ['Témoignages'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(required: ['name', 'role', 'quote'], properties: [
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'role', type: 'string'),
                new OA\Property(property: 'quote', type: 'string'),
                new OA\Property(property: 'program_id', type: 'integer', nullable: true),
                new OA\Property(property: 'order', type: 'integer', minimum: 0, nullable: true),
            ])
        ),
        responses: [
            new OA\Response(response: 201, description: 'Créé', content: new OA\JsonContent(ref: '#/components/schemas/Testimonial')),
            new OA\Response(response: 403, description: "Permission `cms.manage` requise"),
        ]
    )]
    public function store(Request $request)
    {
        abort_unless($request->user()->can('cms.manage'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'string', 'max:255'],
            'quote' => ['required', 'string'],
            'program_id' => ['nullable', 'exists:programs,id'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        return response()->json(Testimonial::create($data), 201);
    }

    #[OA\Patch(
        path: '/testimonials/{testimonial}',
        summary: 'Modifier un témoignage',
        security: [['bearerAuth' => []]],
        tags: ['Témoignages'],
        parameters: [new OA\PathParameter(name: 'testimonial', schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(properties: [
            new OA\Property(property: 'name', type: 'string'),
            new OA\Property(property: 'role', type: 'string'),
            new OA\Property(property: 'quote', type: 'string'),
            new OA\Property(property: 'program_id', type: 'integer', nullable: true),
            new OA\Property(property: 'order', type: 'integer', minimum: 0, nullable: true),
        ])),
        responses: [
            new OA\Response(response: 200, description: 'Modifié', content: new OA\JsonContent(ref: '#/components/schemas/Testimonial')),
            new OA\Response(response: 403, description: "Permission `cms.manage` requise"),
        ]
    )]
    public function update(Request $request, Testimonial $testimonial)
    {
        abort_unless($request->user()->can('cms.manage'), 403);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'role' => ['sometimes', 'string', 'max:255'],
            'quote' => ['sometimes', 'string'],
            'program_id' => ['nullable', 'exists:programs,id'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        $testimonial->update($data);

        return $testimonial;
    }

    #[OA\Delete(
        path: '/testimonials/{testimonial}',
        summary: 'Supprimer un témoignage',
        security: [['bearerAuth' => []]],
        tags: ['Témoignages'],
        parameters: [new OA\PathParameter(name: 'testimonial', schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 204, description: 'Supprimé'),
            new OA\Response(response: 403, description: "Permission `cms.manage` requise"),
        ]
    )]
    public function destroy(Request $request, Testimonial $testimonial)
    {
        abort_unless($request->user()->can('cms.manage'), 403);

        $testimonial->delete();

        return response()->noContent();
    }
}
