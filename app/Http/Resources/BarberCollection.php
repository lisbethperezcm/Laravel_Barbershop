<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class BarberCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request)
    {
        return $this->collection->map(function ($barber) {
            return [
                'id' => $barber->id,
                'first_name' => $barber->person->first_name,
                'last_name' => $barber->person->last_name,
                // Si no hay persona, devuelve null
                'email' => $barber->person->user->email,
                'role_id' => $barber->person->user->role_id,
                'status' => $barber->status,
                'commission' =>$barber->commission->current_percentage ?? "Desconocido",
                'created_at' => $barber->created_at,
                'updated_at' => $barber->updated_at,
            ];
        });
    }
}
