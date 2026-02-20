<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuizAttemptController;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/quizzes/{id}/start', [QuizAttemptController::class, 'start']);
    Route::post('/attempts/{attemptId}/answer', [QuizAttemptController::class, 'submitAnswer']);
    Route::post('/attempts/{attemptId}/finish', [QuizAttemptController::class, 'finish']);
    Route::get('/my-attempts', [QuizAttemptController::class, 'myAttempts']);
});
