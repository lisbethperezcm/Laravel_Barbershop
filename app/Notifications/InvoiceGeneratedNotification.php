<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class InvoiceGeneratedNotification extends Notification
{
    use Queueable;

    public $invoice;
    /**
     * Create a new notification instance.
     */
    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
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
        $invoice = $this->invoice; // Obtener la factura

        // Inicializamos el correo
        $mailMessage = (new MailMessage)
            ->subject('Factura Generada - #' . $invoice->id)
            ->line('Estimado/a,')
            ->line('Su factura ha sido generada con éxito. A continuación, se detallan los datos de la misma:');

        // Detalles de la factura
        $mailMessage->line('ID de la factura: ' . $invoice->id)
                    ->line('Fecha de la factura: ' . $invoice->created_at->format('d-m-Y'))
                    ->line('Estado de la factura: ' .ucfirst($invoice->status))
                    ->line('Subtotal: RD$ ' . number_format($invoice->total_amount, 2))
                    ->line('ITBIS (18%): RD$ ' . number_format($invoice->tax_amount, 2))
                    ->line('Total con ITBIS: RD$ ' . number_format($invoice->total_amount + $invoice->tax_amount, 2));

        // Detalle de los servicios en la factura
        $mailMessage->line('Servicios facturados:');
        foreach ($invoice->invoiceDetails as $detail) {
            $service = $detail->service;
            $mailMessage->line('- ' . $service->name . ' (Cantidad: ' . $detail->quantity . ') - RD$ ' . number_format($detail->total, 2));
        }

        // Agregar información adicional si es necesario
        $mailMessage->line('Gracias por su preferencia.');

        return $mailMessage;
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
