<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Scholarship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

class ScholarshipController extends Controller
{
    #[OA\Get(
        path: '/scholarships',
        summary: 'Lister les bourses',
        description: 'Public.',
        tags: ['Bourses'],
        responses: [new OA\Response(response: 200, description: 'Bourses', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Scholarship')))]
    )]
    public function index()
    {
        return Scholarship::orderBy('order')->orderBy('deadline')->get();
    }

    #[OA\Post(
        path: '/scholarships',
        summary: 'Ajouter une bourse',
        description: "L'image est optionnelle.",
        security: [['bearerAuth' => []]],
        tags: ['Bourses'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(type: 'object', required: ['title'], properties: [
                    new OA\Property(property: 'title', type: 'string', example: "Bourse d'excellence STF"),
                    new OA\Property(property: 'provider', type: 'string', nullable: true, description: 'Organisme qui octroie la bourse.'),
                    new OA\Property(property: 'description', type: 'string', nullable: true),
                    new OA\Property(property: 'amount', type: 'string', nullable: true, example: '500 000 FCFA'),
                    new OA\Property(property: 'audience', type: 'string', nullable: true, example: 'Licence · Master'),
                    new OA\Property(property: 'deadline', type: 'string', format: 'date', nullable: true),
                    new OA\Property(property: 'application_url', type: 'string', format: 'uri', nullable: true),
                    new OA\Property(property: 'image', type: 'string', format: 'binary', nullable: true),
                    new OA\Property(property: 'status', type: 'string', enum: ['ouverte', 'fermee', 'a_venir'], nullable: true),
                    new OA\Property(property: 'order', type: 'integer', minimum: 0, nullable: true),
                ])
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Créée', content: new OA\JsonContent(ref: '#/components/schemas/Scholarship')),
            new OA\Response(response: 403, description: "Permission `cms.manage` requise"),
        ]
    )]
    public function store(Request $request)
    {
        abort_unless($request->user()->can('cms.manage'), 403);

        foreach (['provider', 'description', 'amount', 'audience', 'deadline', 'application_url'] as $field) {
            if ($request->input($field) === '') {
                $request->merge([$field => null]);
            }
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'provider' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'amount' => ['nullable', 'string', 'max:255'],
            'audience' => ['nullable', 'string', 'max:255'],
            'deadline' => ['nullable', 'date'],
            'application_url' => ['nullable', 'url'],
            'image' => ['nullable', 'image', 'max:4096'],
            'status' => [Rule::in(['ouverte', 'fermee', 'a_venir'])],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        unset($data['image']);
        $data['status'] ??= 'ouverte';

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('scholarships', 'public');
        }

        return response()->json(Scholarship::create($data), 201);
    }

    #[OA\Patch(
        path: '/scholarships/{scholarship}',
        summary: 'Modifier une bourse',
        description: "L'image est optionnelle. Envoyer `remove_image=1` pour retirer l'image existante sans en fournir une nouvelle. Multipart requiert `_method=PATCH` avec une requête POST.",
        security: [['bearerAuth' => []]],
        tags: ['Bourses'],
        parameters: [new OA\PathParameter(name: 'scholarship', schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(type: 'object', properties: [
                new OA\Property(property: 'title', type: 'string'),
                new OA\Property(property: 'provider', type: 'string', nullable: true),
                new OA\Property(property: 'description', type: 'string', nullable: true),
                new OA\Property(property: 'amount', type: 'string', nullable: true),
                new OA\Property(property: 'audience', type: 'string', nullable: true),
                new OA\Property(property: 'deadline', type: 'string', format: 'date', nullable: true),
                new OA\Property(property: 'application_url', type: 'string', format: 'uri', nullable: true),
                new OA\Property(property: 'image', type: 'string', format: 'binary', nullable: true),
                new OA\Property(property: 'remove_image', type: 'boolean', nullable: true),
                new OA\Property(property: 'status', type: 'string', enum: ['ouverte', 'fermee', 'a_venir'], nullable: true),
                new OA\Property(property: 'order', type: 'integer', minimum: 0, nullable: true),
            ])
        )),
        responses: [
            new OA\Response(response: 200, description: 'Modifiée', content: new OA\JsonContent(ref: '#/components/schemas/Scholarship')),
            new OA\Response(response: 403, description: "Permission `cms.manage` requise"),
        ]
    )]
    public function update(Request $request, Scholarship $scholarship)
    {
        abort_unless($request->user()->can('cms.manage'), 403);

        foreach (['provider', 'description', 'amount', 'audience', 'deadline', 'application_url'] as $field) {
            if ($request->input($field) === '') {
                $request->merge([$field => null]);
            }
        }

        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'provider' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'amount' => ['nullable', 'string', 'max:255'],
            'audience' => ['nullable', 'string', 'max:255'],
            'deadline' => ['nullable', 'date'],
            'application_url' => ['nullable', 'url'],
            'image' => ['nullable', 'image', 'max:4096'],
            'remove_image' => ['nullable', 'boolean'],
            'status' => [Rule::in(['ouverte', 'fermee', 'a_venir'])],
            'order' => ['nullable', 'integer', 'min:0'],
        ]);

        unset($data['image'], $data['remove_image']);

        if ($request->hasFile('image')) {
            if ($scholarship->image_path) {
                Storage::disk('public')->delete($scholarship->image_path);
            }
            $data['image_path'] = $request->file('image')->store('scholarships', 'public');
        } elseif ($request->boolean('remove_image')) {
            if ($scholarship->image_path) {
                Storage::disk('public')->delete($scholarship->image_path);
            }
            $data['image_path'] = null;
        }

        $scholarship->update($data);

        return $scholarship;
    }

    #[OA\Delete(
        path: '/scholarships/{scholarship}',
        summary: 'Supprimer une bourse',
        security: [['bearerAuth' => []]],
        tags: ['Bourses'],
        parameters: [new OA\PathParameter(name: 'scholarship', schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 204, description: 'Supprimée'),
            new OA\Response(response: 403, description: "Permission `cms.manage` requise"),
        ]
    )]
    public function destroy(Request $request, Scholarship $scholarship)
    {
        abort_unless($request->user()->can('cms.manage'), 403);

        if ($scholarship->image_path) {
            Storage::disk('public')->delete($scholarship->image_path);
        }

        $scholarship->delete();

        return response()->noContent();
    }
}
