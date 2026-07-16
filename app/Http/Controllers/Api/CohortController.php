<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cohort;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CohortController extends Controller
{
    public function index(Request $request)
    {
        return Cohort::query()
            ->when($request->query('program_id'), fn ($q, $id) => $q->where('program_id', $id))
            ->with('program')
            ->orderByDesc('start_date')
            ->get();
    }

    public function show(Cohort $cohort)
    {
        return $cohort->load(['program', 'users']);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Cohort::class);

        $data = $request->validate([
            'program_id' => ['required', 'exists:programs,id'],
            'name' => ['required', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => [Rule::in(['a_venir', 'en_cours', 'termine'])],
        ]);

        $data['status'] ??= 'a_venir';

        return response()->json(Cohort::create($data), 201);
    }

    public function update(Request $request, Cohort $cohort)
    {
        $this->authorize('update', $cohort);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => [Rule::in(['a_venir', 'en_cours', 'termine'])],
        ]);

        $cohort->update($data);

        return $cohort;
    }

    public function destroy(Cohort $cohort)
    {
        $this->authorize('delete', $cohort);

        $cohort->delete();

        return response()->noContent();
    }
}
