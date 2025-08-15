<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Helpers\GeneralHelper;
class BarberDispatchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'barber_name' => $this->barber->person->first_name . ' ' . $this->barber->person->last_name,
            'dispatch_date' => $this->dispatch_date,
            'status' => $this->status->name ?? 'Sin estado',
            'products' => $this->inventoryExit->exitDetails->map(fn($detail) => [
                'product_name' => $detail->product->name,
                'quantity' => $detail->quantity,
                'unit_cost' => $detail->unit_cost,
                'subtotal' => GeneralHelper::getFloat($detail->unit_cost * $detail->quantity)  
            ]),
            'total' => $this->inventoryExit->total ?? 0, // 🔹 Obtiene el total de la salida de inventario
            'created_at' => $this->created_at,
            'created_by' => $this->createdBy->person->first_name . ' ' . $this->createdBy->person->last_name,
            'updated_at' => $this->updated_at,
        ];
    }
}
