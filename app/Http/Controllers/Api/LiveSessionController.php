<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LiveSession;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

class LiveSessionController extends Controller
{
    #[OA\Get(
        path: '/live-sessions',
        summary: 'Lister les sessions live des cours de renforcement',
        description: 'Public, triées par date.',
        tags: ['Cours de renforcement'],
        parameters: [new OA\QueryParameter(name: 'course_id', schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Sessions live', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/LiveSession')))]
    )]
    public function index(Request $request)
    {
        return LiveSession::query()
            ->when($request->query('course_id'), fn ($q, $id) => $q->where('course_id', $id))
            ->orderBy('scheduled_at')
            ->get();
    }

    #[OA\Post(
        path: '/live-sessions',
        summary: 'Programmer une session live',
        security: [['bearerAuth' => []]],
        tags: ['Cours de renforcement'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['course_id', 'title', 'scheduled_at'],
                properties: [
                    new OA\Property(property: 'course_id', type: 'integer'),
                    new OA\Property(property: 'title', type: 'string'),
                    new OA\Property(property: 'scheduled_at', type: 'string', format: 'date-time'),
                    new OA\Property(property: 'duration_minutes', type: 'integer', minimum: 1, nullable: true),
                    new OA\Property(property: 'meeting_link', type: 'string', nullable: true),
                    new OA\Property(property: 'status', type: 'string', enum: ['a_venir', 'en_cours', 'termine'], default: 'a_venir'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Créée', content: new OA\JsonContent(ref: '#/components/schemas/LiveSession')),
            new OA\Response(response: 403, description: "Permission `programs.manage` requise"),
        ]
    )]
    public function store(Request $request)
    {
        $this->authorize('create', LiveSession::class);

        $data = $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'title' => ['required', 'string', 'max:255'],
            'scheduled_at' => ['required', 'date'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
            'meeting_link' => ['nullable', 'string', 'max:255'],
            'status' => [Rule::in(['a_venir', 'en_cours', 'termine'])],
        ]);

        $data['status'] ??= 'a_venir';
        $data['created_by'] = $request->user()->id;

        return response()->json(LiveSession::create($data), 201);
    }

    #[OA\Patch(
        path: '/live-sessions/{liveSession}',
        summary: 'Modifier une session live',
        security: [['bearerAuth' => []]],
        tags: ['Cours de renforcement'],
        parameters: [new OA\PathParameter(name: 'liveSession', schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(properties: [
            new OA\Property(property: 'title', type: 'string'),
            new OA\Property(property: 'scheduled_at', type: 'string', format: 'date-time'),
            new OA\Property(property: 'duration_minutes', type: 'integer', minimum: 1, nullable: true),
            new OA\Property(property: 'meeting_link', type: 'string', nullable: true),
            new OA\Property(property: 'status', type: 'string', enum: ['a_venir', 'en_cours', 'termine']),
        ])),
        responses: [
            new OA\Response(response: 200, description: 'Modifiée', content: new OA\JsonContent(ref: '#/components/schemas/LiveSession')),
            new OA\Response(response: 403, description: "Permission `programs.manage` requise"),
        ]
    )]
    public function update(Request $request, LiveSession $liveSession)
    {
        $this->authorize('update', $liveSession);

        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'scheduled_at' => ['sometimes', 'date'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
            'meeting_link' => ['nullable', 'string', 'max:255'],
            'status' => [Rule::in(['a_venir', 'en_cours', 'termine'])],
        ]);

        $liveSession->update($data);

        return $liveSession;
    }

    #[OA\Delete(
        path: '/live-sessions/{liveSession}',
        summary: 'Supprimer une session live',
        security: [['bearerAuth' => []]],
        tags: ['Cours de renforcement'],
        parameters: [new OA\PathParameter(name: 'liveSession', schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 204, description: 'Supprimée'),
            new OA\Response(response: 403, description: "Permission `programs.manage` requise"),
        ]
    )]
    public function destroy(LiveSession $liveSession)
    {
        $this->authorize('delete', $liveSession);

        $liveSession->delete();

        return response()->noContent();
    }
}
