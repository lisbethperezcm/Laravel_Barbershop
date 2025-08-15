<?php

namespace App\Traits;

use App\Models\Product;
use PhpParser\Node\Expr\Cast\String_;

trait HandlesInventoryLines
{
    /**
     * Procesa líneas de productos (crear/editar/eliminar) y ajusta stock.
     *
     * @param  mixed         $movement         Cabecera: Factura/Entrada/Salida (modelo Eloquent)
     * @param  array         $productLines     Líneas del payload (id?, product_id, quantity, unit_cost?)
     * @param  string        $detailsRelation  Relación en la cabecera ('invoiceDetails', 'entryDetails', 'exitDetails')
     * @param  int           $stockDirection   -1 = resta stock (ventas/salidas), +1 = suma stock (compras/devoluciones)
     * @param  callable|null $getUnitCost      fn(Product $product, array $line): string|float (opcional)
     * @param  string       $priceColumn      Nombre de la columna de precio (opcional)
     */
    protected function processProductDetails(
        $movement,
        array $productLines,
        string $detailsRelation,
        int $stockDirection,
        ?callable $getUnitCost = null,
        string $priceColumn = 'unit_cost'
    ): void {
        // Mapa actual en BD: [detalle_id => product_id]
        $existingDetails = $movement->{$detailsRelation}()
            ->pluck('product_id', 'id')
            ->toArray();

        // CREAR / ACTUALIZAR
        foreach ($productLines as $row) {
            $detailId = $row['id'] ?? null;

            if ($detailId && isset($existingDetails[$detailId])) {
                // Actualizar detalle existente (misma lógica que ya tenías)
                $this->_updateDetail(
                    $movement,
                    $detailsRelation,
                    (int)$detailId,
                    $row,
                    $stockDirection,
                    $getUnitCost,
                    $priceColumn
                );
            } else {
                // Crear nuevo detalle
                $this->_createDetail(
                    $movement,
                    $detailsRelation,
                    $row,
                    $stockDirection,
                    $getUnitCost,
                    $priceColumn
                );
            }
        }

        // ELIMINAR detalles que ya no vienen
        $incomingDetailIds = collect($productLines)->pluck('id')->filter()->toArray();

        if (!empty($incomingDetailIds)) {
            // Comparar por detalle_id
            $detailsToDelete = array_diff(array_keys($existingDetails), $incomingDetailIds);
            foreach ($detailsToDelete as $detailId) {
                $this->_deleteDetail($movement, $detailsRelation, (int)$detailId, $stockDirection);
            }
        } else {
            // Fallback: comparar por product_id si no envían ids de detalle
            $incomingProductIds = collect($productLines)->pluck('product_id')->toArray();
            $productsToDelete = array_diff(array_values($existingDetails), $incomingProductIds);

            if (!empty($productsToDelete)) {
                foreach ($existingDetails as $detailId => $prodId) {
                    if (!in_array($prodId, $productsToDelete, true)) {
                        continue;
                    }
                    $this->_deleteDetail($movement, $detailsRelation, (int)$detailId, $stockDirection);
                }
            }
        }
    }

    /**
     * Calcula el total sumando (unit_cost * quantity) desde los detalles actuales.
     * Devuelve string si hay BCMath; si no, float redondeado.
     */
    protected function calculateDocumentTotal($movement, string $detailsRelation): string|float
    {
        $details = $movement->{$detailsRelation}()->get(['quantity', 'unit_cost']);

        if (function_exists('bcmul') && function_exists('bcadd')) {
            return $details->reduce(function ($acc, $d) {
                return bcadd($acc, bcmul((string)$d->unit_cost, (string)$d->quantity, 2), 2);
            }, "0.00");
        }

        return round(
            $details->reduce(fn($acc, $d) => $acc + ((float)$d->unit_cost * (int)$d->quantity), 0.0),
            2
        );
    }


    /**
     * Elimina el movimiento y revierte el stock de sus detalles.
     * Usa soft delete si está habilitado en el modelo.
     */


