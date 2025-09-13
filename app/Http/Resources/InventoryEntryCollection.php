<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Helpers\GeneralHelper;

class InventoryEntryCollection extends ResourceCollection
{
    /**
     * Transforma la colección de InventoryEntry a arreglo.
     *
     * Espera que cada modelo tenga cargado:
     * - entryDetails.product
     * - createdBy.person (opcional)
     * - updatedBy.person (opcional)
     */
    public function toArray(Request $request)
    {
        return $this->collection->map(function ($entry) {
            return [
                'id'         => $entry->id,
                'entry_type'  => $entry->entry_type,
                'entry_date'  => $entry->entry_date,
                'invoice_number'  => $entry->invoice_number,
                'note'       => $entry->note,

                'products'   => $entry->entryDetails->map(fn($detail) => [
                    'product_id'   => $detail->product_id,
                    'product_name' => $detail->product?->name,
                    'quantity'     => (int) $detail->quantity,
                    'unit_cost'    => GeneralHelper::getFloat($detail->unit_cost),
                    'subtotal'     => GeneralHelper::getFloat($detail->unit_cost * $detail->quantity),
                ]),

                'total'      => GeneralHelper::getFloat($entry->total ?? 0),

                'created_at' => $entry->created_at,
                'created_by' => ($entry->createdBy?->person)
                    ? $entry->createdBy->person->first_name . ' ' . $entry->createdBy->person->last_name
                    : null,

                'updated_at' => $entry->updated_at,
                'updated_by' => ($entry->updatedBy?->person ?? null)
                    ? $entry->updatedBy->person->first_name . ' ' . $entry->updatedBy->person->last_name
                    : null,
              ];
    });
}
}
