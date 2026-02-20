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
    /**
     * @OA\Get(
     *     path="/api/v1/admin/quiz-questions",
     *     summary="Daftar pertanyaan quiz (Filter berdasarkan quiz_id)",
     *     tags={"Manajemen Pertanyaan Quiz"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="quiz_id",
     *         in="query",
     *         required=true,
     *         description="ID Quiz untuk memfilter pertanyaan",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Berhasil mendapatkan daftar pertanyaan kuis")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'quiz_id' => 'required|exists:quizzes,id'
        ]);

        $questions = QuizQuestion::with('options')
            ->where('quiz_id', $request->quiz_id)
            ->get();

        return $this->ok('Berhasil mendapatkan daftar pertanyaan kuis', QuizQuestionResource::collection($questions), 200, ['count' => $questions->count()]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/quiz-questions",
     *     summary="Buat pertanyaan quiz baru",
     *     tags={"Manajemen Pertanyaan Quiz"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"quiz_id","question_text","question_type"},
     *             @OA\Property(property="quiz_id", type="integer", example=1),
     *             @OA\Property(property="question_text", type="string", example="Apa itu Laravel?"),
     *             @OA\Property(property="question_type", type="string", enum={"multiple_choice", "boolean", "short_answer", "essay"}, example="multiple_choice"),
     *             @OA\Property(property="points", type="integer", example=10),
     *             @OA\Property(property="explanation", type="string", example="Laravel adalah framework PHP."),
     *             @OA\Property(
     *                 property="options",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="option_text", type="string", example="Framework PHP"),
     *                     @OA\Property(property="is_correct", type="boolean", example=true)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Pertanyaan berhasil dibuat")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'quiz_id' => 'required|exists:quizzes,id',
            'question_text' => 'required|string',
            'question_type' => 'required|string|in:multiple_choice,boolean,short_answer,essay',
            'points' => 'integer|min:0',
            'explanation' => 'nullable|string',
            'options' => 'required_if:question_type,multiple_choice,boolean|array',
            'options.*.option_text' => 'required_with:options|string',
            'options.*.is_correct' => 'boolean',
        ]);

        $question = DB::transaction(function () use ($request) {
            $q = QuizQuestion::create([
                'quiz_id' => $request->quiz_id,
                'question_text' => $request->question_text,
                'question_type' => $request->input('question_type', 'multiple_choice'),
                'points' => $request->input('points', 10),
                'explanation' => $request->input('explanation'),
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

        return $this->ok('Pertanyaan Quiz berhasil dibuat', new QuizQuestionResource($question->load('options')), 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/quiz-questions/{id}",
     *     summary="Detail pertanyaan quiz",
     *     tags={"Manajemen Pertanyaan Quiz"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Berhasil mendapatkan detail pertanyaan kuis")
     * )
     */
    public function show($id): JsonResponse
    {
        $question = QuizQuestion::with('options')->findOrFail($id);

        return $this->ok('Berhasil mendapatkan detail pertanyaan kuis', new QuizQuestionResource($question));
    }

    /**
     * @OA\Put(
     *     path="/api/v1/admin/quiz-questions/{id}",
     *     summary="Update pertanyaan quiz",
     *     tags={"Manajemen Pertanyaan Quiz"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="quiz_id", type="integer", example=1),
     *             @OA\Property(property="question_text", type="string", example="Apa keunggulan Laravel?"),
     *             @OA\Property(property="question_type", type="string", example="multiple_choice"),
     *             @OA\Property(property="points", type="integer", example=15),
     *             @OA\Property(property="explanation", type="string", example="Eloquent ORM adalah salah satunya.")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Pertanyaan berhasil diperbarui")
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        $question = QuizQuestion::findOrFail($id);

        $request->validate([
            'quiz_id' => 'sometimes|exists:quizzes,id',
            'question_text' => 'sometimes|string',
            'question_type' => 'sometimes|string|in:multiple_choice,boolean,short_answer,essay',
            'points' => 'sometimes|integer|min:0',
            'explanation' => 'sometimes|nullable|string',
            'options' => 'sometimes|array',
            'options.*.option_text' => 'required_with:options|string',
            'options.*.is_correct' => 'boolean',
        ]);

        $question = DB::transaction(function () use ($request, $question) {
            $question->update($request->only(['quiz_id', 'question_text', 'question_type', 'points', 'explanation']));

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

        return $this->ok('Pertanyaan Quiz berhasil diperbarui', new QuizQuestionResource($question->load('options')));
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/admin/quiz-questions/{id}",
     *     summary="Hapus pertanyaan quiz",
     *     tags={"Manajemen Pertanyaan Quiz"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Pertanyaan berhasil dihapus")
     * )
     */
    public function destroy($id): JsonResponse
    {
        $question = QuizQuestion::findOrFail($id);
        $question->delete();

        return $this->ok('Pertanyaan Quiz berhasil dihapus');
    }
}
