<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
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
