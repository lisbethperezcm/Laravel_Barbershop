<?php

namespace Database\Seeders;

use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       
        $defaultServices = [
            ['name' => 'Corte de cabello', 'current_price' => 500, 'previous_price' => null, 'duration' => 40, 'updated_by' => null],
            ['name' => 'Afeitado', 'current_price' => 350, 'previous_price' => null, 'duration' => 45, 'updated_by' => null],
            ['name' => 'Masaje', 'current_price' => 400, 'previous_price' => null, 'duration' => 20, 'updated_by' => null],
            ['name' => 'Corte de cabello y afeitado', 'current_price' => 800, 'previous_price' => null, 'duration' => 45, 'updated_by' => null],
            ['name' => 'Masaje relajante', 'current_price' => 600, 'previous_price' => null, 'duration' => 25, 'updated_by' => null],
        ];
        
        // Inserción directa utilizando DB::table() y sin involucrar el modelo Eloquent
        foreach ($defaultServices as $service) {
            DB::table('services')->insert(array_merge($service, [
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]));
        }
    }

}
