<?php

namespace App\Http\Resources;

use App\Models\Client;
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

            // ✅ Tiene factura (paid)
            $paid = $appointment->relationLoaded('invoice')
                ? !is_null($appointment->invoice)
                : $appointment->invoice()->exists();

            // ✅ Obtener la evaluación si existe (intenta con 'barberReview' y si no, con 'review')


            $evaluated = $appointment->relationLoaded('review')
                ? !is_null($appointment->review)
                : $appointment->review()->exists();
            // Estructura del detalle de la evaluación (si existe)
            $reviewData = $evaluated ? [
                'id'         => $appointment->review->id ?? null,
                'rating'     => $appointment->review->rating ?? null,
                'comment'    => $appointment->review->comment ?? null,
            ] : null;

            return [
                'id'               => $appointment->id,
                'client_id'        => $appointment->client_id ? $appointment->client_id :null,
                'client_name'      => $appointment->client ? $appointment->client->person->first_name . ' ' . $appointment->client->person->last_name : "Desconocido",
                'barber_name'      => $appointment->barber ? $appointment->barber->person->first_name . ' ' . $appointment->barber->person->last_name : "Desconocido",
                'appointment_date' => $appointment->appointment_date,
                'start_time'       => $startDateTime,
                'end_time'         => $endDateTime,
                'status'           => $appointment->status->name,
                'created_at'       => $appointment->created_at,
                'created_by'       => $appointment->createdBy
                    ? $appointment->createdBy->person->first_name . ' ' . $appointment->createdBy->person->last_name
                    : 'desconocido',
                'updated_at'       => $appointment->updated_at,
                'paid'             => (bool) $paid,

                //  campo para indicar si fue evaluada
                'evaluated'        => (bool) $evaluated,

                //  detalle de la evaluación si existe
                'review'           => $reviewData,

                'services' => $appointment->services->map(function ($service) {
                    return [
                        'name'     => $service->name,
                        'price'    => $service->current_price,
                        'duration' => $service->duration,
                    ];
                }),
            ];
        });
    }
}
