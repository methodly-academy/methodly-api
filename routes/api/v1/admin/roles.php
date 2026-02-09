<?php

use App\Http\Controllers\Admin\RoleController;
use Illuminate\Support\Facades\Route;

Route::apiResource('roles', RoleController::class);
Route::post('/roles/{id}/permissions', [RoleController::class, 'syncPermissions']);
