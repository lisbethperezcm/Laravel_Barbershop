<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $data = [
            'user_id' => $this->id,
            'email' => $this->email,
            'full_name' => $this->person ? $this->person->first_name . ' ' . $this->person->last_name : null,
            'role' => $this->role->name, // Asumiendo que tienes una relación 'role' en el modelo User
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        // Agregar client_id solo si el usuario es un cliente
        if ($this->role->name === 'Cliente') {
            $data['client_id'] = optional($this->person->client)->id;
        }

        // Agregar barber_id solo si el usuario es un barbero
        if ($this->role->name === 'Barbero') {
            $data['barber_id'] = optional($this->person->barber)->id;
        }

        // Agregar admin_id solo si el usuario es admin
        if ($this->role->name === 'Admin') {
            $data['admin_id'] = $this->id;
        }

        return $data;
    }
}