    protected function softDeleteMovementAndRevertStock($movement, string $detailsRelation, int $stockDirection): void
    {
        // Solo detalles vigentes (no-trashed) por defecto
        $movement->{$detailsRelation}->each(function ($detail) use ($stockDirection) {
            // Revertir:
            // Entrada (+1) → revertir = -cantidad
            // Salida/Factura (-1) → revertir = +cantidad
            $this->_applyStock($detail->product, $detail->quantity * ($stockDirection * -1));
            $detail->delete(); // Soft delete
        });

        $movement->delete(); // Soft delete
    }



    // ================== HELPERS ==================

    /**
     * Helper: crear detalle y ajustar stock
     */
    protected function _createDetail(
        $movement,
        string $detailsRelation,
        array $row,
        int $stockDirection,
        ?callable $getUnitCost = null,
        string $priceColumn = 'unit_cost'
    ): void {
        $product = $this->_findProductOrNull($row['product_id'] ?? $row['id'] ?? null);
        if (!$product) {
            return;
        }

        $unitCost = $this->_resolveUnitCost($product, $row, null, $getUnitCost);
        
        // Crear vía relación (la FK se asigna según la relación definida en el modelo)
        $movement->{$detailsRelation}()->create([
            'product_id' => $product->id,
            'quantity'   => (int)($row['quantity'] ?? 0),
            'unit_cost'  => $unitCost,
            $priceColumn => $unitCost,
        ]);

        // Ajustar stock
        $this->_applyStock($product, (int)($row['quantity'] ?? 0) * $stockDirection);
    }

    /**
     * Helper: actualizar detalle (revierte stock previo, actualiza qty/costo y reaplica stock).
     * Mantiene exactamente tu comportamiento actual (NO cambia product_id).
     */
    protected function _updateDetail(
        $movement,
        string $detailsRelation,
        int $detailId,
        array $row,
        int $stockDirection,
        ?callable $getUnitCost = null,
        String $priceColumn = 'unit_cost'
    ): void {
        $detail = $movement->{$detailsRelation}()->find($detailId);
        if (!$detail) {
            return;
        }

        // 1) Revertir stock previo de la línea
        $this->_applyStock($detail->product, $detail->quantity * ($stockDirection * -1));

        // 2) Resolver unit_cost (con el product del detalle, tal cual tu lógica)
        $unitCost = $this->_resolveUnitCost($detail->product, $row, $detail, $getUnitCost);

        // 3) Actualizar línea (solo quantity y unit_cost)
        $detail->update([
            'quantity'  => (int)($row['quantity'] ?? 0),
            'unit_cost' => $unitCost,
            $priceColumn => $unitCost,
        ]);

        // 4) Aplicar stock con el nuevo valor
        $this->_applyStock($detail->product, (int)($row['quantity'] ?? 0) * $stockDirection);
    }

    /**
     * Helper: eliminar detalle y revertir stock.
     */
    protected function _deleteDetail(
        $movement,
        string $detailsRelation,
        int $detailId,
        int $stockDirection
    ): void {
        $detail = $movement->{$detailsRelation}()->find($detailId);
        if (!$detail) {
            return;
        }

        $this->_applyStock($detail->product, $detail->quantity * ($stockDirection * -1));
        $detail->delete();
    }

    /**
     * Helper: aplicar delta al stock.
     */
    protected function _applyStock(Product $product, int $delta): void
    {
        if ($delta === 0) {
            return;
        }
        $product->increment('stock', $delta);
    }

    /**
     * Helper: resolver unit_cost (callable > row['unit_cost'] > product->unit_cost).
     */
    protected function _resolveUnitCost(
        Product $product,
        array $row,
        $existingDetail = null,
        ?callable $getUnitCost = null
    ): string|float {
        $unit = $getUnitCost
            ? $getUnitCost($product, $row)
            : ($row['unit_cost'] ?? $product->unit_cost);

        // Normaliza a 2 decimales si hay BCMath; si no, float.
        return function_exists('bcadd')
            ? number_format((float)$unit, 2, '.', '')
            : (float)$unit;
    }

    /**
     * Helper: buscar producto o null.
     */
    protected function _findProductOrNull($productId): ?Product
    {
        if (!$productId) {
            return null;
        }
        return Product::find($productId);
    }
}
