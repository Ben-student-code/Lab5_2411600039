<?php

namespace App\Models;

use App\Observers\WorkoutObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([WorkoutObserver::class])]
class Workout extends Model
{
    use HasFactory;

    protected $table = 'workouts';

    protected $fillable = [
        'user_id',
        'date',
        'activity',
        'type',
        'intensity',
        'status',
        'calories',
        'duration',
        'notes',
        'reminder_sent_at',
    ];

    protected $casts = [
        'date' => 'datetime',
        'reminder_sent_at' => 'datetime',
    ];

    // ==================== RELATIONSHIPS ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(WorkoutLog::class);
    }

    // ==================== HELPER METHODS ====================

    public function isCompleted(): bool
    {
        return $this->status === 'Completed';
    }

    public function isToday(): bool
    {
        return $this->date->isToday();
    }

    // ==================== QUERY SCOPES ====================

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'Completed');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('date', today());
    }

    public function scopeRecent($query)
    {
        return $query->orderByDesc('date');
    }
}