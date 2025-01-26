<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ClientCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request)
    {
        return $this->collection->map(function ($client) {
            return [
                'id' => $client->id,
                // Accede a la informacion del nombre atraves de la relacion con persona
                'first_name' => $client->person->first_name,
                'last_name' => $client->person->last_name,
                // Accede a la informacion del nombre atraves de la relacion de persona con user
                'email' => $client->person->user->email,
                'phone_number' => $client->person->phone_number,
                'adress' => $client->person->address,
                'role_id' => $client->person->user->role_id,
                'created_at' => $client->created_at,
                'updated_at' => $client->updated_at,
            ];
        });
    }
}
