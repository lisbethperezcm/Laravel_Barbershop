<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Helpers\DateHelper;

class AppointmentNotification extends Notification
{
    use Queueable;

    protected $appointment;
    protected $role;

    /*

    Tipos de las notificaciones
type NotificationType =
  | "new_appointment"
  | "reminder"
  | "canceled"
  | "payment"
  | "review";
    */
    /**
     * Create a new notification instance.
     */
    public function __construct($appointment)
    {
        $this->appointment = $appointment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
     public function via(object $notifiable): array
    {
        $role = strtolower($notifiable->role->name ?? '');

        // Siempre guardamos en DB; email solo para clientes
        $channels = ['database'];

        if ($role === 'cliente') {
            $channels[] = 'mail';
        }

        return $channels;
    }
    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): ?MailMessage
    {
       
       
            return (new MailMessage)
                ->subject('Confirmación de tu Cita en VIP Stylist Barbershop')
                ->view('emails.appointment_notification', ['appointment' => $this->appointment]);
       
    }


    
    public function toDatabase($notifiable): array
    {
        // Detectar rol del receptor para personalizar el mensaje
        $roleName = strtolower($notifiable->role->name ?? '');
        $time = DateHelper::formatTime12($this->appointment->start_time);
        $date = DateHelper::formatDateLong($this->appointment->appointment_date);
        $barberName = $this->appointment->barber->person->first_name . ' ' . $this->appointment->barber->person->last_name;
        $clientName = $this->appointment->client->person->first_name . ' ' . $this->appointment->client->person->last_name;



        if ($this->role === 'cliente') {
            return [
                'title' => 'Tu cita ha sido reservada',
                'type' => 'new_appointment',
                'body'  => "Tienes una cita con el barbero {$barberName} el {$date} a las {$time}.",
                'appointment_id' => $this->appointment->id,
                'role' => 'cliente',
            ];
        }

        if ($this->role === 'barbero') {
            return [
                'title' => 'Nueva cita asignada',
                'type' => 'new_appointment',
                'body'  => "Atenderás a {$clientName} el {$date} a las {$time}.",
                'appointment_id' => $this->appointment->id,
                'role' => 'barbero',
            ];
        }

        // Fallback (admin u otros)
        return [
            'title' => 'Cita registrada',
            'type' => 'new_appointment',
            'body'  => "Se creó una cita el {$date} a las {$time}.",
            'appointment_id' => $this->appointment->id,
            'role' => $roleName ?: 'unknown',
        ];
    }


    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
