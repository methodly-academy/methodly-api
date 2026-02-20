<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\CourseController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// API v1
Route::prefix('v1')->group(function () {
    require __DIR__ . '/api/v1/auth.php';
    
    // Admin Routes
    Route::prefix('admin')->group(function () {
        require __DIR__ . '/api/v1/admin.php';
    });

    // Course Management
    Route::get('/courses',[CourseController::class,'index']);
    Route::get('/courses/{course}',[CourseController::class,'show']);

    // Quiz Actions (Submit, History)
    require __DIR__ . '/api/v1/quiz_actions.php';
});
