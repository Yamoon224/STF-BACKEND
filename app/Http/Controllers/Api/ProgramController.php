<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProgramController extends Controller
{
    public function index(Request $request)
    {
        return Program::query()
            ->withCount('cohorts')
            ->withCount(['pairings as mentees_count' => fn ($q) => $q->whereIn('status', ['actif', 'en_attente'])])
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->orderBy('name')
            ->get();
    }

    public function show(Program $program)
    {
        return $program->load(['cohorts', 'modules']);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Program::class);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'audience' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'color' => ['nullable', 'string', 'max:50'],
            'status' => [Rule::in(['a_venir', 'en_cours', 'archive'])],
            'cycle_start' => ['nullable', 'date'],
            'cycle_end' => ['nullable', 'date', 'after_or_equal:cycle_start'],
        ]);

        $data['slug'] = Str::slug($data['name']);
        $data['status'] ??= 'a_venir';

        return response()->json(Program::create($data), 201);
    }

    public function update(Request $request, Program $program)
    {
        $this->authorize('update', $program);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'audience' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'color' => ['nullable', 'string', 'max:50'],
            'status' => [Rule::in(['a_venir', 'en_cours', 'archive'])],
            'cycle_start' => ['nullable', 'date'],
            'cycle_end' => ['nullable', 'date', 'after_or_equal:cycle_start'],
        ]);

        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $program->update($data);

        return $program;
    }

    public function destroy(Program $program)
    {
        $this->authorize('delete', $program);

        $program->delete();

        return response()->noContent();
    }
}
