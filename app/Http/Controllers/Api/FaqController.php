<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class FaqController extends Controller
{
    #[OA\Get(
        path: '/faqs',
        summary: 'Lister les questions fréquentes',
        description: 'Public.',
        tags: ['FAQ'],
        parameters: [new OA\QueryParameter(name: 'category', schema: new OA\Schema(type: 'string', example: 'mentorat'))],
        responses: [new OA\Response(response: 200, description: 'FAQ', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Faq')))]
    )]
    public function index(Request $request)
    {
        return Faq::query()
            ->when($request->query('category'), fn ($q, $category) => $q->where('category', $category))
            ->orderBy('order')
            ->get();
    }

    #[OA\Post(
        path: '/faqs',
        summary: 'Ajouter une question fréquente',
        security: [['bearerAuth' => []]],
        tags: ['FAQ'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(required: ['question', 'answer'], properties: [
                new OA\Property(property: 'question', type: 'string'),
                new OA\Property(property: 'answer', type: 'string'),
                new OA\Property(property: 'category', type: 'string', nullable: true),
                new OA\Property(property: 'order', type: 'integer', minimum: 0, nullable: true),
            ])
        ),
        responses: [
            new OA\Response(response: 201, description: 'Créée', content: new OA\JsonContent(ref: '#/components/schemas/Faq')),
            new OA\Response(response: 403, description: "Permission `cms.manage` requise"),
        ]
    )]
    public function store(Request $request)
    {
        abort_unless($request->user()->can('cms.manage'), 403);

        $data = $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string'],
            'category' => ['nullable', 'string', 'max:255'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        return response()->json(Faq::create($data), 201);
    }

    #[OA\Patch(
        path: '/faqs/{faq}',
        summary: 'Modifier une question fréquente',
        security: [['bearerAuth' => []]],
        tags: ['FAQ'],
        parameters: [new OA\PathParameter(name: 'faq', schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(properties: [
            new OA\Property(property: 'question', type: 'string'),
            new OA\Property(property: 'answer', type: 'string'),
            new OA\Property(property: 'category', type: 'string', nullable: true),
            new OA\Property(property: 'order', type: 'integer', minimum: 0, nullable: true),
        ])),
        responses: [
            new OA\Response(response: 200, description: 'Modifiée', content: new OA\JsonContent(ref: '#/components/schemas/Faq')),
            new OA\Response(response: 403, description: "Permission `cms.manage` requise"),
        ]
    )]
    public function update(Request $request, Faq $faq)
    {
        abort_unless($request->user()->can('cms.manage'), 403);

        $data = $request->validate([
            'question' => ['sometimes', 'string', 'max:255'],
            'answer' => ['sometimes', 'string'],
            'category' => ['nullable', 'string', 'max:255'],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        $faq->update($data);

        return $faq;
    }

    #[OA\Delete(
        path: '/faqs/{faq}',
        summary: 'Supprimer une question fréquente',
        security: [['bearerAuth' => []]],
        tags: ['FAQ'],
        parameters: [new OA\PathParameter(name: 'faq', schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 204, description: 'Supprimée'),
            new OA\Response(response: 403, description: "Permission `cms.manage` requise"),
        ]
    )]
    public function destroy(Request $request, Faq $faq)
    {
        abort_unless($request->user()->can('cms.manage'), 403);

        $faq->delete();

        return response()->noContent();
    }
}
