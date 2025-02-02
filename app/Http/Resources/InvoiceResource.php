<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request)
    {
        return [
            'id' => $this->id,
            'client_name' => $this->client->person->first_name . ' ' . $this->client->person->last_name,
            'barber_name' => $this->barber->person->first_name . ' ' . $this->barber->person->last_name,
            'status' => $this->status,
            'appointment_date' => $this->appointment_date,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'created_at' => $this->created_at,
            'created_by' => $this->createdBy ? $this->createdBy->person->first_name . ' ' . $this->createdBy->person->last_name : 'desconocido',
            'updated_at' => $this->updated_at,
            'services' => $this->services->map(fn($service) => $service->name)
        ];
    }
}
