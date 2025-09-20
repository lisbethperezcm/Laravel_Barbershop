<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Helpers\DateHelper;

class AppointmentNotificationClient extends Notification
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
        $channels = ['database', 'mail'];




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
        // $this->roleName ya fue seteado en via()
        $time = DateHelper::formatTime12($this->appointment->start_time);
        $date = DateHelper::formatDateLong($this->appointment->appointment_date);

        $clientFirst = $this->appointment->client?->person?->first_name ?? '';
        $clientLast  = $this->appointment->client?->person?->last_name  ?? '';
        $clientName  = trim("$clientFirst $clientLast");

        $barberFirst = $this->appointment->barber?->person?->first_name ?? '';
        $barberLast  = $this->appointment->barber?->person?->last_name  ?? '';
        $barberName  = trim("$barberFirst $barberLast");


        return [
            'title'          => 'Tu cita ha sido reservada',
            'type'           => 'new_appointment',
            'body'           => "Agendaste una cita con el barbero {$barberName} el {$date} a las {$time}.",
            'appointment_id' => $this->appointment->id,
            'role'           => 'cliente',
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
