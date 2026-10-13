<?php

use App\Http\Controllers\Api\WorkoutController;
use App\Http\Controllers\Api\WorkoutStatsApiController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

Route::middleware(['auth'])->group(function () {

    // ==================== PAGES ====================
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
//

    Route::get('/inventory', function () {
        return view('inventory.index');
    })->name('inventory.index');

    Route::get('/notifications', function () {
        return view('notifications.index');
    })->name('notifications.index');

    // ==================== LAB 5 + LAB 6 API (session-based) ====================
    Route::prefix('api')->group(function () {

        // Workout CRUD
        Route::get('/workouts',              [WorkoutController::class, 'index']);
        Route::post('/workouts',             [WorkoutController::class, 'store']);
        Route::get('/workouts/{workout}',    [WorkoutController::class, 'show']);
        Route::put('/workouts/{workout}',    [WorkoutController::class, 'update']);
        Route::delete('/workouts/{workout}', [WorkoutController::class, 'destroy']);

        // Lab 6 Stats
        Route::get('/workouts/stats',              [WorkoutStatsApiController::class, 'stats']);
        Route::get('/workouts/recent',             [WorkoutStatsApiController::class, 'recent']);
        Route::get('/workouts/recent-logs',        [WorkoutStatsApiController::class, 'recentLogs']);
        Route::get('/workouts/notifications',      [WorkoutStatsApiController::class, 'notifications']);
        Route::post('/workouts/notifications/read', [WorkoutStatsApiController::class, 'markNotificationsRead']);
    });

});

require __DIR__.'/auth.php';
//