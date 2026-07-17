<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\ModuleProgress;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

class ModuleController extends Controller
{
    #[OA\Get(
        path: '/modules',
        summary: 'Lister les modules',
        description: 'Les modules `brouillon` ne sont visibles qu’avec `programs.manage`. Si authentifiée, chaque module inclut `my_progress`.',
        tags: ['Modules'],
        parameters: [new OA\QueryParameter(name: 'program_id', schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Modules', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Module')))]
    )]
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

    #[OA\Get(
        path: '/modules/{module}',
        summary: 'Consulter un module (avec ses quiz)',
        tags: ['Modules'],
        parameters: [new OA\PathParameter(name: 'module', schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Module', content: new OA\JsonContent(ref: '#/components/schemas/Module'))]
    )]
    public function show(Module $module)
    {
        return $module->load('quizzes');
    }

    #[OA\Post(
        path: '/modules',
        summary: 'Créer un module',
        security: [['bearerAuth' => []]],
        tags: ['Modules'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['title'],
                properties: [
                    new OA\Property(property: 'program_id', type: 'integer', nullable: true),
                    new OA\Property(property: 'title', type: 'string'),
                    new OA\Property(property: 'description', type: 'string', nullable: true),
                    new OA\Property(property: 'order', type: 'integer', minimum: 0, nullable: true),
                    new OA\Property(property: 'status', type: 'string', enum: ['brouillon', 'publie'], default: 'brouillon'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Créé', content: new OA\JsonContent(ref: '#/components/schemas/Module')),
            new OA\Response(response: 403, description: "Permission `programs.manage` requise"),
        ]
    )]
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

    #[OA\Patch(
        path: '/modules/{module}',
        summary: 'Modifier un module',
        security: [['bearerAuth' => []]],
        tags: ['Modules'],
        parameters: [new OA\PathParameter(name: 'module', schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(properties: [
            new OA\Property(property: 'title', type: 'string'),
            new OA\Property(property: 'description', type: 'string', nullable: true),
            new OA\Property(property: 'order', type: 'integer', minimum: 0, nullable: true),
            new OA\Property(property: 'status', type: 'string', enum: ['brouillon', 'publie']),
        ])),
        responses: [
            new OA\Response(response: 200, description: 'Modifié', content: new OA\JsonContent(ref: '#/components/schemas/Module')),
            new OA\Response(response: 403, description: "Permission `programs.manage` requise"),
        ]
    )]
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

    #[OA\Delete(
        path: '/modules/{module}',
        summary: 'Supprimer un module',
        security: [['bearerAuth' => []]],
        tags: ['Modules'],
        parameters: [new OA\PathParameter(name: 'module', schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 204, description: 'Supprimé'),
            new OA\Response(response: 403, description: "Permission `programs.manage` requise"),
        ]
    )]
    public function destroy(Module $module)
    {
        $this->authorize('delete', $module);

        $module->delete();

        return response()->noContent();
    }

    #[OA\Post(
        path: '/modules/{module}/progress',
        summary: 'Mettre à jour ma progression sur un module',
        description: '201 la première fois (création), 200 ensuite (mise à jour) — comportement standard de Laravel pour un modèle nouvellement créé.',
        security: [['bearerAuth' => []]],
        tags: ['Modules'],
        parameters: [new OA\PathParameter(name: 'module', schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(required: ['progress'], properties: [
                new OA\Property(property: 'progress', type: 'integer', minimum: 0, maximum: 100),
            ])
        ),
        responses: [
            new OA\Response(response: 200, description: 'Progression mise à jour'),
            new OA\Response(response: 201, description: 'Progression créée'),
        ]
    )]
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
