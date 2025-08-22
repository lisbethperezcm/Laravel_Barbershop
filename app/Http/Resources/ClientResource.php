<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
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
            'first_name' => $this->person->first_name,
            'last_name' => $this->person->last_name,
            'email' => $this->person->user->email,
            'phone_number' => $this->person->phone_number,
            'role_id' => $this->person->user->role_id,
            'address' => $this->person->address,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
