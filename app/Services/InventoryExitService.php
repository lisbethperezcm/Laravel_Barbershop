<?php

namespace App\Services;

use App\Models\InventoryExit;
use App\Models\BarberDispatch;
use Illuminate\Support\Facades\DB;
use App\Traits\HandlesInventoryLines;

class InventoryExitService
{
    use HandlesInventoryLines;

    /**
     * Crear una salida de inventario con detalles de productos.
     */
    public function createInventoryExit(array $data) : InventoryExit
    {

        //Crear cabecera sin total
        $inventoryExit = InventoryExit::create([
            'exit_type' => $data['exit_type'] ?? 'Despacho a Barbero',
            'exit_date' => $data['exit_date'],
            'note'      => $data['note'] ?? null,
            'total'     => 0, // se recalcula luego
        ]);

        // Sincronizar detalles (resta stock: -1)
        $this->processProductDetails(
            movement: $inventoryExit,
            productLines: $data['products'] ?? [],
            detailsRelation: 'exitDetails',
            stockDirection: -1, // resta stock
            getUnitCost: null // usa el unit_cost de la línea o del producto si no viene
        );

        //Recalcular total desde la BD
        $total = $this->calculateDocumentTotal($inventoryExit, 'exitDetails');
        $inventoryExit->update(['total' => $total]);

        return $inventoryExit;
    }

    /**
     * Actualizar una salida de inventario con detalles de productos.
     */
    public function updateInventoryExit(InventoryExit $inventoryExit, array $data) : InventoryExit
    {

        // Obtener el usuario autenticado (para el despacho asociado)
        $userId = auth()->id();

        // Si te llegan cambios de fecha con 'dispatch_date', mapea a exit_date
        if (isset($data['dispatch_date'])) {
            $data['exit_date'] = $data['dispatch_date'];
        }

         if(!isset($data['products'])) {
                // No se actualizan los productos
                return $inventoryExit->load('exitDetails.product');
            }

        //Actualizar cabecera (sin tocar total aún)
        $inventoryExit->update([
            'exit_type' => $data['exit_type'] ?? $inventoryExit->exit_type,
            'exit_date' => $data['exit_date'] ?? $inventoryExit->exit_date,
            'note'      => $data['note']      ?? $inventoryExit->note,
        ]);

        //Sincronizar detalles con el trait (resta stock: -1)
        $this->processProductDetails(
            movement: $inventoryExit,
            productLines: $data['products'] ?? [],
            detailsRelation: 'exitDetails',
            stockDirection: -1,
            getUnitCost: null // o pasa un resolver para forzar el unit_cost del producto
        );

        //Recalcular total desde la BD
        $total = $this->calculateDocumentTotal($inventoryExit, 'exitDetails');
        $inventoryExit->update(['total' => $total]);

        // Actualizar despacho asociado (si existe)
        $barberDispatch = BarberDispatch::where('exit_id', $inventoryExit->id)->first();
        if ($barberDispatch) {
            $barberDispatch->update(['updated_by' => $userId, 'updated_at' => now()]);
        }

        return $inventoryExit;
    }

    /**
     * Elimina una salida de inventario.
     */
    public function deleteInventoryExit($inventoryExit): InventoryExit
    {
        // Reutiliza el método del trait para revertir y borrar
        $this->softDeleteMovementAndRevertStock(
            $inventoryExit,   // La salida a eliminar
            'exitDetails',    // Relación en el modelo
            -1                // Salidas suma el stock al revertir
        );
        return $inventoryExit->fresh();
    }
}
