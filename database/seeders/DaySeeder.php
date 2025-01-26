<?php

namespace Database\Seeders;

use App\Models\Day;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $days = [
            ['name' => 'Lunes'],
            ['name' => 'Martes'],
            ['name' => 'Miércoles'],
            ['name' => 'Jueves'],
            ['name' => 'Viernes'],
            ['name' => 'Sábado'],
            ['name' => 'Domingo']
        ];

        Day::insert($days);
    }
}
