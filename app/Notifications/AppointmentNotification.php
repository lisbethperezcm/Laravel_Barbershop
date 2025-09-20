<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Helpers\DateHelper;

class AppointmentNotification extends Notification
{
    use Queueable;

    protected $appointment;

    public function __construct($appointment)
    {
        $this->appointment = $appointment;
    }

    public function via(object $notifiable): array
    {
        $role = strtolower(trim($notifiable->role->name ?? ''));

        // Siempre DB; email solo clientes
        $channels = ['database'];
        if ($role === 'cliente') {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Confirmación de tu Cita en VIP Stylist Barbershop')
            ->view('emails.appointment_notification', [
                'appointment' => $this->appointment,
            ]);
    }

    public function toDatabase($notifiable): array
    {
        $role = strtolower(trim($notifiable->role->name ?? ''));

        $time = DateHelper::formatTime12($this->appointment->start_time);
        $date = DateHelper::formatDateLong($this->appointment->appointment_date);

        $barberFirst = $this->appointment->barber?->person?->first_name ?? '';
        $barberLast  = $this->appointment->barber?->person?->last_name  ?? '';
        $barberName  = trim("$barberFirst $barberLast");

        $clientFirst = $this->appointment->client?->person?->first_name ?? '';
        $clientLast  = $this->appointment->client?->person?->last_name  ?? '';
        $clientName  = trim("$clientFirst $clientLast");

        if ($role === 'cliente') {
            return [
                'title'          => 'Tu cita ha sido reservada',
                'type'           => 'new_appointment',
                'body'           => "Agendaste una cita con el barbero {$barberName} el {$date} a las {$time}.",
                'appointment_id' => $this->appointment->id,
                'role'           => 'cliente',
            ];
        }

        if ($role === 'barbero') {
            return [
                'title'          => 'Nueva cita agendada',
                'type'           => 'new_appointment',
                'body'           => "{$clientName} agendó una cita para {$date} a las {$time}.",
                'appointment_id' => $this->appointment->id,
                'role'           => 'barbero',
            ];
        }

        // Fallback admin u otros
        return [
            'title'          => 'Cita registrada',
            'type'           => 'new_appointment',
            'body'           => "Se creó una cita el {$date} a las {$time}.",
            'appointment_id' => $this->appointment->id,
            'role'           => $role ?: 'unknown',
        ];
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
