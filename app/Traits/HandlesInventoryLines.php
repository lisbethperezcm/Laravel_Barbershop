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
     * @param  string        $priceColumn      Nombre de la columna de precio (por defecto 'unit_cost')
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
                continue; // línea inválida sin product_id
            }

            $seenProductIds[] = (int) $productId;

            if (isset($byProduct[$productId])) {
                // UPDATE por product_id
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

        // ELIMINAR detalles cuyo product_id no vino en el payload
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
     * NOTA: Para revertir una salida (stockDirection = -1), aquí se suma stock.
     *       Para revertir una entrada (stockDirection = +1), aquí se resta stock.
     */
    protected function softDeleteMovementAndRevertStock($movement, string $detailsRelation, int $stockDirection): void
    {
        $movement->{$detailsRelation}->each(function ($detail) use ($stockDirection) {
            $this->_applyStock($detail->product, $detail->quantity * ($stockDirection * -1));
            $detail->delete();
        });

        $movement->delete();
    }

    // ================== HELPERS ==================

    /**
     * Crear detalle y ajustar stock.
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

        // Resolver costo: payload > callable > producto
        $unitCost = $this->_resolveUnitCost($product, $row, null, $getUnitCost, $priceColumn);

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
     * Actualizar detalle (revierte stock previo, actualiza qty/costo y reaplica stock).
     * Mantiene el costo guardado en el detalle si no se envía uno nuevo en el payload.
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

        // 2) Resolver costo con prioridad: payload > callable > costo guardado en el detalle > producto
        $unitCost = $this->_resolveUnitCost($detail->product, $row, $detail, $getUnitCost, $priceColumn);

        // 3) Actualizar línea
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
     * Eliminar detalle y revertir stock.
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
     * Aplicar delta al stock.
     */
    protected function _applyStock(Product $product, int $delta): void
    {
        if ($delta === 0) {
            return;
        }
        $product->increment('stock', $delta);
    }

    /**
     * Resolver unit_cost de forma simple y eficiente:
     * 1) Si hay callable, lo usamos.
     * 2) Si viene en el payload (priceColumn o unit_cost), lo usamos.
     * 3) Si existe detalle previo, usamos su costo almacenado (mantiene el costo histórico).
     * 4) En último caso, usamos el unit_cost actual del producto.
     */
    protected function _resolveUnitCost(
        Product $product,
        array $row,
        $existingDetail = null,
        ?callable $getUnitCost = null,
        string $priceColumn = 'unit_cost'
    ): string|float {
        // 1) callable
        if ($getUnitCost) {
            $unit = $getUnitCost($product, $row);
        } else {
            // 2) payload
            $unit = $row[$priceColumn] ?? $row['unit_cost'] ?? null;

            if ($unit === null) {
                // 3) costo guardado en el detalle
                if ($existingDetail) {
                    $unit = $existingDetail->{$priceColumn} ?? $existingDetail->unit_cost ?? null;
                }
            }

            // 4) fallback: costo del producto
            if ($unit === null) {
                $unit = $product->{$priceColumn} ?? $product->unit_cost;
            }
        }

        // Normaliza a 2 decimales si hay BCMath; si no, float.
        return function_exists('bcadd')
            ? number_format((float) $unit, 2, '.', '')
            : (float) $unit;
    }

    /**
     * Buscar producto o null.
     */
    protected function _findProductOrNull($productId): ?Product
    {
        if (!$productId) {
            return null;
        }
        return Product::find($productId);
    }
}
