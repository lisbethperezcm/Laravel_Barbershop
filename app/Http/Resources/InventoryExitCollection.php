<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Helpers\GeneralHelper;

class InventoryExitCollection extends ResourceCollection
{
    /**
     * Transforma la colección de InventoryExit a arreglo.
     *
     * Espera que cada modelo tenga cargado:
     * - exitDetails.product
     * - createdBy.person (opcional)
     * - updatedBy.person (opcional)
     */
    public function toArray(Request $request)
    {
        return $this->collection->map(function ($exit) {
            return [
                'id'         => $exit->id,
                'exit_type'  => $exit->exit_type,
                'exit_date'  => $exit->exit_date,
                'note'       => $exit->note,

                'products'   => $exit->exitDetails->map(fn($detail) => [
                    'product_id'   => $detail->product_id,
                    'product_name' => $detail->product?->name,
                    'quantity'     => (int) $detail->quantity,
                    'unit_cost'    => GeneralHelper::getFloat($detail->unit_cost),
                    'subtotal'     => GeneralHelper::getFloat($detail->unit_cost * $detail->quantity),
                ]),

                'total'      => GeneralHelper::getFloat($exit->total ?? 0),

                'created_at' => $exit->created_at,
                'created_by' => ($exit->createdBy?->person)
                    ? $exit->createdBy->person->first_name . ' ' . $exit->createdBy->person->last_name
                    : null,

                'updated_at' => $exit->updated_at,
                'updated_by' => ($exit->updatedBy?->person ?? null)
                    ? $exit->updatedBy->person->first_name . ' ' . $exit->updatedBy->person->last_name
                    : null,
              ];
    });
}
}
