<?php

namespace App\Services;

use App\Models\InventoryEntry;
use Illuminate\Support\Facades\DB;
use App\Traits\HandlesInventoryLines;

class InventoryEntryService
{
    use HandlesInventoryLines;

    /**
     * Crear una ENTRADA de inventario con detalles de productos.
     */
    public function createInventoryEntry(array $data): InventoryEntry
    {
        
            // 1) Crear cabecera sin total
            $inventoryEntry = InventoryEntry::create([
                'entry_type' => $data['entry_type'] ?? 'Compra',
                'entry_date' => $data['entry_date'],
                'invoice_number' => $data['invoice_number'] ?? null,
                'note'       => $data['note'] ?? null,
                'total'      => 0, // se recalcula luego
            ]);

            // 2) Sincronizar detalles (suma stock: +1)
            $this->processProductDetails(
                movement:        $inventoryEntry,
                productLines:    $data['products'] ?? [],
                detailsRelation: 'entryDetails',
                stockDirection:  +1, // suma stock
                getUnitCost:     null // usa unit_cost del payload o del producto si no viene
            );

            // 3) Recalcular total desde la BD
            $total = $this->calculateDocumentTotal($inventoryEntry, 'entryDetails');
            $inventoryEntry->update(['total' => $total]);

            return $inventoryEntry;
    
    }

    /**
     * Actualizar una ENTRADA de inventario con detalles de productos.
     */
    public function updateInventoryEntry(InventoryEntry $inventoryEntry, array $data): InventoryEntry
    {
        

            // 1) Actualizar cabecera (excepto total)
            $inventoryEntry->update([
                'entry_type' => $data['entry_type'] ?? $inventoryEntry->entry_type,
                'entry_date' => $data['entry_date'] ?? $inventoryEntry->entry_date,
                'note'       => $data['note']       ?? $inventoryEntry->note,
                'invoice_number' => $data['invoice_number'] ?? $inventoryEntry->invoice_number,
            ]);

            if(!isset($data['products'])) {
                // No se actualizan los productos
                return $inventoryEntry->load('entryDetails.product');
            }
            // 2) Sincronizar detalles (suma stock: +1)
            $this->processProductDetails(
                movement:        $inventoryEntry,
                productLines:    $data['products'] ?? [],
                detailsRelation: 'entryDetails',
                stockDirection:  +1,
                getUnitCost:     null // usa unit_cost del payload o del producto si no viene
            );

            // 3) Recalcular total desde la BD
            $total = $this->calculateDocumentTotal($inventoryEntry, 'entryDetails');
            $inventoryEntry->update(['total' => $total]);

            return $inventoryEntry;
        
    }
}
