<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Requests\ProductRequest;
use App\Http\Requests\EditProductRequest;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        // Obtener el nombre del producto del request (si viene)
        $product_name = $request->input('name') ? trim($request->name) : null;

        $productsQuery = Product::query();

        // Filtro por nombre si se envía en la petición
        if ($product_name) {
            $productsQuery->nameLike($product_name);
        }

        $products = $productsQuery->get();

        return response()->json([
            'data' => $products,
            'errorCode' => '200'
        ], 200);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductRequest $request)
    {

        $product = Product::create($request->all());

        return response()->json([
            'message' => 'Producto creado exitosamente.',
            'data' => $product,
            'errorCode' => '201'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EditProductRequest $request, Product $product)
    {


        $product = Product::findOrFail($product->id);
        // Validar datos y excluir stock
        $validated = $request->safe()->except(['stock']);

        if ($request->has('stock')) {
            return response()->json([
                'message' => 'El stock no es posible actualizar en esta funcionalidad. Usa entradas/salidas/devoluciones.',
                'errorCode' => 422
            ], 422);
        }

        // Update directo
        $product->update($validated);

        return response()->json([
            'message' => 'Producto actualizado exitosamente.',
            'data' => $product->fresh(),
            'errorCode' => 200
        ], 200);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete(); // Soft Delete: solo marca deleted_at

        return response()->json([
            'message' => 'Producto eliminado correctamente',
            'errorCode' => 200
        ]);
    }
    /** */

    /**
     * Obtener productos con bajo stock.
     */
    public function getLowStockProducts(Request $request)
    {
        // Umbral configurable (por defecto 5)
        $threshold = $request->input('threshold', 5);

        $products = Product::where('stock', '<=', $threshold)
            ->orderBy('stock', 'asc')
            ->select('id', 'name', 'stock')
            ->get();

        return response()->json([
            'data' => $products,
            'errorCode' => '200'
        ], 200);
    }
}
