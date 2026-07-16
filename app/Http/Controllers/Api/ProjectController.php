<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return Project::query()
            ->with(['mentee', 'pairing'])
            ->when(! $user->can('pairings.manage'), function ($q) use ($user) {
                $q->where('mentee_id', $user->id)
                    ->orWhereHas('pairing', fn ($q) => $q->where('mentor_id', $user->id));
            })
            ->orderByDesc('updated_at')
            ->get();
    }

    public function show(Project $project)
    {
        $this->authorize('view', $project);

        return $project->load(['mentee', 'pairing']);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Project::class);

        $data = $request->validate([
            'pairing_id' => ['nullable', 'exists:mentorship_pairings,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'file_path' => ['nullable', 'string'],
        ]);

        $data['mentee_id'] = $request->user()->id;
        $data['status'] = 'brouillon';

        return response()->json(Project::create($data), 201);
    }

    public function update(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'file_path' => ['nullable', 'string'],
            'status' => [Rule::in(['brouillon', 'soumis', 'en_validation', 'valide', 'rejete'])],
        ]);

        if (! $request->user()->can('pairings.manage')) {
            // Mentees may only submit their own draft project; validation is staff-only.
            unset($data['status']);
            if ($request->boolean('submit') && $project->status === 'brouillon') {
                $data['status'] = 'soumis';
            }
        }

        $project->update($data);

        return $project;
    }

    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);

        $project->delete();

        return response()->noContent();
    }
}
