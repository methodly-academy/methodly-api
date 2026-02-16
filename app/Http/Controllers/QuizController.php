<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\QuizResource;

class QuizController extends Controller
{
    public function index(): JsonResponse
    {
        $quizzes = Quiz::all();

        return response()->json([
            'status' => 'success',
            'message' => 'list of all quizzes',
            'count' => $quizzes->count(),
            'data' => QuizResource::collection($quizzes)
        ], 200);
    }

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

        return response()->json([
            'status' => 'success',
            'message' => 'quiz created successfully',
            'data' => new QuizResource($quiz),
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $quiz = Quiz::findOrFail($id);

        return response()->json([
            'status' => 'success',
            'message' => 'quiz detail',
            'data' => new QuizResource($quiz),
        ], 200);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $quiz = Quiz::findOrFail($id);

        $request->validate([
            'chapter_id' => 'sometimes|exists:chapters,id',
            'title' => 'sometimes|string|max:255',
        ]);

        $quiz->update($request->only(['chapter_id', 'title']));

        return response()->json([
            'status' => 'success',
            'message' => 'quiz updated successfully',
            'data' => new QuizResource($quiz)
        ], 200);
    }

    public function destroy($id): JsonResponse
    {
        $quiz = Quiz::findOrFail($id);
        $quiz->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'quiz deleted successfully'
        ], 200);
    }
}
