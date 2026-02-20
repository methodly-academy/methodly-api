<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use App\Models\QuizQuestion;
use App\Models\QuizOption;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class QuizAttemptController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/quizzes/{id}/start",
     *     summary="Memulai pengerjaan quiz baru",
     *     tags={"Pengerjaan Quiz"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=201, 
     *         description="Kuis berhasil dimulai",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     */
    public function start(Request $request, $quizId): JsonResponse
    {
        $quiz = Quiz::findOrFail($quizId);
        
        $attempt = QuizAttempt::create([
            'user_id' => $request->user()->id,
            'quiz_id' => $quiz->id,
            'status' => 'started',
            'started_at' => now(),
            'score' => 0
        ]);

        return $this->ok('Kuis dimulai', $attempt, 201);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/attempts/{attemptId}/answer",
     *     summary="Submit jawaban per soal",
     *     tags={"Pengerjaan Quiz"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="attemptId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"question_id"},
     *             @OA\Property(property="question_id", type="integer"),
     *             @OA\Property(property="option_id", type="integer", nullable=true),
     *             @OA\Property(property="answer_text", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200, 
     *         description="Jawaban berhasil disimpan",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     */
    public function submitAnswer(Request $request, $attemptId): JsonResponse
    {
        $request->validate([
            'question_id' => 'required|exists:quiz_questions,id',
            'option_id' => 'nullable|exists:quiz_options,id',
            'answer_text' => 'nullable|string',
        ]);

        $attempt = QuizAttempt::where('user_id', $request->user()->id)
            ->where('status', 'started')
            ->findOrFail($attemptId);

        $question = QuizQuestion::with('options')->findOrFail($request->question_id);
        
        $isCorrect = false;
        $pointsEarned = 0;

        if ($question->question_type === 'multiple_choice' || $question->question_type === 'boolean') {
            $option = $question->options->firstWhere('id', $request->option_id);
            if ($option && $option->is_correct) {
                $isCorrect = true;
                $pointsEarned = $question->points;
            }
        }

        // Upsert answer (jika user menjawab ulang soal yang sama dalam attempt yang sama)
        $answer = QuizAttemptAnswer::updateOrCreate(
            [
                'quiz_attempt_id' => $attempt->id,
                'quiz_question_id' => $question->id,
            ],
            [
                'quiz_option_id' => $request->option_id,
                'answer_text' => $request->answer_text,
                'is_correct' => $isCorrect,
                'points_earned' => $pointsEarned,
            ]
        );

        return $this->ok('Jawaban berhasil disimpan', $answer);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/attempts/{attemptId}/finish",
     *     summary="Menyelesaikan pengerjaan quiz",
     *     tags={"Pengerjaan Quiz"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="attemptId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200, 
     *         description="Kuis berhasil diselesaikan",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     */
    public function finish(Request $request, $attemptId): JsonResponse
    {
        $attempt = QuizAttempt::with('answers')
            ->where('user_id', $request->user()->id)
            ->where('status', 'started')
            ->findOrFail($attemptId);

        $totalScore = $attempt->answers->sum('points_earned');

        $attempt->update([
            'status' => 'completed',
            'score' => $totalScore,
            'completed_at' => now()
        ]);

        return $this->ok('Kuis selesai', [
            'attempt_id' => $attempt->id,
            'final_score' => $attempt->score,
            'completed_at' => $attempt->completed_at
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/my-attempts",
     *     summary="Riwayat pengerjaan quiz",
     *     tags={"Pengerjaan Quiz"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Berhasil mendapatkan daftar riwayat pengerjaan kuis")
     * )
     */
    public function myAttempts(Request $request): JsonResponse
    {
        $attempts = QuizAttempt::with('quiz')
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->ok('Berhasil mendapatkan daftar riwayat pengerjaan kuis', $attempts);
    }
}
