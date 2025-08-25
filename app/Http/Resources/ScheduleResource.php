<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'barber_id'  => $this->barber_id,
            'day_id'     => $this->day_id,
            'day_name'   => $this->day?->name, // si tienes relación con tabla Days
            'start_time' => $this->start_time,
            'end_time'   => $this->end_time,
            'status_id'  => $this->status_id,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
