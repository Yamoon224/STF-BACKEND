<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

class ReportController extends Controller
{
    #[OA\Get(
        path: '/reports',
        summary: 'Lister les signalements',
        description: 'Sans `reports.view`, ne renvoie que les signalements créés par l’utilisatrice connectée.',
        security: [['bearerAuth' => []]],
        tags: ['Signalements'],
        parameters: [new OA\QueryParameter(name: 'status', schema: new OA\Schema(type: 'string', enum: ['nouveau', 'en_cours', 'resolu']))],
        responses: [new OA\Response(response: 200, description: 'Signalements', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Report')))]
    )]
    public function index(Request $request)
    {
        $this->authorize('viewAny', Report::class);

        $user = $request->user();

        return Report::query()
            ->with(['reporter', 'resolver'])
            ->when(! $user->can('reports.view'), fn ($q) => $q->where('reporter_id', $user->id))
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->get();
    }

    #[OA\Post(
        path: '/reports',
        summary: 'Créer un signalement',
        description: 'Ouverte à toute utilisatrice connectée (bouton "Signaler" de la messagerie, modération de groupe, etc.).',
        security: [['bearerAuth' => []]],
        tags: ['Signalements'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(required: ['context_type', 'description'], properties: [
                new OA\Property(property: 'context_type', type: 'string', example: 'messagerie_pairing'),
                new OA\Property(property: 'context_id', type: 'integer', nullable: true),
                new OA\Property(property: 'description', type: 'string'),
            ])
        ),
        responses: [new OA\Response(response: 201, description: 'Créé', content: new OA\JsonContent(ref: '#/components/schemas/Report'))]
    )]
    public function store(Request $request)
    {
        $this->authorize('create', Report::class);

        $data = $request->validate([
            'context_type' => ['required', 'string', 'max:255'],
            'context_id' => ['nullable', 'integer'],
            'description' => ['required', 'string'],
        ]);

        $data['reporter_id'] = $request->user()->id;
        $data['status'] = 'nouveau';

        $report = Report::create($data);

        AuditLog::record($request->user(), 'signalement.cree', $report);

        return response()->json($report, 201);
    }

    #[OA\Patch(
        path: '/reports/{report}',
        summary: 'Changer le statut d’un signalement',
        description: "Réservé à `reports.manage`. Passer à `resolu` horodate et attribue la résolution à l'utilisatrice courante.",
        security: [['bearerAuth' => []]],
        tags: ['Signalements'],
        parameters: [new OA\PathParameter(name: 'report', schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(required: ['status'], properties: [
                new OA\Property(property: 'status', type: 'string', enum: ['nouveau', 'en_cours', 'resolu']),
            ])
        ),
        responses: [
            new OA\Response(response: 200, description: 'Modifié', content: new OA\JsonContent(ref: '#/components/schemas/Report')),
            new OA\Response(response: 403, description: "Permission `reports.manage` requise"),
        ]
    )]
    public function update(Request $request, Report $report)
    {
        $this->authorize('update', $report);

        $data = $request->validate([
            'status' => ['required', Rule::in(['nouveau', 'en_cours', 'resolu'])],
        ]);

        if ($data['status'] === 'resolu') {
            $data['resolved_by'] = $request->user()->id;
            $data['resolved_at'] = now();
        }

        $report->update($data);

        AuditLog::record($request->user(), 'signalement.modifie', $report, $data);

        return $report;
    }
}
