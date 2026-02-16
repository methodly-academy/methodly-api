<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LevelController;

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // User Management
    require __DIR__ . '/admin/users.php';

    // Role Management
    require __DIR__ . '/admin/roles.php';

    // Permission Management
    require __DIR__ . '/admin/permissions.php';

    // Level Management
    Route::apiResource('levels',LevelController::class);

});
