<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseProgress;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

class CourseController extends Controller
{
    #[OA\Get(
        path: '/courses',
        summary: 'Lister les cours de renforcement',
        description: 'Public — utilisé par le parcours niveau → matière du site vitrine. Les cours `brouillon` ne sont visibles qu’avec `programs.manage`. Si authentifiée, chaque cours inclut `my_progress`.',
        tags: ['Cours de renforcement'],
        parameters: [
            new OA\QueryParameter(name: 'level_id', schema: new OA\Schema(type: 'integer')),
            new OA\QueryParameter(name: 'subject_id', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Cours', content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Course')))]
    )]
    public function index(Request $request)
    {
        $courses = Course::query()
            ->when($request->query('level_id'), fn ($q, $id) => $q->where('level_id', $id))
            ->when($request->query('subject_id'), fn ($q, $id) => $q->where('subject_id', $id))
            ->when(! $request->user()?->can('programs.manage'), fn ($q) => $q->where('status', 'publie'))
            ->orderBy('order')
            ->get();

        if ($request->user()) {
            $progress = CourseProgress::where('user_id', $request->user()->id)
                ->whereIn('course_id', $courses->pluck('id'))
                ->get()
                ->keyBy('course_id');

            $courses->each(function (Course $course) use ($progress) {
                $course->setAttribute('my_progress', $progress->get($course->id)?->progress ?? 0);
            });
        }

        return $courses;
    }

    #[OA\Get(
        path: '/courses/{course}',
        summary: 'Consulter un cours de renforcement (avec labo virtuel et sessions live)',
        description: 'Public.',
        tags: ['Cours de renforcement'],
        parameters: [new OA\PathParameter(name: 'course', schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Cours', content: new OA\JsonContent(ref: '#/components/schemas/Course'))]
    )]
    public function show(Course $course)
    {
        return $course->load(['level', 'subject', 'experiments', 'liveSessions']);
    }

    #[OA\Post(
        path: '/courses',
        summary: 'Créer un cours de renforcement',
        security: [['bearerAuth' => []]],
        tags: ['Cours de renforcement'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['level_id', 'subject_id', 'title'],
                properties: [
                    new OA\Property(property: 'level_id', type: 'integer'),
                    new OA\Property(property: 'subject_id', type: 'integer'),
                    new OA\Property(property: 'title', type: 'string'),
                    new OA\Property(property: 'description', type: 'string', nullable: true),
                    new OA\Property(property: 'order', type: 'integer', minimum: 0, nullable: true),
                    new OA\Property(property: 'status', type: 'string', enum: ['brouillon', 'publie'], default: 'brouillon'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Créé', content: new OA\JsonContent(ref: '#/components/schemas/Course')),
            new OA\Response(response: 403, description: "Permission `programs.manage` requise"),
        ]
    )]
    public function store(Request $request)
    {
        $this->authorize('create', Course::class);

        $data = $request->validate([
            'level_id' => ['required', 'exists:levels,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'order' => ['nullable', 'integer', 'min:0'],
            'status' => [Rule::in(['brouillon', 'publie'])],
        ]);

        $data['status'] ??= 'brouillon';

        return response()->json(Course::create($data), 201);
    }

    #[OA\Patch(
        path: '/courses/{course}',
        summary: 'Modifier un cours de renforcement',
        security: [['bearerAuth' => []]],
        tags: ['Cours de renforcement'],
        parameters: [new OA\PathParameter(name: 'course', schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(properties: [
            new OA\Property(property: 'level_id', type: 'integer'),
            new OA\Property(property: 'subject_id', type: 'integer'),
            new OA\Property(property: 'title', type: 'string'),
            new OA\Property(property: 'description', type: 'string', nullable: true),
            new OA\Property(property: 'order', type: 'integer', minimum: 0, nullable: true),
            new OA\Property(property: 'status', type: 'string', enum: ['brouillon', 'publie']),
        ])),
        responses: [
            new OA\Response(response: 200, description: 'Modifié', content: new OA\JsonContent(ref: '#/components/schemas/Course')),
            new OA\Response(response: 403, description: "Permission `programs.manage` requise"),
        ]
    )]
    public function update(Request $request, Course $course)
    {
        $this->authorize('update', $course);

        $data = $request->validate([
            'level_id' => ['sometimes', 'exists:levels,id'],
            'subject_id' => ['sometimes', 'exists:subjects,id'],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'order' => ['nullable', 'integer', 'min:0'],
            'status' => [Rule::in(['brouillon', 'publie'])],
        ]);

        $course->update($data);

        return $course;
    }

    #[OA\Delete(
        path: '/courses/{course}',
        summary: 'Supprimer un cours de renforcement',
        security: [['bearerAuth' => []]],
        tags: ['Cours de renforcement'],
        parameters: [new OA\PathParameter(name: 'course', schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 204, description: 'Supprimé'),
            new OA\Response(response: 403, description: "Permission `programs.manage` requise"),
        ]
    )]
    public function destroy(Course $course)
    {
        $this->authorize('delete', $course);

        $course->delete();

        return response()->noContent();
    }

    #[OA\Post(
        path: '/courses/{course}/progress',
        summary: 'Mettre à jour ma progression sur un cours de renforcement',
        description: '201 la première fois (création), 200 ensuite (mise à jour).',
        security: [['bearerAuth' => []]],
        tags: ['Cours de renforcement'],
        parameters: [new OA\PathParameter(name: 'course', schema: new OA\Schema(type: 'integer'))],
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
    public function updateProgress(Request $request, Course $course)
    {
        $data = $request->validate([
            'progress' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        $progress = CourseProgress::updateOrCreate(
            ['user_id' => $request->user()->id, 'course_id' => $course->id],
            [
                'progress' => $data['progress'],
                'completed_at' => $data['progress'] >= 100 ? now() : null,
            ]
        );

        return $progress;
    }
}
