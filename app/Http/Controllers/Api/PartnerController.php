<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class PartnerController extends Controller
{
    #[OA\Get(
        path: '/partners',
        summary: 'Lister les partenaires',
        description: 'Public.',
        tags: ['Partenaires'],
        responses: [new OA\Response(response: 200, description: 'Partenaires', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Partner')))]
    )]
    public function index()
    {
        return Partner::orderBy('order')->get();
    }

    #[OA\Post(
        path: '/partners',
        summary: 'Ajouter un partenaire',
        security: [['bearerAuth' => []]],
        tags: ['Partenaires'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(required: ['name'], properties: [
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'logo_path', type: 'string', nullable: true),
                new OA\Property(property: 'url', type: 'string', format: 'uri', nullable: true),
                new OA\Property(property: 'order', type: 'integer', minimum: 0, nullable: true),
            ])
        ),
        responses: [
            new OA\Response(response: 201, description: 'Créé', content: new OA\JsonContent(ref: '#/components/schemas/Partner')),
            new OA\Response(response: 403, description: "Permission `cms.manage` requise"),
        ]
    )]
    public function store(Request $request)
    {
        abort_unless($request->user()->can('cms.manage'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'logo_path' => ['nullable', 'string'],
            'url' => ['nullable', 'url'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        return response()->json(Partner::create($data), 201);
    }

    #[OA\Patch(
        path: '/partners/{partner}',
        summary: 'Modifier un partenaire',
        security: [['bearerAuth' => []]],
        tags: ['Partenaires'],
        parameters: [new OA\PathParameter(name: 'partner', schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(properties: [
            new OA\Property(property: 'name', type: 'string'),
            new OA\Property(property: 'logo_path', type: 'string', nullable: true),
            new OA\Property(property: 'url', type: 'string', format: 'uri', nullable: true),
            new OA\Property(property: 'order', type: 'integer', minimum: 0, nullable: true),
        ])),
        responses: [
            new OA\Response(response: 200, description: 'Modifié', content: new OA\JsonContent(ref: '#/components/schemas/Partner')),
            new OA\Response(response: 403, description: "Permission `cms.manage` requise"),
        ]
    )]
    public function update(Request $request, Partner $partner)
    {
        abort_unless($request->user()->can('cms.manage'), 403);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'logo_path' => ['nullable', 'string'],
            'url' => ['nullable', 'url'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        $partner->update($data);

        return $partner;
    }

    #[OA\Delete(
        path: '/partners/{partner}',
        summary: 'Supprimer un partenaire',
        security: [['bearerAuth' => []]],
        tags: ['Partenaires'],
        parameters: [new OA\PathParameter(name: 'partner', schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 204, description: 'Supprimé'),
            new OA\Response(response: 403, description: "Permission `cms.manage` requise"),
        ]
    )]
    public function destroy(Request $request, Partner $partner)
    {
        abort_unless($request->user()->can('cms.manage'), 403);

        $partner->delete();

        return response()->noContent();
    }
}
