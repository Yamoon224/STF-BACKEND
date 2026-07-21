<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
        description: 'Le logo est optionnel.',
        security: [['bearerAuth' => []]],
        tags: ['Partenaires'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(type: 'object', required: ['name'], properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'logo', type: 'string', format: 'binary', nullable: true),
                    new OA\Property(property: 'url', type: 'string', format: 'uri', nullable: true),
                    new OA\Property(property: 'order', type: 'integer', minimum: 0, nullable: true),
                    new OA\Property(property: 'type', type: 'string', enum: ['confiance', 'partenaire'], nullable: true, description: "`confiance` : \"Ils nous font confiance\" (partenaires avec qui STF a déjà travaillé). `partenaire` : partenaires au sens large. Défaut : `confiance`."),
                ])
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Créé', content: new OA\JsonContent(ref: '#/components/schemas/Partner')),
            new OA\Response(response: 403, description: "Permission `cms.manage` requise"),
        ]
    )]
    public function store(Request $request)
    {
        abort_unless($request->user()->can('cms.manage'), 403);

        if ($request->input('url') === '') {
            $request->merge(['url' => null]);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'url' => ['nullable', 'url'],
            'order' => ['nullable', 'integer', 'min:0'],
            'type' => ['nullable', 'in:confiance,partenaire'],
        ]);

        unset($data['logo']);
        $data['type'] ??= 'confiance';

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('partners', 'public');
        }

        return response()->json(Partner::create($data), 201);
    }

    #[OA\Patch(
        path: '/partners/{partner}',
        summary: 'Modifier un partenaire',
        description: "Le logo est optionnel. Envoyer `remove_logo=1` pour retirer le logo existant sans en fournir un nouveau. Multipart requiert `_method=PATCH` avec une requête POST.",
        security: [['bearerAuth' => []]],
        tags: ['Partenaires'],
        parameters: [new OA\PathParameter(name: 'partner', schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(type: 'object', properties: [
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'logo', type: 'string', format: 'binary', nullable: true),
                new OA\Property(property: 'remove_logo', type: 'boolean', nullable: true),
                new OA\Property(property: 'url', type: 'string', format: 'uri', nullable: true),
                new OA\Property(property: 'order', type: 'integer', minimum: 0, nullable: true),
                new OA\Property(property: 'type', type: 'string', enum: ['confiance', 'partenaire'], nullable: true),
            ])
        )),
        responses: [
            new OA\Response(response: 200, description: 'Modifié', content: new OA\JsonContent(ref: '#/components/schemas/Partner')),
            new OA\Response(response: 403, description: "Permission `cms.manage` requise"),
        ]
    )]
    public function update(Request $request, Partner $partner)
    {
        abort_unless($request->user()->can('cms.manage'), 403);

        if ($request->input('url') === '') {
            $request->merge(['url' => null]);
        }

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
            'url' => ['nullable', 'url'],
            'order' => ['nullable', 'integer', 'min:0'],
            'type' => ['nullable', 'in:confiance,partenaire'],
        ]);

        unset($data['logo'], $data['remove_logo']);

        if ($request->hasFile('logo')) {
            if ($partner->logo_path) {
                Storage::disk('public')->delete($partner->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('partners', 'public');
        } elseif ($request->boolean('remove_logo')) {
            if ($partner->logo_path) {
                Storage::disk('public')->delete($partner->logo_path);
            }
            $data['logo_path'] = null;
        }

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

        if ($partner->logo_path) {
            Storage::disk('public')->delete($partner->logo_path);
        }

        $partner->delete();

        return response()->noContent();
    }
}
