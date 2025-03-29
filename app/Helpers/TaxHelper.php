<?php

use App\Models\Product;

if (!function_exists('calculateProductsTax')) {
    /**
     * Obtener el ITBIS basado en los productos (los servicios no llevan ITBIS).
     *
     * @param array|null $products Lista de productos con sus cantidades.
     * @return float Total del ITBIS calculado.
     */
    function calculateProductsTax(?array $products): float
    {
        if (empty($products)) {
            return 0;
        }

        // Cargar todos los productos en una sola consulta
        $productsDB = Product::whereIn('id', collect($products)->pluck('product_id'))->get()->keyBy('id');

        // Calcular el ITBIS sumando (precio * cantidad * itbis)
        return collect($products)->sum(fn($product) => 
            ($productsDB[$product['product_id']]->sale_price * 
             $product['quantity'] * 
             ($productsDB[$product['id']]->itbis / 100) ?? 0) 
        );
    }
}
/*
protected function getProductsTaxAmount(?array $products): float
{

    if (empty($products)) {
        return 0;
    }

// Cargar todos los productos en una sola consulta
$productsDB = Product::whereIn('id', collect($products)->pluck('id'))->get()->keyBy('id');

// Calcular el ITBIS sumando (itbis * cantidad) de cada producto
return collect($products)->sum(fn($product) => 
($productsDB[$product['id']]->sale_price * $product['quantity'] * $productsDB[$product['id']]->itbis ?? 0) 
);*/