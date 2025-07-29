<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */

public function index(Request $request)
{
    // Obtener el nombre del producto del request (si viene)
    $product_name = $request->input('name');

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
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        //
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
}
