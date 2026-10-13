<?php

namespace App\Observers;

use App\Models\Workout;
use App\Models\WorkoutLog;
use App\Notifications\WorkoutFailedNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WorkoutObserver
{
    /**
     * Temporary storage for original values during update.
     * Kept in a static array keyed by workout ID — NOT on the model itself.
     */
    protected static array $originalValues = [];

    /**
     * Runs AFTER a workout is created.
     */
    public function created(Workout $workout): void
    {
        DB::transaction(function () use ($workout) {
            WorkoutLog::create([
                'user_id'      => Auth::id() ?? $workout->user_id,
                'workout_id'   => $workout->id,
                'action'       => 'created',
                'changes'      => [
                    'after' => $workout->only([
                        'activity', 'type', 'intensity',
                        'status', 'calories', 'duration', 'date', 'notes',
                    ]),
                ],
                'workout_name' => $workout->activity,
            ]);
        });

        if ($workout->status === 'Failed') {
            $this->notifyFailed($workout);
        }
    }

    /**
     * Runs BEFORE a workout is updated.
     * Capture original values in a static store (NOT on the model).
     */
    public function updating(Workout $workout): void
    {
        self::$originalValues[$workout->id] = $workout->getOriginal();
    }

    /**
     * Runs AFTER a workout is updated.
     */
    public function updated(Workout $workout): void
    {
        $original = self::$originalValues[$workout->id] ?? [];

        $changes = [];
        foreach ($workout->getChanges() as $field => $newValue) {
            if ($field === 'updated_at') continue;
            $changes[$field] = [
                'from' => $original[$field] ?? null,
                'to'   => $newValue,
            ];
        }

        // Clean up the static store
        unset(self::$originalValues[$workout->id]);

        if (empty($changes)) {
            return;
        }

        DB::transaction(function () use ($workout, $changes) {
            WorkoutLog::create([
                'user_id'      => Auth::id() ?? $workout->user_id,
                'workout_id'   => $workout->id,
                'action'       => 'updated',
                'changes'      => $changes,
                'workout_name' => $workout->activity,
            ]);
        });

        // Send Failed notification if status just changed to Failed
        $statusChanged = isset($changes['status']);
        if ($statusChanged && $workout->status === 'Failed') {
            $this->notifyFailed($workout);
        }
    }

    /**
     * Runs BEFORE a workout is deleted.
     */
    public function deleting(Workout $workout): void
    {
        DB::transaction(function () use ($workout) {
            WorkoutLog::create([
                'user_id'      => Auth::id() ?? $workout->user_id,
                'workout_id'   => null,
                'action'       => 'deleted',
                'changes'      => [
                    'before' => $workout->only([
                        'activity', 'type', 'intensity',
                        'status', 'calories', 'duration', 'date', 'notes',
                    ]),
                ],
                'workout_name' => $workout->activity,
            ]);
        });
    }

    /**
     * Send a "Workout Failed" notification (throttled to 1/hour per workout).
     */
    protected function notifyFailed(Workout $workout): void
    {
        $user = $workout->user;
        if (! $user) return;

        $recentlyNotified = DB::table('notifications')
            ->where('type', WorkoutFailedNotification::class)
            ->where('created_at', '>=', now()->subHour())
            ->where('data', 'like', '%"workout_id":' . $workout->id . '%')
            ->exists();

        if ($recentlyNotified) {
            return;
        }

        $user->notify(new WorkoutFailedNotification($workout));
    }
}