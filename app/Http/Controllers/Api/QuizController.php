<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function show(Quiz $quiz)
    {
        return $quiz->load('questions.options');
    }

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
