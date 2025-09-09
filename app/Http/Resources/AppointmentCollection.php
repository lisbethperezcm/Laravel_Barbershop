<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class AppointmentCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request)
    {
        return $this->collection->map(function ($appointment) {

            $startDateTime = Carbon::parse($appointment->appointment_date . ' ' . $appointment->start_time)->toJSON();
            $endDateTime   = Carbon::parse($appointment->appointment_date . ' ' . $appointment->end_time)->toJSON();
            
             // ✅ Determinar si la cita tiene factura(s) asociada(s)
            // 
            $paid = $appointment->relationLoaded('invoice')
                ? !is_null($appointment->invoice)
                : $appointment->invoice()->exists();
            return [

                'id' => $appointment->id,
                'client_id' => $appointment->client_id,
                'client_name' => $appointment->client->person->first_name . ' ' . $appointment->client->person->last_name,
                'barber_name' => $appointment->barber->person->first_name . ' ' . $appointment->barber->person->last_name,
                'appointment_date' => $appointment->appointment_date, // fecha de la cita
                'start_time' => $startDateTime, // hora de inicio
                'end_time' => $endDateTime, // hora de fin
                'status' => $appointment->status->name, // Estatus de la cita
                'created_at' => $appointment->created_at, // fecha y hora de creación
                'created_by' => $appointment->createdBy ? $appointment->createdBy->person->first_name . ' ' . $appointment->createdBy->person->last_name  : 'desconocido', //
                'updated_at' => $appointment->updated_at, // fecha y hora de actualización
                'paid'             => (bool) $paid, // Indica si la cita tiene factura(s) asociada(s)
                'services' => $appointment->services->map(function ($service) {
                    return [
                        'name'     => $service->name,
                        'price'    => $service->current_price,
                        'duration' => $service->duration
                    ];
                })



            ];
        });
    }
}
