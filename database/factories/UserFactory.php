<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\User;
use App\Models\Person;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;
    protected $model = User::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    { 

        
        $user = User::create([
            
            'email' => $this->faker->unique()->safeEmail,
            'password' => bcrypt('password123'), // Contraseña predeterminada
            'role_id' => Role::inRandomOrder()->first()->id, // Asignar un rol aleatorio
        ]);

        // Crear la persona asociada al usuario
        $person = Person::factory()->create([
            'user_id' => $user->id, // Asociar la persona al usuario
        ]);
        
        return [
           
            'email' => $this->faker->unique()->safeEmail,
            'password' => bcrypt('password123'),
            'role_id' => Role::inRandomOrder()->first()->id, // Asignar un rol aleatorio
        ];
    }
}
