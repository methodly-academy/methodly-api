<?php

namespace App\Http\Controllers;

use App\Models\Level;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\LevelResource;

class LevelController extends Controller
{
    public function index(){
        $levels = Level::all();

        return response()->json([
            'status' => 'success',
            'message' => 'list of all levels',
            'count' => $levels->count(),
            'data' => LevelResource::collection($levels)
        ], 200);
    }

    public function store(Request $request): JsonResponse{
        $level = Level::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'level created successfully',
            'data' => new LevelResource($level),
        ], 201);
    }
    
    public function update(Request $request, $id): JsonResponse{
        $level = Level::findOrFail($id);

        $level->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name), 
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'level updated successfully',
            'data' => new LevelResource($level)
        ], 200);
    }

    public function destroy(Level $level): JsonResponse{
        $level->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'level deleted successfully'
        ], 200);
    }
}