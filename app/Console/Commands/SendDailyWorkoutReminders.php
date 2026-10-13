<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Workout;
use App\Notifications\WorkoutReminderNotification;
use Illuminate\Console\Command;

class SendDailyWorkoutReminders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'workouts:send-reminders';

    /**
     * The console command description.
     */
    protected $description = 'Send daily reminders to users who have not logged a workout today.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Checking for missed workouts...');

        $today = now()->toDateString();
        $formattedDate = now()->format('F j, Y');
        $sentCount = 0;
        $skippedCount = 0;

        User::chunk(100, function ($users) use ($today, $formattedDate, &$sentCount, &$skippedCount) {
            foreach ($users as $user) {

                // Did they log a workout today?
                $hasWorkoutToday = Workout::where('user_id', $user->id)
                    ->whereDate('date', $today)
                    ->exists();

                if ($hasWorkoutToday) {
                    $skippedCount++;
                    continue;
                }

                // Skip if we already sent a reminder today
                $alreadyRemindedToday = $user->notifications()
                    ->where('type', WorkoutReminderNotification::class)
                    ->whereDate('created_at', $today)
                    ->exists();

                if ($alreadyRemindedToday) {
                    $skippedCount++;
                    continue;
                }

                // Send the reminder
                $user->notify(new WorkoutReminderNotification(
                    userName: $user->name,
                    date: $formattedDate,
                    missedCount: 1,
                ));

                $sentCount++;
                $this->line("  → Reminder sent to {$user->email}");
            }
        });

        $this->info("Done! Sent: {$sentCount}, Skipped: {$skippedCount}");
        return self::SUCCESS;
    }
}