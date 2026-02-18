<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\CourseResource;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\DeleteCourseRequest;

class CourseController extends Controller
{

    public function index(Request $request): JsonResponse
    {
        // 1. Ambil input dari user (misal dari query params ?search=laravel&type=free&limit=10)
        $keyword = $request->input('search');
        $type    = $request->input('type');
        // membandingkan request user dengan 50, ambil yang lebih kecil, jika user, meminta lebih dari 50, maka akan dipaksa menjadi 50
        $limit   = min($request->input('limit', 10),50);

        // 2. Eksekusi Query dengan Chaining Scopes
        $courses = Course::query()
        // Mengambil data relasi level
        ->with('level')
        // Hanya ambil yg sudah dipublish
        ->published()
        // Cari berdasarkan nama
        ->search($keyword)
        // Filter berdasarkan tipe
        ->byType($type)
        // Pagination dengan limit, misal limit 5 maka, tiap page hanya berisi 5 data
        ->paginate($limit);  
        
        return response()->json([
                'status' => 'success',
                'message' => 'list of courses fetched successfully',
                'total_items' => $courses->total(),
                // Format Respon Json mengikuti CourseResource
                'data' => CourseResource::collection($courses),
                'pagination' => [
                    'current_page' => $courses->currentPage(),
                    'per_page' => $courses->perPage(),
                    'last_page' => $courses->lastPage(),
                    'next_page_url' => $courses->nextPageUrl(),
                    'prev_page_url' => $courses->previousPageUrl(),
            ]
        ], 200);
    }

    public function store(StoreCourseRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['slug'] = Str::slug($validated['name']);

        $course = Course::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'course created successfully',
            'data' => new CourseResource($course)
        ], 201);
    }

    public function update(StoreCourseRequest $request, Course $course): JsonResponse
    {
        $validated = $request->validated();
        $validated['slug'] = Str::slug($validated['name']);

        $course->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'course updated successfully',
            'data' => new CourseResource($course)
        ], 200);
    }

    public function destroy(DeleteCourseRequest $request, Course $course): JsonResponse{
        $course->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'course deleted successfully'
        ], 200);
    }

    public function show(Course $course): JsonResponse{
        $course->load('level');

        return response()->json([
            'status' => 'success',
            'message' => 'course detail retrieved successfully',
            'data' => new CourseResource($course)
        ], 200);
    }
}
