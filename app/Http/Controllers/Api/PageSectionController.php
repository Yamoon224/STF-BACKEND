<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PageSection;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class PageSectionController extends Controller
{
    #[OA\Get(
        path: '/page-sections',
        summary: 'Lister les sections de contenu éditables des pages statiques',
        description: 'Public.',
        tags: ['Sections de page'],
        parameters: [new OA\QueryParameter(name: 'page', schema: new OA\Schema(type: 'string', example: 'a-propos'))],
        responses: [new OA\Response(response: 200, description: 'Sections', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/PageSection')))]
    )]
    public function index(Request $request)
    {
        return PageSection::query()
            ->when($request->query('page'), fn ($q, $page) => $q->where('page_key', $page))
            ->orderBy('page_key')
            ->orderBy('order')
            ->get();
    }

    #[OA\Patch(
        path: '/page-sections/{pageSection}',
        summary: 'Modifier le contenu d\'une section de page',
        security: [['bearerAuth' => []]],
        tags: ['Sections de page'],
        parameters: [new OA\PathParameter(name: 'pageSection', schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(required: ['payload'], properties: [
                new OA\Property(property: 'payload', type: 'object'),
            ])
        ),
        responses: [
            new OA\Response(response: 200, description: 'Modifiée', content: new OA\JsonContent(ref: '#/components/schemas/PageSection')),
            new OA\Response(response: 403, description: "Permission `cms.manage` requise"),
        ]
    )]
    public function update(Request $request, PageSection $pageSection)
    {
        abort_unless($request->user()->can('cms.manage'), 403);

        $data = $request->validate([
            'payload' => ['required', 'array'],
        ]);

        $pageSection->update($data);

        return $pageSection;
    }
}
