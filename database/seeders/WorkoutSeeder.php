<?php

namespace Database\Seeders;

use App\Models\Workout;
use Illuminate\Database\Seeder;

class WorkoutSeeder extends Seeder
{
    public function run(): void
    {
        $workouts = [
            ['date' => '2025-08-11 14:30', 'activity' => 'Push-ups',         'type' => 'strength',    'intensity' => 'high',   'status' => 'Completed', 'calories' => 150, 'duration' => 15, 'notes' => '3 sets of 20 reps'],
            ['date' => '2025-08-11 09:00', 'activity' => 'Morning Jog',      'type' => 'cardio',      'intensity' => 'medium', 'status' => 'Completed', 'calories' => 320, 'duration' => 25, 'notes' => '2.5 miles'],
            ['date' => '2025-08-10 16:00', 'activity' => 'Yoga Session',     'type' => 'flexibility', 'intensity' => 'low',    'status' => 'Completed', 'calories' => 80,  'duration' => 30, 'notes' => 'Vinyasa flow'],
            ['date' => '2025-08-10 11:30', 'activity' => 'HIIT Workout',     'type' => 'hiit',        'intensity' => 'high',   'status' => 'Completed', 'calories' => 450, 'duration' => 20, 'notes' => '4 rounds'],
            ['date' => '2025-08-10 08:00', 'activity' => 'Weight Training',  'type' => 'strength',    'intensity' => 'high',   'status' => 'Ongoing',   'calories' => 280, 'duration' => 35, 'notes' => 'Upper body'],
            ['date' => '2025-08-09 17:45', 'activity' => 'Evening Run',      'type' => 'cardio',      'intensity' => 'medium', 'status' => 'Completed', 'calories' => 350, 'duration' => 30, 'notes' => '3 miles'],
            ['date' => '2025-08-09 14:00', 'activity' => 'Pilates',          'type' => 'flexibility', 'intensity' => 'low',    'status' => 'Completed', 'calories' => 120, 'duration' => 25, 'notes' => 'Core focus'],
            ['date' => '2025-08-09 07:30', 'activity' => 'Sprint Intervals', 'type' => 'hiit',        'intensity' => 'high',   'status' => 'Failed',    'calories' => 300, 'duration' => 15, 'notes' => 'Did not complete'],
            ['date' => '2025-08-08 18:00', 'activity' => 'Bicep Curls',      'type' => 'strength',    'intensity' => 'medium', 'status' => 'Completed', 'calories' => 100, 'duration' => 20, 'notes' => '3 sets of 12 reps'],
            ['date' => '2025-08-08 12:00', 'activity' => 'Brisk Walk',       'type' => 'cardio',      'intensity' => 'low',    'status' => 'Completed', 'calories' => 180, 'duration' => 40, 'notes' => '2 miles'],
            ['date' => '2025-08-07 15:30', 'activity' => 'Deadlifts',        'type' => 'strength',    'intensity' => 'high',   'status' => 'Completed', 'calories' => 200, 'duration' => 25, 'notes' => '5 sets'],
            ['date' => '2025-08-07 10:00', 'activity' => 'Cycling',          'type' => 'cardio',      'intensity' => 'medium', 'status' => 'Completed', 'calories' => 400, 'duration' => 45, 'notes' => 'Stationary bike'],
        ];

        foreach ($workouts as $w) {
            Workout::create($w);
        }
    }
}