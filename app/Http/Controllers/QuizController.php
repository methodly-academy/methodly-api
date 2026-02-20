<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\QuizResource;

class QuizController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/admin/quizzes",
     *     summary="Daftar kuis (Filter berdasarkan course_id atau chapter_id)",
     *     tags={"Manajemen Quiz"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="course_id",
     *         in="query",
     *         required=false,
     *         description="ID Kursus untuk memfilter kuis",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="chapter_id",
     *         in="query",
     *         required=false,
     *         description="ID Bab untuk memfilter kuis",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200, 
     *         description="Berhasil mendapatkan daftar kuis",
     *         @OA\JsonContent(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/SuccessResponse"),
     *                 @OA\Schema(
     *                     @OA\Property(property="count", type="integer", example=1),
     *                     @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Quiz"))
     *                 )
     *             }
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'course_id' => 'nullable|exists:courses,id',
            'chapter_id' => 'nullable|exists:chapters,id',
        ]);

        $quizzes = Quiz::when($request->chapter_id, function ($query, $chapter_id) {
                return $query->where('chapter_id', $chapter_id);
            })
            ->when($request->course_id, function ($query, $course_id) {
                return $query->whereHas('chapter', function ($q) use ($course_id) {
                    $q->where('course_id', $course_id);
                });
            })
            ->get();

        return $this->ok('Berhasil mendapatkan daftar kuis', QuizResource::collection($quizzes), 200, ['count' => $quizzes->count()]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/quizzes",
     *     summary="Buat quiz baru",
     *     tags={"Manajemen Quiz"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"chapter_id","title"},
     *             @OA\Property(property="chapter_id", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="Quiz Dasar Laravel")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201, 
     *         description="Kuis berhasil dibuat",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'chapter_id' => 'required|exists:chapters,id',
            'title' => 'required|string|max:255',
        ]);

        $quiz = Quiz::create([
            'chapter_id' => $request->chapter_id,
            'title' => $request->title,
        ]);

        return $this->ok('Kuis berhasil dibuat', new QuizResource($quiz), 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/admin/quizzes/{id}",
     *     summary="Detail quiz",
     *     tags={"Manajemen Quiz"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Berhasil mendapatkan detail kuis")
     * )
     */
    public function show($id): JsonResponse
    {
        $quiz = Quiz::findOrFail($id);

        return $this->ok('Berhasil mendapatkan detail kuis', new QuizResource($quiz));
    }

    /**
     * @OA\Put(
     *     path="/api/v1/admin/quizzes/{id}",
     *     summary="Update quiz",
     *     tags={"Manajemen Quiz"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="chapter_id", type="integer", example=1),
     *             @OA\Property(property="title", type="string", example="Quiz Laravel Lanjutan")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Kuis berhasil diperbarui")
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        $quiz = Quiz::findOrFail($id);

        $request->validate([
            'chapter_id' => 'sometimes|exists:chapters,id',
            'title' => 'sometimes|string|max:255',
        ]);

        $quiz->update($request->only(['chapter_id', 'title']));

        return $this->ok('Kuis berhasil diperbarui', new QuizResource($quiz));
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/admin/quizzes/{id}",
     *     summary="Hapus quiz",
     *     tags={"Manajemen Quiz"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Kuis berhasil dihapus")
     * )
     */
    public function destroy($id): JsonResponse
    {
        $quiz = Quiz::findOrFail($id);
        $quiz->delete();

        return $this->ok('Kuis berhasil dihapus');
    }
}
