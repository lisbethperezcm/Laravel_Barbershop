<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Barber;
use App\Models\Day;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call([

         RoleSeeder::class,
         DaySeeder::class,
         ServiceSeeder::class,
         StatusSeeder::class,
        //BarberSeeder::class,
        //ProductSeeder::class,
           // RoleSeeder::class,
           // DaySeeder::class, 
         

        ]);
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
