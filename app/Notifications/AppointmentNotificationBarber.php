<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Helpers\DateHelper;

class AppointmentNotificationBarber extends Notification
{
    use Queueable;

    protected $appointment;
   

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
      

        // Siempre guardamos en DB; email solo para clientes
        $channels = ['database'];


        return $channels;
    }
    /**
     * Get the mail representation of the notification.
     */


    public function toDatabase($notifiable): array
    {
        // $this->roleName ya fue seteado en via()
        $time = DateHelper::formatTime12($this->appointment->start_time);
        $date = DateHelper::formatDateLong($this->appointment->appointment_date);

        $barberFirst = $this->appointment->barber?->person?->first_name ?? '';
        $barberLast  = $this->appointment->barber?->person?->last_name  ?? '';
        $barberName  = trim("$barberFirst $barberLast");

        $clientFirst = $this->appointment->client?->person?->first_name ?? '';
        $clientLast  = $this->appointment->client?->person?->last_name  ?? '';
        $clientName  = trim("$clientFirst $clientLast");


        return [
            'title'          => 'Nueva cita agendada',
            'type'           => 'new_appointment',
            'body'           => "{$clientName} agendó una cita para {$date} a las {$time}.",
            'appointment_id' => $this->appointment->id,
            'role'           => 'barbero',
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
