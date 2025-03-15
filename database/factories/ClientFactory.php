<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Client;
use App\Models\Person;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     * 
     * @return array<string, mixed>
     */

     protected $model = Client::class;
    public function definition(): array
    {
        return [
            'person_id' =>Person::factory()->withRole(3)->create()->id,
            
            /*Person::factory()->create([
                'user_id' => User::factory()->role(3)->create()->id 
            ])->id*/
            
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
