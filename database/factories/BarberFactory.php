<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Barber;
use App\Models\Person;
use App\Models\Schedule;
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
public function configure()
{
    return $this->afterCreating(function (Barber $barber) {
        $defaultSchedules = [
            ['day_id' => 1, 'start_time' => '09:00:00', 'end_time' => '17:00:00', 'status' => 'active'], // Lunes
            ['day_id' => 2, 'start_time' => '09:00:00', 'end_time' => '17:00:00', 'status' => 'active'], // Martes
            ['day_id' => 3, 'start_time' => '09:00:00', 'end_time' => '17:00:00', 'status' => 'active'], // Miércoles
            ['day_id' => 4, 'start_time' => '09:00:00', 'end_time' => '17:00:00', 'status' => 'active'], // Jueves
            ['day_id' => 5, 'start_time' => '09:00:00', 'end_time' => '17:00:00', 'status' => 'active'], // Viernes
        ];

        foreach ($defaultSchedules as $schedule) {
            Schedule::create(array_merge($schedule, ['barber_id' => $barber->id]));
        }
    });
}
}