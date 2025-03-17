<?php

namespace Database\Factories;

use App\Models\Barber;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Appointment>
 */
class AppointmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::inRandomOrder()->first()->id,
            'barber_id' => Barber::inRandomOrder()->first()->id,
            'appointment_date' => $this->faker->date('Y-m-d'),
            'start_time' => $this->faker->time('H:i:s'),
            'end_time' => $this->faker->time('H:i:s'),
            'status_id' => $this->faker->randomElement([1, 3, 7]), // Solo selecciona estos valores
            'updated_by' => null,
            'created_at' => now(),
            'updated_at' => now()
        ];
    }
}
