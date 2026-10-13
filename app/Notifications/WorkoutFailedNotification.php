<?php

namespace App\Notifications;

use App\Models\Workout;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class WorkoutFailedNotification extends Notification
{
    use Queueable;

    public Workout $workout;

    public function __construct(Workout $workout)
    {
        $this->workout = $workout;
    }

    /**
     * Delivery channels — only 'database' so it works without SMTP.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Data stored in the notifications table.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title'        => '❌ Workout Failed',
            'message'      => "Your workout '{$this->workout->activity}' was marked as Failed. Consider rescheduling it!",
            'workout_id'   => $this->workout->id,
            'workout_name' => $this->workout->activity,
            'status'       => $this->workout->status,
            'date'         => $this->workout->date ? $this->workout->date->format('M j, Y') : null,
            'url'          => url('/inventory'),
            'icon'         => 'bi-x-circle-fill',
            'color'        => 'danger',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Workout Failed - Fitness Tracker')
            ->line("Your workout '{$this->workout->activity}' was marked as Failed.")
            ->line('Consider rescheduling it and staying consistent! 💪')
            ->action('View Workouts', url('/inventory'));
    }
}