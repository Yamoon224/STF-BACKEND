<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CmsPage;
use App\Models\CmsPageImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;

class CmsPageImageController extends Controller
{
    #[OA\Post(
        path: '/cms/pages/{page}/images',
        summary: "Ajouter une ou plusieurs images à la galerie d'une activité/actualité",
        description: "Vient s'ajouter à l'image de couverture (`image`) de la page/article. Jusqu'à 10 images par envoi, 8 Mo chacune.",
        security: [['bearerAuth' => []]],
        tags: ['CMS'],
        parameters: [new OA\PathParameter(name: 'page', schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    type: 'object',
                    required: ['images'],
                    properties: [
                        new OA\Property(
                            property: 'images',
                            type: 'array',
                            items: new OA\Items(type: 'string', format: 'binary')
                        ),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Images ajoutées', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/CmsPageImage'))),
            new OA\Response(response: 403, description: "Permission `cms.manage` requise"),
        ]
    )]
    public function store(Request $request, CmsPage $page)
    {
        abort_unless($request->user()->can('cms.manage'), 403);

        $request->validate([
            'images' => ['required', 'array', 'max:10'],
            'images.*' => ['image', 'max:8192'],
        ]);

        $nextOrder = ((int) $page->images()->max('order')) + 1;

        $created = collect($request->file('images'))->map(function ($file, $i) use ($page, $nextOrder) {
            return $page->images()->create([
                'image_path' => $file->store('cms-pages', 'public'),
                'order' => $nextOrder + $i,
            ]);
        });

        return response()->json($created->values(), 201);
    }

    #[OA\Delete(
        path: '/cms/page-images/{image}',
        summary: "Retirer une image de la galerie",
        security: [['bearerAuth' => []]],
        tags: ['CMS'],
        parameters: [new OA\PathParameter(name: 'image', schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 204, description: 'Supprimée'),
            new OA\Response(response: 403, description: "Permission `cms.manage` requise"),
        ]
    )]
    public function destroy(Request $request, CmsPageImage $image)
    {
        abort_unless($request->user()->can('cms.manage'), 403);

        Storage::disk('public')->delete($image->image_path);
        $image->delete();

        return response()->noContent();
    }
}
