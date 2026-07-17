<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AuditLogController extends Controller
{
    #[OA\Get(
        path: '/audit-logs',
        summary: "Lister les journaux d'audit",
        security: [['bearerAuth' => []]],
        tags: ["Journaux d'audit"],
        parameters: [new OA\QueryParameter(name: 'actor_id', schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Page paginée (30/page)', content: new OA\JsonContent(properties: [
                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/AuditLog')),
            ])),
            new OA\Response(response: 403, description: "Permission `audit-logs.view` requise"),
        ]
    )]
    public function index(Request $request)
    {
        return AuditLog::query()
            ->with('actor')
            ->when($request->query('actor_id'), fn ($q, $id) => $q->where('actor_id', $id))
            ->orderByDesc('created_at')
            ->paginate(30);
    }
}
