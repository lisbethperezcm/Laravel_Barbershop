<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class EstatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
    
        DB::table('estatus')->insert([
            ['name' => 'activo'],
            ['name' => 'inactivo'],
            ['name' => 'reservado'],
            ['name' => 'pendiente'],
            ['name' => 'en proceso'],
            ['name' => 'cancelado'],
            ['name' => 'completado'],
            ['name' => 'pagado'],
            ['name' => 'entregado'],
            ['name' => 'devuelto'],
            ['name' => 'descontinuado'],
        ]);
    }
}
