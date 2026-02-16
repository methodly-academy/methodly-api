<?php

namespace App\Http\Controllers;

use App\Models\QuizQuestion;
use App\Models\QuizOption;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\QuizQuestionResource;
use Illuminate\Support\Facades\DB;

class QuizQuestionController extends Controller
{
    public function index(): JsonResponse
    {
        $questions = QuizQuestion::with('options')->get();

        return response()->json([
            'status' => 'success',
            'message' => 'list of all quiz questions',
            'count' => $questions->count(),
            'data' => QuizQuestionResource::collection($questions)
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'quiz_id' => 'required|exists:quizzes,id',
            'question_text' => 'required|string',
            'options' => 'array|min:1',
            'options.*.option_text' => 'required|string',
            'options.*.is_correct' => 'boolean',
        ]);

        $question = DB::transaction(function () use ($request) {
            $q = QuizQuestion::create([
                'quiz_id' => $request->quiz_id,
                'question_text' => $request->question_text,
            ]);

            if ($request->has('options')) {
                foreach ($request->options as $option) {
                    $q->options()->create([
                        'option_text' => $option['option_text'],
                        'is_correct' => $option['is_correct'] ?? false,
                    ]);
                }
            }
            return $q;
        });

        return response()->json([
            'status' => 'success',
            'message' => 'quiz question created successfully',
            'data' => new QuizQuestionResource($question->load('options')),
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $question = QuizQuestion::with('options')->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'message' => 'quiz question detail',
            'data' => new QuizQuestionResource($question),
        ], 200);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $question = QuizQuestion::findOrFail($id);

        $request->validate([
            'quiz_id' => 'sometimes|exists:quizzes,id',
            'question_text' => 'sometimes|string',
            'options' => 'sometimes|array|min:1',
            'options.*.option_text' => 'required_with:options|string',
            'options.*.is_correct' => 'boolean',
        ]);

        $question = DB::transaction(function () use ($request, $question) {
            $question->update($request->only(['quiz_id', 'question_text']));

            if ($request->has('options')) {
                $question->options()->delete();
                foreach ($request->options as $option) {
                    $question->options()->create([
                        'option_text' => $option['option_text'],
                        'is_correct' => $option['is_correct'] ?? false,
                    ]);
                }
            }
            return $question;
        });

        return response()->json([
            'status' => 'success',
            'message' => 'quiz question updated successfully',
            'data' => new QuizQuestionResource($question->load('options'))
        ], 200);
    }

    public function destroy($id): JsonResponse
    {
        $question = QuizQuestion::findOrFail($id);
        $question->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'quiz question deleted successfully'
        ], 200);
    }
}
