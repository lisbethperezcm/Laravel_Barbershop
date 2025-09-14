<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Helpers\GeneralHelper;

class BarberReviewCollection extends ResourceCollection
{
    /**
     * Transforma la colección de BarberReview a arreglo.
     *
     * Espera que cada modelo tenga cargado:
     * - client.person
     * - barber.person
     * - appointment (opcional)
     * - createdBy.person (opcional)
     * - updatedBy.person (opcional)
     */
    public function toArray(Request $request)
    {
        return $this->collection->map(function ($review) {
            return [
                'id'            => $review->id,
                'rating'        => (int) $review->rating,
                'comment'       => $review->comment,

                // Cliente
                'client' => $review->client?->person
                    ? $review->client->person->first_name . ' ' . $review->client->person->last_name
                    : null,

                // Barbero
                'barber' => $review->barber?->person
                    ? $review->barber->person->first_name . ' ' . $review->barber->person->last_name
                    : null,

                // Cita relacionada
                'appointment_id' => $review->appointment_id,

                // Auditoría
                'created_at' => $review->created_at,
                'created_by' => ($review->createdBy?->person)
                    ? $review->createdBy->person->first_name . ' ' . $review->createdBy->person->last_name
                    : null,

                'updated_at' => $review->updated_at,
                'updated_by' => ($review->updatedBy?->person ?? null)
                    ? $review->updatedBy->person->first_name . ' ' . $review->updatedBy->person->last_name
                    : null,
            ];
        });
    }
}
