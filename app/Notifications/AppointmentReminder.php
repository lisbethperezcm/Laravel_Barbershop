<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Helpers\DateHelper;

class AppointmentReminder extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  \App\Models\Appointment $appointment
     * @param  string $when  'day_before' | 'one_hour'
     */
    public function __construct(
        public $appointment,
        public string $when
    ) {}



    public function via(object $notifiable): array
{
    $role = strtolower($notifiable->role->name ?? '');

    // Solo clientes reciben la notificación (correo + base de datos).
    if ($role === 'cliente') {
        return ['database', 'mail'];
    }

    // Cualquier otro rol: no enviar nada.
    return [];
}

public function toMail(object $notifiable): ?MailMessage
{
    // Con via() filtrado, aquí normalmente solo entran clientes.
    return (new MailMessage)
        ->subject($this->when === 'day_before'
            ? 'Recordatorio de cita - Mañana'
            : 'Recordatorio de cita - En 1 hora')
        ->view('emails.appointment_reminder', [
            'appointment' => $this->appointment,
            'whenLabel'   => $this->when === 'day_before' ? 'mañana' : 'en 1 hora',
        ]);
}

public function toDatabase($notifiable): array
{
    // Idem: solo clientes gracias a via()
    $time = DateHelper::formatTime12($this->appointment->start_time);
    $date = DateHelper::formatDateLong($this->appointment->appointment_date);

    $barberName = optional($this->appointment->barber?->person)->first_name.' '.
                  optional($this->appointment->barber?->person)->last_name;

    return [
        'title'          => 'Recordatorio de cita — '.($this->when === 'day_before' ? 'Mañana' : 'En 1 hora'),
        'type'           => 'reminder',
        'when'           => $this->when,
        'body'           => "No olvides tu cita con el barbero {$barberName} el {$date} a las {$time}. ¡Te esperamos!",
        'appointment_id' => $this->appointment->id,
        'role'           => 'cliente',
    ];
}
}