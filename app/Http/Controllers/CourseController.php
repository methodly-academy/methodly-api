<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\CourseResource;

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
}
