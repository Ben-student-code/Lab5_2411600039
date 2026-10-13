<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkoutLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'workout_id',
        'action',
        'changes',
        'workout_name',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    // ==================== RELATIONSHIPS ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workout(): BelongsTo
    {
        return $this->belongsTo(Workout::class);
    }

    // ==================== QUERY SCOPES ====================

    public function scopeRecent($query)
    {
        return $query->orderByDesc('created_at');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeAction($query, string $action)
    {
        return $query->where('action', $action);
    }
}