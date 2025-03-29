<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ExitDetail;
use Illuminate\Http\Request;
use App\Models\InventoryExit;
use Illuminate\Support\Arr;

use function Laravel\Prompts\error;

class InventoryExitsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index() {}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //Obtener el usuario autenticado 
        $user = auth()->user();

        $request->validate([
            'exit_type' => 'required|string',
            'exit_date' => 'required|date',
            'products' => 'required|array',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);


        $products = $request->input('products', []);
        $total = $this->getProductsSubtotal($products);


        // Crear la salida de inventario
        $inventoryExit = InventoryExit::create([
            'exit_type' => $request->exit_type,
            'exit_date' => $request->exit_date,
            'note' => $request->note ?? null,
            'total' => $total
        ]);

        // Crear detalles de salida y actualizar stock de productos

        $this->storeExitDetails($inventoryExit, $products);


        return response()->json([
            'message' => 'Salida de inventario creada exitosamente',
            'data' => $inventoryExit,
            'errorCode' => 201
        ], 201);
    }



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


    //Calcula el total de los productos
    protected function getProductsSubtotal(array $products): float
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
    /**
     * Display the specified resource.
     */
    public function show(InventoryExit $inventoryExit)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InventoryExit $inventoryExit)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InventoryExit $inventoryExit)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InventoryExit $inventoryExit)
    {
        //
    }
}
