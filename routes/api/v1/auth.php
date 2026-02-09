<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PasswordResetController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLinkEmail']);
Route::post('/reset-password', [PasswordResetController::class, 'reset']);

Route::get('/reset-password/{token}', function ($token) {
    return response()->json([
        'message' => 'Silakan gunakan endpoint POST /api/v1/reset-password untuk mengatur ulang kata sandi.',
        'payload' => [
            'token' => $token,
            'email' => request()->email,
        ]
    ]);
})->name('password.reset');

Route::post('/refresh-token', [AuthController::class, 'refreshToken']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'user']);
});
