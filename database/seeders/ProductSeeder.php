<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('products')->insert([
            [
                'name' => 'Shampoo Anticaspa',
                'description' => 'Shampoo eficaz para el tratamiento de la caspa y el cuidado del cuero cabelludo.',
                'sale_price' => 10.50,
                'unit_cost' => 6.00,
                'stock' => 100,
                'itbis' => 18.00,
                'status_id' => 1,  // Asumiendo que 1 es 'activo'
                'created_by' => 1,  // Asumiendo que el usuario con id 1 es el que crea
                'updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Afeitadora Eléctrica',
                'description' => 'Afeitadora eléctrica con 3 cabezales flexibles para un afeitado cómodo.',
                'sale_price' => 25.00,
                'unit_cost' => 15.00,
                'stock' => 50,
                'itbis' => 18.00,
                'status_id' => 1,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Gel para Cabello',
                'description' => 'Gel fijador para un peinado perfecto todo el día.',
                'sale_price' => 5.00,
                'unit_cost' => 2.50,
                'stock' => 200,
                'itbis' => 18.00,
                'status_id' => 1,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Crema de Afeitar',
                'description' => 'Crema suave para un afeitado sin irritaciones.',
                'sale_price' => 7.00,
                'unit_cost' => 3.50,
                'stock' => 150,
                'itbis' => 18.00,
                'status_id' => 1,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Tijeras Profesionales',
                'description' => 'Tijeras de alta calidad para cortes de cabello precisos.',
                'sale_price' => 18.00,
                'unit_cost' => 10.00,
                'stock' => 70,
                'itbis' => 18.00,
                'status_id' => 1,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Peine para Cabello',
                'description' => 'Peine de plástico resistente para todo tipo de cabello.',
                'sale_price' => 2.00,
                'unit_cost' => 1.00,
                'stock' => 500,
                'itbis' => 18.00,
                'status_id' => 1,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Máquina de Cortar Cabello',
                'description' => 'Máquina eléctrica para cortes de cabello profesionales.',
                'sale_price' => 30.00,
                'unit_cost' => 18.00,
                'stock' => 60,
                'itbis' => 18.00,
                'status_id' => 1,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pomada para Cabello',
                'description' => 'Pomada de fijación fuerte para mantener el peinado durante todo el día.',
                'sale_price' => 8.00,
                'unit_cost' => 4.00,
                'stock' => 120,
                'itbis' => 18.00,
                'status_id' => 1,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Espejo de Mano',
                'description' => 'Espejo compacto ideal para cortes y detalles.',
                'sale_price' => 3.00,
                'unit_cost' => 1.50,
                'stock' => 200,
                'itbis' => 18.00,
                'status_id' => 1,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Spray de Brillo',
                'description' => 'Spray para darle un brillo natural al cabello.',
                'sale_price' => 6.00,
                'unit_cost' => 3.00,
                'stock' => 80,
                'itbis' => 18.00,
                'status_id' => 1,
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
