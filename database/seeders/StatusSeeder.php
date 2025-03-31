<?php

namespace Database\Seeders;

use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
    
        DB::table('statuses')->insert([
            ['name' => 'Activo','created_at' => Carbon::now()],
            ['name' => 'Inactivo','created_at' => Carbon::now()],
            ['name' => 'Reservado','created_at' => Carbon::now()],
            ['name' => 'Pendiente','created_at' => Carbon::now()],
            ['name' => 'En proceso','created_at' => Carbon::now()],
            ['name' => 'Cancelado','created_at' => Carbon::now()],
            ['name' => 'Completado','created_at' => Carbon::now()],
            ['name' => 'Pagado','created_at' => Carbon::now()],
            ['name' => 'Entregado','created_at' => Carbon::now()],
            ['name' => 'Devuelto','created_at' => Carbon::now()],
            ['name' => 'Descontinuado','created_at' => Carbon::now()]
        ]);
    }
}
