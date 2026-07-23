<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CmsPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

class CmsPageController extends Controller
{
    #[OA\Get(
        path: '/cms/pages',
        summary: 'Lister les pages/articles',
        description: 'Public — ne renvoie que les contenus publiés, sauf avec `cms.manage`.',
        tags: ['CMS'],
        parameters: [
            new OA\QueryParameter(name: 'type', schema: new OA\Schema(type: 'string', enum: ['page', 'article'])),
            new OA\QueryParameter(name: 'category', schema: new OA\Schema(type: 'string')),
        ],
        responses: [new OA\Response(response: 200, description: 'Contenus', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/CmsPage')))]
    )]
    public function index(Request $request)
    {
        $canManage = $request->user()?->can('cms.manage');

        return CmsPage::query()
            ->when($canManage, fn ($q) => $q->with('images'))
            ->when(! $canManage, fn ($q) => $q->where('status', 'publie'))
            ->when($request->query('type'), fn ($q, $type) => $q->where('type', $type))
            ->when($request->query('category'), fn ($q, $category) => $q->where('category', $category))
            ->orderByDesc('published_at')
            ->get();
    }

    #[OA\Get(
        path: '/cms/pages/{slug}',
        summary: 'Consulter une page/article par slug',
        description: 'Public pour les contenus publiés ; 404 sur un brouillon sans `cms.manage`.',
        tags: ['CMS'],
        parameters: [new OA\PathParameter(name: 'slug', schema: new OA\Schema(type: 'string'))],
        responses: [
            new OA\Response(response: 200, description: 'Contenu', content: new OA\JsonContent(ref: '#/components/schemas/CmsPage')),
            new OA\Response(response: 404, description: 'Introuvable ou non publié'),
        ]
    )]
    public function show(Request $request, string $slug)
    {
        $page = CmsPage::with('images')->where('slug', $slug)->firstOrFail();

        abort_if($page->status !== 'publie' && ! $request->user()?->can('cms.manage'), 404);

        return $page;
    }

    #[OA\Post(
        path: '/cms/pages',
        summary: 'Créer une page/article',
        description: "L'image est optionnelle. Multipart requis pour joindre un fichier.",
        security: [['bearerAuth' => []]],
        tags: ['CMS'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(type: 'object', required: ['title'], properties: [
                    new OA\Property(property: 'title', type: 'string'),
                    new OA\Property(property: 'type', type: 'string', enum: ['page', 'article'], default: 'page'),
                    new OA\Property(property: 'body', type: 'string', nullable: true),
                    new OA\Property(property: 'excerpt', type: 'string', nullable: true),
                    new OA\Property(property: 'category', type: 'string', nullable: true),
                    new OA\Property(property: 'image', type: 'string', format: 'binary', nullable: true, description: "Image d'illustration (activité, événement, actualité)."),
                    new OA\Property(property: 'status', type: 'string', enum: ['brouillon', 'publie'], default: 'brouillon'),
                ])
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Créé', content: new OA\JsonContent(ref: '#/components/schemas/CmsPage')),
            new OA\Response(response: 403, description: "Permission `cms.manage` requise"),
        ]
    )]
    public function store(Request $request)
    {
        abort_unless($request->user()->can('cms.manage'), 403);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => [Rule::in(['page', 'article'])],
            'body' => ['nullable', 'string'],
            'excerpt' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:8192'],
            'status' => [Rule::in(['brouillon', 'publie'])],
        ]);

        unset($data['image']);
        $data['slug'] = Str::slug($data['title']);
        $data['author_id'] = $request->user()->id;
        $data['type'] ??= 'page';
        $data['status'] ??= 'brouillon';
        if ($data['status'] === 'publie') {
            $data['published_at'] = now();
        }

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('cms-pages', 'public');
        }

        return response()->json(CmsPage::create($data), 201);
    }

    #[OA\Patch(
        path: '/cms/pages/{page}',
        summary: 'Modifier une page/article',
        description: "L'image est optionnelle. Envoyer `remove_image=1` pour retirer l'image existante sans en fournir une nouvelle. Multipart requiert `_method=PATCH` avec une requête POST.",
        security: [['bearerAuth' => []]],
        tags: ['CMS'],
        parameters: [new OA\PathParameter(name: 'page', schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(type: 'object', properties: [
                new OA\Property(property: 'title', type: 'string'),
                new OA\Property(property: 'body', type: 'string', nullable: true),
                new OA\Property(property: 'excerpt', type: 'string', nullable: true),
                new OA\Property(property: 'category', type: 'string', nullable: true),
                new OA\Property(property: 'image', type: 'string', format: 'binary', nullable: true),
                new OA\Property(property: 'remove_image', type: 'boolean', nullable: true),
                new OA\Property(property: 'status', type: 'string', enum: ['brouillon', 'publie']),
            ])
        )),
        responses: [
            new OA\Response(response: 200, description: 'Modifié', content: new OA\JsonContent(ref: '#/components/schemas/CmsPage')),
            new OA\Response(response: 403, description: "Permission `cms.manage` requise"),
        ]
    )]
    public function update(Request $request, CmsPage $page)
    {
        abort_unless($request->user()->can('cms.manage'), 403);

        foreach (['body', 'excerpt', 'category'] as $field) {
            if ($request->input($field) === '') {
                $request->merge([$field => null]);
            }
        }

        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'excerpt' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:8192'],
            'remove_image' => ['nullable', 'boolean'],
            'status' => [Rule::in(['brouillon', 'publie'])],
        ]);

        unset($data['image'], $data['remove_image']);

        if (isset($data['title'])) {
            $data['slug'] = Str::slug($data['title']);
        }
        if (($data['status'] ?? null) === 'publie' && ! $page->published_at) {
            $data['published_at'] = now();
        }

        if ($request->hasFile('image')) {
            if ($page->image_path) {
                Storage::disk('public')->delete($page->image_path);
            }
            $data['image_path'] = $request->file('image')->store('cms-pages', 'public');
        } elseif ($request->boolean('remove_image')) {
            if ($page->image_path) {
                Storage::disk('public')->delete($page->image_path);
            }
            $data['image_path'] = null;
        }

        $page->update($data);

        return $page;
    }

    #[OA\Delete(
        path: '/cms/pages/{page}',
        summary: 'Supprimer une page/article',
        security: [['bearerAuth' => []]],
        tags: ['CMS'],
        parameters: [new OA\PathParameter(name: 'page', schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 204, description: 'Supprimé'),
            new OA\Response(response: 403, description: "Permission `cms.manage` requise"),
        ]
    )]
    public function destroy(Request $request, CmsPage $page)
    {
        abort_unless($request->user()->can('cms.manage'), 403);

        if ($page->image_path) {
            Storage::disk('public')->delete($page->image_path);
        }

        $page->delete();

        return response()->noContent();
    }
}
