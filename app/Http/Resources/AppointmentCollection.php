<?php

namespace App\Http\Resources;

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
            return [
                
                'id' => $appointment->id,
                'client_name' => $appointment->client->person->first_name . ' ' . $appointment->client->person->last_name,
                'barber_name' => $appointment->barber->person->first_name . ' ' . $appointment->barber->person->last_name,
                'status' => $appointment->status,
                'appointment_date' => $appointment->appointment_date, // fecha de la cita
                'start_time' => $appointment->start_time, // hora de inicio
                'end_time' => $appointment->end_time, // hora de fin
                'created_at' => $appointment->created_at, // fecha y hora de creación
                'created_by' => $appointment->createdBy ? $appointment->createdBy->person->first_name . ' ' .$appointment->createdBy->person->last_name  : 'desconocido', //
                'updated_at' => $appointment->updated_at, // fecha y hora de actualización
                'services' => $appointment->services->map(function($service) {
                return $service->name;})



            ];
        });
    }
}
