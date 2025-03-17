<?php

namespace Database\Factories;

use App\Models\Barber;
use App\Models\User;
use App\Models\Person;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Barber>
 */
class BarberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

     protected $model = Barber::class;
    public function definition(): array
    {
        
            return [
                'person_id'=> Person::factory()->withRole(2)->create()->id,
                'status' => $this->faker->randomElement(['active', 'inactive']),
                'created_at' => now(),
                'updated_at' => now(),
            ];
    
}
}