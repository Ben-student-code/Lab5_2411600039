<?php

use App\Http\Controllers\Api\WorkoutController;
use App\Http\Controllers\Api\WorkoutStatsApiController;
use Illuminate\Support\Facades\Route;

// Lab 5 — Workout CRUD
Route::apiResource('workouts', WorkoutController::class);

// Lab 6 — Stats, recent activity, notifications, and audit logs
Route::middleware('auth')->prefix('workouts')->group(function () {
    Route::get('/stats',              [WorkoutStatsApiController::class, 'stats']);
    Route::get('/recent',             [WorkoutStatsApiController::class, 'recent']);
    Route::get('/recent-logs',        [WorkoutStatsApiController::class, 'recentLogs']);
    Route::get('/notifications',      [WorkoutStatsApiController::class, 'notifications']);
    Route::post('/notifications/read', [WorkoutStatsApiController::class, 'markNotificationsRead']);
});