<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Helpers\GeneralHelper;
use Illuminate\Http\Resources\Json\ResourceCollection;

class BarberDispatchCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request)
    {
        return $this->collection->map(function ($dispatch) {
       
            return [
                'id' => $dispatch->id,
                'barber_name' => $dispatch->barber->person->first_name . ' ' . $dispatch->barber->person->last_name,
                'dispatch_date' => $dispatch->dispatch_date,
                'status' => $dispatch->status->name ?? 'Sin estado',
                'products' => $dispatch->inventoryExit->exitDetails->map(fn($detail) => [
                    'product_name' => $detail->product->name,
                    'quantity' => $detail->quantity,
                    'unit_cost' => $detail->unit_cost,
                    'subtotal' => GeneralHelper::getFloat($detail->unit_cost * $detail->quantity)  
                ]),
                'total' => $dispatch->inventoryExit->total ?? 0, // 🔹 Obtiene el total de la salida de inventario
                'created_at' =>$dispatch->created_at,
                'created_by' => $dispatch->createdBy->person->first_name . ' ' . $dispatch->createdBy->person->last_name,
                'updated_at' => $dispatch->updated_at,
            ];
    });
}
}