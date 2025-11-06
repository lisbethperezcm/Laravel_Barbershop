<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'client_name' => $this->client ? $this->client->person->first_name . ' ' . $this->client->person->last_name : "Desconocido",
            'barber_name' => $this->barber ? $this->barber->person->first_name . ' ' . $this->barber->person->last_name : "Desconocido",
            'status' => $this->status->name,
            'appointment_date' => $this->appointment_date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'created_at' => $this->created_at,
            'created_by' => $this->createdBy ? $this->createdBy->person->first_name . ' ' . $this->createdBy->person->last_name : 'desconocido',
            'updated_at' => $this->updated_at,
            'services' => $this->services->map(fn($service) =>  [
                        'name'     => $service->name,
                        'price'    => $service->current_price,
                        'duration' => $service->duration
                    ])->toArray()
        ];
    }

   
}
