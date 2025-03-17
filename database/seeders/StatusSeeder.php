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
            ['name' => 'activo','created_at' => Carbon::now()],
            ['name' => 'inactivo','created_at' => Carbon::now()],
            ['name' => 'reservado','created_at' => Carbon::now()],
            ['name' => 'pendiente','created_at' => Carbon::now()],
            ['name' => 'en proceso','created_at' => Carbon::now()],
            ['name' => 'cancelado','created_at' => Carbon::now()],
            ['name' => 'completado','created_at' => Carbon::now()],
            ['name' => 'pagado','created_at' => Carbon::now()],
            ['name' => 'entregado','created_at' => Carbon::now()],
            ['name' => 'devuelto','created_at' => Carbon::now()],
            ['name' => 'descontinuado','created_at' => Carbon::now()]
        ]);
    }
}
