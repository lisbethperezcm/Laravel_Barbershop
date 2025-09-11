<?php

namespace App\Traits;

use App\Models\Product;

trait HandlesInventoryLines
{
    /**
     * Procesa líneas de productos (crear/editar/eliminar) y ajusta stock.
     *
     * @param  mixed         $movement         Cabecera: Factura/Entrada/Salida (modelo Eloquent)
     * @param  array         $productLines     Líneas del payload (product_id, quantity, unit_cost?)
     * @param  string        $detailsRelation  Relación en la cabecera ('invoiceDetails', 'entryDetails', 'exitDetails')
     * @param  int           $stockDirection   -1 = resta stock (ventas/salidas), +1 = suma stock (compras/devoluciones)
     * @param  callable|null $getUnitCost      fn(Product $product, array $line): string|float (opcional)
     * @param  string        $priceColumn      Nombre de la columna de precio (opcional)
     */
    protected function processProductDetails(
        $movement,
        array $productLines,
        string $detailsRelation,
        int $stockDirection,
        ?callable $getUnitCost = null,
        string $priceColumn = 'unit_cost'
    ): void {
        // Indexar existentes: product_id => detail_id
        $byProduct = $movement->{$detailsRelation}()
            ->pluck('id', 'product_id')
            ->toArray();

        $seenProductIds = [];

        // CREAR / ACTUALIZAR según product_id
        foreach ($productLines as $row) {
            $productId = $row['product_id'] ?? null;
            if (!$productId) {
                // si no viene product_id, ignoramos la línea
                continue;
            }

            $seenProductIds[] = (int) $productId;

            if (isset($byProduct[$productId])) {
                // UPDATE por product_id (mapea al detail_id existente)
                $detailId = (int) $byProduct[$productId];

                $this->_updateDetail(
                    $movement,
                    $detailsRelation,
                    $detailId,
                    $row,
                    $stockDirection,
                    $getUnitCost,
                    $priceColumn
                );
            } else {
                // CREATE
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

        // ELIMINAR: cualquier detalle cuyo product_id no vino en el payload
        if (!empty($byProduct)) {
            $existingProductIds = array_map('intval', array_keys($byProduct));
            $seenProductIds     = array_values(array_unique(array_map('intval', $seenProductIds)));

            $productsToDelete = array_diff($existingProductIds, $seenProductIds);

            foreach ($productsToDelete as $prodId) {
                $detailId = (int) $byProduct[$prodId];
                $this->_deleteDetail($movement, $detailsRelation, $detailId, $stockDirection);
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
                return bcadd($acc, bcmul((string) $d->unit_cost, (string) $d->quantity, 2), 2);
            }, "0.00");
        }

        return round(
            $details->reduce(fn ($acc, $d) => $acc + ((float) $d->unit_cost * (int) $d->quantity), 0.0),
            2
        );
    }

    /**
     * Elimina el movimiento y revierte el stock de sus detalles (soft delete si aplica).
     */
    protected function softDeleteMovementAndRevertStock($movement, string $detailsRelation, int $stockDirection): void
    {
        $movement->{$detailsRelation}->each(function ($detail) use ($stockDirection) {
            // Revertir:
            // Entrada (+1) → revertir = -cantidad
            // Salida/Factura (-1) → revertir = +cantidad
            $this->_applyStock($detail->product, $detail->quantity * ($stockDirection * -1));
            $detail->delete();
        });

        $movement->delete();
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
        $payload = [
            'product_id' => $product->id,
            'quantity'   => (int) ($row['quantity'] ?? 0),
            'unit_cost'  => $unitCost,
        ];
        if ($priceColumn !== 'unit_cost') {
            $payload[$priceColumn] = $unitCost;
        }

        $movement->{$detailsRelation}()->create($payload);

        // Ajustar stock
        $this->_applyStock($product, (int) ($row['quantity'] ?? 0) * $stockDirection);
    }

    /**
     * Helper: actualizar detalle (revierte stock previo, actualiza qty/costo y reaplica stock).
     * Mantiene exactamente tu comportamiento original (NO cambia product_id).
     */
    protected function _updateDetail(
        $movement,
        string $detailsRelation,
        int $detailId,
        array $row,
        int $stockDirection,
        ?callable $getUnitCost = null,
        string $priceColumn = 'unit_cost'
    ): void {
        $detail = $movement->{$detailsRelation}()->find($detailId);
        if (!$detail) {
            return;
        }

        // 1) Revertir stock previo de la línea
        $this->_applyStock($detail->product, $detail->quantity * ($stockDirection * -1));

        // 2) Resolver unit_cost (con el product del detalle, tal cual tu lógica)
        $unitCost = $this->_resolveUnitCost($detail->product, $row, $detail, $getUnitCost);

        // 3) Actualizar línea (quantity / costos)
        $update = [
            'quantity'  => (int) ($row['quantity'] ?? 0),
            'unit_cost' => $unitCost,
        ];
        if ($priceColumn !== 'unit_cost') {
            $update[$priceColumn] = $unitCost;
        }

        $detail->update($update);

        // 4) Aplicar stock con el nuevo valor
        $this->_applyStock($detail->product, (int) ($row['quantity'] ?? 0) * $stockDirection);
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
            ? number_format((float) $unit, 2, '.', '')
            : (float) $unit;
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
