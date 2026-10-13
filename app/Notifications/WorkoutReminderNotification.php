<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class WorkoutReminderNotification extends Notification
{
    use Queueable;

    public string $userName;
    public string $date;
    public int $missedCount;

    public function __construct(string $userName, string $date, int $missedCount = 1)
    {
        $this->userName = $userName;
        $this->date = $date;
        $this->missedCount = $missedCount;
    }

    /**
     * Delivery channels — only 'database' so it works without SMTP setup.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Data stored in the notifications table (JSON).
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title'        => '🏋️ Missed Workout Alert',
            'message'      => "Hi {$this->userName}, you haven't logged a workout on {$this->date}. Time to move! 💪",
            'date'         => $this->date,
            'missed_count' => $this->missedCount,
            'url'          => url('/dashboard'),
            'icon'         => 'bi-exclamation-triangle-fill',
            'color'        => 'warning',
        ];
    }

    /**
     * Optional mail representation (not used currently since 'mail' is not in via()).
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Workout Reminder - Fitness Tracker')
            ->greeting("Hi {$this->userName}!")
            ->line("You haven't logged a workout on {$this->date}.")
            ->line('Staying consistent is the key to progress. Get moving today! 💪')
            ->action('Log a Workout', url('/dashboard'))
            ->line('Keep up the great work!');
    }
}