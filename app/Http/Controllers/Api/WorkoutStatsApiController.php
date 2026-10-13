<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Workout;
use App\Models\WorkoutLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkoutStatsApiController extends Controller
{
    /**
     * Aggregated stats for the current user's dashboard.
     * Called every 30 seconds by the JS poller.
     */
    public function stats()
    {
        $userId = Auth::id();

        $workouts = Workout::where('user_id', $userId)->get();

        $totalWorkouts = $workouts->count();
        $completed = $workouts->where('status', 'Completed')->count();
        $ongoing = $workouts->where('status', 'Ongoing')->count();
        $failed = $workouts->where('status', 'Failed')->count();
        $totalCalories = $workouts->sum('calories');
        $totalDuration = $workouts->sum('duration');
        $completionRate = $totalWorkouts > 0
            ? round(($completed / $totalWorkouts) * 100)
            : 0;

        // This-week stats
        $startOfWeek = now()->startOfWeek();
        $thisWeekWorkouts = $workouts->filter(fn ($w) => $w->date >= $startOfWeek)->count();

        // Today
        $todayWorkouts = $workouts->filter(fn ($w) => $w->date->isToday())->count();

        // Streak (consecutive days with at least 1 workout)
        $streak = $this->calculateStreak($userId);

        return response()->json([
            'total_workouts'     => $totalWorkouts,
            'completed'          => $completed,
            'ongoing'            => $ongoing,
            'failed'             => $failed,
            'total_calories'     => (int) $totalCalories,
            'total_duration'     => (int) $totalDuration,
            'completion_rate'    => $completionRate,
            'this_week'          => $thisWeekWorkouts,
            'today_count'        => $todayWorkouts,
            'streak_days'        => $streak,
            'has_workout_today'  => $todayWorkouts > 0,
            'last_updated'       => now()->toIso8601String(),
        ]);
    }

    /**
     * Recent workouts (limit 10) for the activity table.
     */
    public function recent()
    {
        return Workout::where('user_id', Auth::id())
            ->orderByDesc('date')
            ->limit(10)
            ->get([
                'id', 'date', 'activity', 'type',
                'intensity', 'status', 'calories', 'duration',
            ]);
    }

    /**
     * Recent workout audit logs (limit 10) — Lab 6 feature.
     */
    public function recentLogs()
    {
        return WorkoutLog::with('workout:id,activity,type')
            ->where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->limit(10)
            ->get([
                'id', 'action', 'workout_name',
                'changes', 'created_at',
            ]);
    }

    /**
     * Unread notifications for the current user.
     */
    public function notifications()
    {
        $user = Auth::user();

        return response()->json([
            'unread_count' => $user->unreadNotifications()->count(),
            'unread'       => $user->unreadNotifications()->limit(10)->get(),
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markNotificationsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return response()->json(['success' => true]);
    }

    /**
     * Calculate the user's workout streak in consecutive days.
     */
    protected function calculateStreak(int $userId): int
    {
        $dates = Workout::where('user_id', $userId)
            ->orderByDesc('date')
            ->pluck('date')
            ->map(fn ($d) => $d->toDateString())
            ->unique()
            ->values();

        if ($dates->isEmpty()) {
            return 0;
        }

        $streak = 0;
        $checkDate = now()->startOfDay();

        // If no workout today, start checking from yesterday
        if ($dates->first() !== $checkDate->toDateString()) {
            $checkDate = $checkDate->subDay();
        }

        foreach ($dates as $dateStr) {
            if ($dateStr === $checkDate->toDateString()) {
                $streak++;
                $checkDate = $checkDate->subDay();
            } elseif ($dateStr < $checkDate->toDateString()) {
                break;
            }
        }

        return $streak;
    }
}