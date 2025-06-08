<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use App\Models\Appointment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentConfirmed extends Notification
{
    use Queueable;

    public function __construct(public Appointment $appointment) {}

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Ваша запись подтверждена')
            ->line("Ваша запись к {$this->appointment->doctor->user->name} на {$this->appointment->appointment_time} подтверждена.")
            ->action('Просмотреть запись', url('/appointments'));
    }
}
