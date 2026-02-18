<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\CourseController;

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // User Management
    require __DIR__ . '/admin/users.php';

    // Role Management
    require __DIR__ . '/admin/roles.php';

    // Permission Management
    require __DIR__ . '/admin/permissions.php';

    // Level Management
    Route::apiResource('levels',LevelController::class);

    // Course Management
    Route::post('/courses',[CourseController::class, 'store']);
    Route::put('/courses/{course}',[CourseController::class, 'update']);
    Route::delete('/courses/{course}',[CourseController::class, 'destroy']);

});
