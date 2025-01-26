<?php

namespace Database\Factories;
use App\Models\Person;

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
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'phone_number' => $this->faker->phoneNumber,
            'address' => $this->faker->address,
        ];
    }
}
