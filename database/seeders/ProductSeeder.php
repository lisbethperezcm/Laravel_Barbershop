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
    {DB::table('products')->insert([
        [
            'name' => 'Shampoo Anticaspa',
            'description' => 'Shampoo eficaz para el tratamiento de la caspa y el cuidado del cuero cabelludo.',
            'sale_price' => 450.00,
            'unit_cost' => 300.00,
            'stock' => 100,
            'itbis' => 18.00,
            'status_id' => 1,
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'name' => 'Afeitadora Eléctrica',
            'description' => 'Afeitadora eléctrica con 3 cabezales flexibles para un afeitado cómodo.',
            'sale_price' => 950.00,
            'unit_cost' => 600.00,
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
            'sale_price' => 320.00,
            'unit_cost' => 180.00,
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
            'sale_price' => 350.00,
            'unit_cost' => 200.00,
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
            'sale_price' => 700.00,
            'unit_cost' => 400.00,
            'stock' => 70,
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
            'sale_price' => 1200.00,
            'unit_cost' => 800.00,
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
            'sale_price' => 380.00,
            'unit_cost' => 250.00,
            'stock' => 120,
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
            'sale_price' => 340.00,
            'unit_cost' => 200.00,
            'stock' => 80,
            'itbis' => 18.00,
            'status_id' => 1,
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);
    
    }
}
