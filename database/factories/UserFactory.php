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

        
        return [
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password123'), // Contraseña encriptada correctamente
            'role_id' => $this->faker->randomElement([1, 2, 3]), // Rol aleatorio
            'updated_by' => null,
            'created_at' => now(), // Establecer timestamp actual
            'updated_at' => now(), // Establecer timestamp actual
        ];
        
    }

    public function role($roleId)
    {
        return $this->state([
            'role_id' => $roleId
        ]);
    }
}
