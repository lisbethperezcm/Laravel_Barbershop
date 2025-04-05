<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ExitDetail;
use App\Models\InventoryExit;
use App\Models\BarberDispatch;
use Illuminate\Support\Facades\DB;

class InventoryExitService
{
    /**
     * Crear una salida de inventario con detalles de productos.
     */
    public function createInventoryExit(array $data)
    {
        DB::beginTransaction();
        try {
            // Calcular el total de los productos
            $total = $this->calculateTotal($data['products']);

            // crear la salida de inventario
            $inventoryExit = InventoryExit::create([
                'exit_type' => $data['exit_type'] ?? 'Despacho a Barbero',
                'exit_date' => $data['exit_date'],
                'note' => $data['note'] ?? null,
                'total' => $total,
            ]);

            // Guardar detalles de salida y actualizar stock
            $this->storeExitDetails($inventoryExit, $data['products']);

            DB::commit();
            return $inventoryExit;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    /**
     * actualizar una salida de inventario con detalles de productos.
     */
    public function updateInventoryExit(InventoryExit $inventoryExit, array $data)
    {


        DB::beginTransaction();
        try {

            // Obtener el usuario autenticado
            $userId = auth()->id();
            // Calcular el total con los nuevos productos

            $total = $this->calculateTotal($data['products']);

            // Asegurar que el total sea parte de los datos actualizados
            $data['total'] = $total;

            // Validar actualizacion de la fecha de la salida
            
            if (isset($data['dispatch_date'])) {
                $data['exit_date'] = $data['dispatch_date'];
            }
            // Actualizar la salida de inventario
            $inventoryExit->update($data);

            // Obtener el despacho asociado y actualizar su `updated_by`
            $barberDispatch = BarberDispatch::where('exit_id', $inventoryExit->id)->first();
            if ($barberDispatch) {
                $barberDispatch->update(['updated_by' => $userId, 'updated_at' => now()]);
            }

            // Obtener los productos actuales de la salida
            $existingProducts = $inventoryExit->exitDetails()->pluck('product_id', 'id')->toArray();
            $newProductIds = collect($data['products'])->pluck('product_id')->toArray();

            // Manejar los detalles de la salida y el stock
            foreach ($data['products'] as $product) {
                if (isset($product['id']) && isset($existingProducts[$product['id']])) {
                    // Producto ya existente, actualizarlo
                    $exitDetail = ExitDetail::find($product['id']);

                    // Restaurar stock antes de actualizar
                    $exitDetail->product->increment('stock', $exitDetail->quantity);

                    // Actualizar detalle de salida
                    $exitDetail->update([
                        'quantity' => $product['quantity'],
                        'unit_cost' => $exitDetail->product->unit_cost,
                    ]);

                    // Reducir stock con la nueva cantidad
                    $exitDetail->product->decrement('stock', $product['quantity']);
                } else {
                    // Nuevo producto, agregarlo
                    $productModel = Product::find($product['product_id']);
                    if ($productModel) {
                        ExitDetail::create([
                            'exit_id' => $inventoryExit->id,
                            'product_id' => $product['product_id'],
                            'quantity' => $product['quantity'],
                            'unit_cost' => $productModel->unit_cost,
                        ]);

                        // Reducir stock del producto
                        $productModel->decrement('stock', $product['quantity']);
                    }
                }
            }

            // Eliminar productos que ya no están en la solicitud
            $productsToDelete = array_diff(array_keys($existingProducts), $newProductIds);
            foreach ($productsToDelete as $productId) {
                $exitDetail = ExitDetail::find($productId);
                if ($exitDetail) {
                    // Restaurar stock antes de eliminar
                    $exitDetail->product->increment('stock', $exitDetail->quantity);
                    $exitDetail->delete();
                }
            }

            DB::commit();
            return $inventoryExit;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    /**
     * Almacenar los detalles de la salida y actualizar el stock
     */
    protected function storeExitDetails(InventoryExit $inventoryExit, array $products)
    {
        foreach ($products as $product) {
            $productModel = Product::find($product['product_id']);

            if ($productModel) { // Verificar si el producto existe antes de continuar
                ExitDetail::create([
                    'exit_id' => $inventoryExit->id,
                    'product_id' => $product['product_id'],
                    'quantity' => $product['quantity'],
                    'unit_cost' => $productModel->unit_cost,
                ]);

                // Reducir stock del producto
                $productModel->decrement('stock', $product['quantity']);
            }
        }
    }

    /**
     * Calcular el total de los productos
     */
    public function calculateTotal(array $products): float
    {
        $productsModel = Product::whereIn('id', collect($products)->pluck('product_id'))->get()->keyBy('id');

        $total = 0;

        foreach ($products as $product) {
            if (isset($productsModel[$product['product_id']])) {
                $total += $productsModel[$product['product_id']]->unit_cost * $product['quantity'];
            }
        }

        return $total;
    }
}
