<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class QuizController extends Controller
{
    #[OA\Get(
        path: '/quizzes/{quiz}',
        summary: 'Consulter un quiz (questions + options)',
        security: [['bearerAuth' => []]],
        tags: ['Modules'],
        parameters: [new OA\PathParameter(name: 'quiz', schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Quiz', content: new OA\JsonContent(ref: '#/components/schemas/Quiz'))]
    )]
    public function show(Quiz $quiz)
    {
        return $quiz->load('questions.options');
    }

    #[OA\Post(
        path: '/modules/{module}/quizzes',
        summary: 'Créer un quiz pour un module',
        security: [['bearerAuth' => []]],
        tags: ['Modules'],
        parameters: [new OA\PathParameter(name: 'module', schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['title', 'questions'],
                properties: [
                    new OA\Property(property: 'title', type: 'string'),
                    new OA\Property(property: 'passing_score', type: 'integer', minimum: 0, maximum: 100, default: 70),
                    new OA\Property(property: 'questions', type: 'array', minItems: 1, items: new OA\Items(ref: '#/components/schemas/QuizQuestionInput')),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Créé', content: new OA\JsonContent(ref: '#/components/schemas/Quiz')),
            new OA\Response(response: 403, description: "Permission `programs.manage` requise"),
            new OA\Response(response: 422, description: 'Validation échouée', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function store(Request $request, Module $module)
    {
        $this->authorize('create', Module::class);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'passing_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.question' => ['required', 'string'],
            'questions.*.type' => ['required', 'in:unique,multiple'],
            'questions.*.options' => ['required', 'array', 'min:2'],
            'questions.*.options.*.label' => ['required', 'string'],
            'questions.*.options.*.is_correct' => ['boolean'],
        ]);

        $quiz = $module->quizzes()->create([
            'title' => $data['title'],
            'passing_score' => $data['passing_score'] ?? 70,
        ]);

        foreach ($data['questions'] as $order => $question) {
            $questionModel = $quiz->questions()->create([
                'question' => $question['question'],
                'type' => $question['type'],
                'order' => $order,
            ]);

            foreach ($question['options'] as $option) {
                $questionModel->options()->create($option);
            }
        }

        return response()->json($quiz->load('questions.options'), 201);
    }

    #[OA\Post(
        path: '/quizzes/{quiz}/attempts',
        summary: 'Soumettre une tentative de quiz',
        description: 'Le score est calculé côté serveur en comparant les options sélectionnées aux bonnes réponses.',
        security: [['bearerAuth' => []]],
        tags: ['Modules'],
        parameters: [new OA\PathParameter(name: 'quiz', schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['answers'],
                properties: [
                    new OA\Property(
                        property: 'answers',
                        type: 'object',
                        description: 'Clé = id de la question, valeur = liste des ids d’options sélectionnées',
                        example: ['1' => [3]],
                        additionalProperties: new OA\AdditionalProperties(type: 'array', items: new OA\Items(type: 'integer'))
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Tentative enregistrée', content: new OA\JsonContent(ref: '#/components/schemas/QuizAttempt')),
            new OA\Response(response: 422, description: 'Validation échouée', content: new OA\JsonContent(ref: '#/components/schemas/ValidationError')),
        ]
    )]
    public function attempt(Request $request, Quiz $quiz)
    {
        $data = $request->validate([
            'answers' => ['required', 'array'],
            'answers.*' => ['array'],
            'answers.*.*' => ['integer', 'exists:quiz_options,id'],
        ]);

        $quiz->load('questions.options');

        $totalQuestions = $quiz->questions->count();
        $correct = 0;

        foreach ($quiz->questions as $question) {
            $correctOptionIds = $question->options->where('is_correct', true)->pluck('id')->sort()->values();
            $givenOptionIds = collect($data['answers'][$question->id] ?? [])->sort()->values();

            if ($correctOptionIds->all() === $givenOptionIds->all()) {
                $correct++;
            }
        }

        $score = $totalQuestions > 0 ? (int) round(($correct / $totalQuestions) * 100) : 0;

        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'user_id' => $request->user()->id,
            'score' => $score,
            'passed' => $score >= $quiz->passing_score,
            'answers' => $data['answers'],
            'submitted_at' => now(),
        ]);

        return response()->json($attempt, 201);
    }
}
