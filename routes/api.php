<?php

use App\Http\Controllers\Api\WorkoutController;
use Illuminate\Support\Facades\Route;

Route::apiResource('workouts', WorkoutController::class);