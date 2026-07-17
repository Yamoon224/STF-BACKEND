<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\GroupFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;

class GroupFileController extends Controller
{
    #[OA\Get(
        path: '/groups/{group}/files',
        summary: "Lister les fichiers d'un groupe",
        security: [['bearerAuth' => []]],
        tags: ['Groupes'],
        parameters: [new OA\PathParameter(name: 'group', schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Fichiers', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/GroupFile'))),
            new OA\Response(response: 403, description: 'Non membre du groupe'),
        ]
    )]
    public function index(Group $group)
    {
        $this->authorize('view', $group);

        return $group->files()->with('uploader')->orderByDesc('created_at')->get();
    }

    #[OA\Post(
        path: '/groups/{group}/files',
        summary: 'Déposer un fichier dans un groupe',
        description: 'Taille maximale : 20 Mo.',
        security: [['bearerAuth' => []]],
        tags: ['Groupes'],
        parameters: [new OA\PathParameter(name: 'group', schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    type: 'object',
                    required: ['file'],
                    properties: [new OA\Property(property: 'file', type: 'string', format: 'binary')]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Déposé', content: new OA\JsonContent(ref: '#/components/schemas/GroupFile')),
            new OA\Response(response: 403, description: 'Non membre du groupe'),
        ]
    )]
    public function store(Request $request, Group $group)
    {
        $this->authorize('post', $group);

        $data = $request->validate([
            'file' => ['required', 'file', 'max:20480'],
        ]);

        $path = $request->file('file')->store("groups/{$group->id}", 'local');

        $file = $group->files()->create([
            'uploader_id' => $request->user()->id,
            'name' => $request->file('file')->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $request->file('file')->getMimeType(),
            'size_bytes' => $request->file('file')->getSize(),
        ]);

        return response()->json($file->load('uploader'), 201);
    }

    #[OA\Delete(
        path: '/files/{file}',
        summary: 'Supprimer un fichier',
        description: "Réservé à l'autrice du dépôt ou à `groups.manage`.",
        security: [['bearerAuth' => []]],
        tags: ['Groupes'],
        parameters: [new OA\PathParameter(name: 'file', schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 204, description: 'Supprimé'),
            new OA\Response(response: 403, description: "Réservé à l'autrice du dépôt"),
        ]
    )]
    public function destroy(Request $request, GroupFile $file)
    {
        abort_unless(
            $request->user()->id === $file->uploader_id || $request->user()->can('groups.manage'),
            403
        );

        Storage::disk('local')->delete($file->path);
        $file->delete();

        return response()->noContent();
    }
}
