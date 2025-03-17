<?php
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AuthTest extends TestCase
{
    use DatabaseTransactions; // Mantiene los datos en memoria sin borrar la BD


    /** @test */
    public function un_usuario_puede_registrarse_exitosamente()
    {
        // Asegurar que hay al menos un rol en la BD
        $role = Role::firstOrCreate(['name' => 'Cliente']);

        // Simular una solicitud de registro con los campos correctos
        $response = $this->postJson('/api/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'role_id' => $role->id,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // Depurar en caso de error
        $response->dump();

        // Verificar que la respuesta es exitosa y devuelve un token
        $response->assertStatus(201)
                 ->assertJsonStructure(['access_token']);

        // Verificar que el usuario se guardó en la BD
        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
    }

    /** @test */
    public function un_usuario_puede_iniciar_sesion_correctamente()
    {
        //$role = Role::firstOrCreate(['name' => 'Cliente']);
        $this->un_usuario_puede_registrarse_exitosamente();

        // Simular una solicitud de inicio de sesión
        $response = $this->postJson('/api/login', [
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);
         // 🟢 Imprimir la respuesta del servidor para ver el error exacto
    $response->dump();

        // Verificar que el inicio de sesión es exitoso y devuelve un token
       $response->assertStatus(200)
                 ->assertJsonStructure(['access_token']);
    }

    /** @test */
    public function no_se_puede_iniciar_sesion_con_credenciales_incorrectas()
    {
        $role = Role::firstOrCreate(['name' => 'Cliente']);

        // Crear usuario manualmente en la base de datos
        User::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'role_id' => $role->id,
            'password' => Hash::make('password123'),
        ]);

        // Intentar iniciar sesión con una contraseña incorrecta
        $response = $this->postJson('/api/login', [
            'email' => 'john@example.com',
            'password' => 'wrongpassword',
        ]);

        // Verificar que devuelve error 401 (No autorizado)
        $response->dump();
        $response->assertStatus(401);
               //  ->assertJson(['message' => 'Credenciales incorrectas']);
    }

    /** @test */
    public function no_se_puede_registrar_un_usuario_con_email_existente()
    {
        $role = Role::firstOrCreate(['name' => 'Cliente']);

        // Insertar un usuario manualmente
        User::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'role_id' => $role->id,
            'password' => Hash::make('password123'),
        ]);

        // Intentar registrar otro usuario con el mismo email
        $response = $this->postJson('/api/register', [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'john@example.com', // Email duplicado
            'role_id' => $role->id,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // Verificar que devuelve error 422 (Unprocessable Entity)
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }
}
