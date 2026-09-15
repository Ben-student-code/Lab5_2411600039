<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Workout extends Model
{
    protected $table = 'workouts';

    protected $fillable = [
        'date',
        'activity',
        'type',
        'intensity',
        'status',
        'calories',
        'duration',
        'notes',
    ];

    protected $casts = [
        'date' => 'datetime',
    ];
}