<?php

namespace Database\Factories;
use App\Models\Person;
use App\Models\User;


use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Person>
 */
class PersonFactory extends Factory
{

    protected $model = Person::class;
    /**
     * Define the model's default state./
     
     * @return array<string, mixed>
     * 
     */


    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'phone_number' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'user_id' => User::factory(), // Crea un usuario y lo asocia
            'updated_by' => null,
            'created_at' => now(), // Establecer timestamp actual
            'updated_at' => now(), // Establecer timestamp actual
        ];
    }
   public function withRole($roleId)
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => User::factory()->role($roleId)->create()->id,
        ]);
    }
}
