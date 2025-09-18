<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
        ->subject('Bienvenido a nuestra Barbería')
        ->greeting('Hola, ' . $notifiable->person->first_name . ' 👋')
        ->line('Gracias por registrarte en nuestra barbería. Estamos felices de tenerte con nosotros.')
        ->line('Ahora puedes agendar tu primera cita con nosotros y disfrutar de nuestros servicios.')
        ->line('Si tienes alguna duda, contáctanos. ¡Nos vemos pronto!')
        ->salutation('Saludos, VIP Stylist Barbershop');
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
