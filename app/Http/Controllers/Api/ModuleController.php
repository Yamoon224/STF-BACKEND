<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\ModuleProgress;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ModuleController extends Controller
{
    public function index(Request $request)
    {
        $modules = Module::query()
            ->when($request->query('program_id'), fn ($q, $id) => $q->where('program_id', $id))
            ->when(! $request->user()?->can('programs.manage'), fn ($q) => $q->where('status', 'publie'))
            ->orderBy('order')
            ->get();

        if ($request->user()) {
            $progress = ModuleProgress::where('user_id', $request->user()->id)
                ->whereIn('module_id', $modules->pluck('id'))
                ->get()
                ->keyBy('module_id');

            $modules->each(function (Module $module) use ($progress) {
                $module->setAttribute('my_progress', $progress->get($module->id)?->progress ?? 0);
            });
        }

        return $modules;
    }

    public function show(Module $module)
    {
        return $module->load('quizzes');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Module::class);

        $data = $request->validate([
            'program_id' => ['nullable', 'exists:programs,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'order' => ['nullable', 'integer', 'min:0'],
            'status' => [Rule::in(['brouillon', 'publie'])],
        ]);

        $data['status'] ??= 'brouillon';

        return response()->json(Module::create($data), 201);
    }

    public function update(Request $request, Module $module)
    {
        $this->authorize('update', $module);

        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'order' => ['nullable', 'integer', 'min:0'],
            'status' => [Rule::in(['brouillon', 'publie'])],
        ]);

        $module->update($data);

        return $module;
    }

    public function destroy(Module $module)
    {
        $this->authorize('delete', $module);

        $module->delete();

        return response()->noContent();
    }

    public function updateProgress(Request $request, Module $module)
    {
        $data = $request->validate([
            'progress' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $progress = ModuleProgress::updateOrCreate(
            ['user_id' => $request->user()->id, 'module_id' => $module->id],
            [
                'progress' => $data['progress'],
                'completed_at' => $data['progress'] >= 100 ? now() : null,
            ]
        );

        return $progress;
    }
}
