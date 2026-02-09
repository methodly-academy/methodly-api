<?php

use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/users', [UserController::class, 'index']);
Route::post('/users/{id}/roles', [UserController::class, 'syncRoles']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);
