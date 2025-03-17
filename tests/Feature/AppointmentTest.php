<?php

namespace Tests\Feature;

use App\Models\Appointment;
use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\Barber;
use App\Models\Client;

use App\Models\Person;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AppointmentTest extends TestCase
{
    use DatabaseTransactions; // Mantiene los datos en memoria sin borrar la BD

    /** @test */
    public function un_cliente_puede_reservar_una_cita()
    {

        // Crear un cliente asociado a la persona
        $client = Client::factory()->create();

        // Crear un barbero asociado a la persona
        $barber = Barber::factory()->create();

        $user = $client->person->user;

        // Datos de la cita
        $appointmentData = [
            'client_id' => $client->id,
            'barber_id' => $barber->id,
            'appointment_date' => '2025-02-04',
            'start_time' => '10:00:00',
            'end_time' => '10:30:00',
            'services' => [1, 2],
        ];

        // Hacer la solicitud para reservar la cita
        $response = $this->actingAs($user, 'sanctum')->postJson('/api/appointments', $appointmentData);

        // Depurar la respuesta si la prueba falla
        $response->dump();

        // Verificar que la cita se creó correctamente
        $response->assertStatus(201)
            ->assertJson(['message' => 'Cita creada exitosamente.']);
        // Verificar que la cita está en la base de datos
        $this->assertDatabaseHas('appointments', [
            'client_id' => $client->id,
            'barber_id' => $barber->id,
            'start_time' => '10:00:00',
            'end_time' => '10:30:00',
        ]);
    }

    /** @test */
    public function it_fails_to_create_an_appointment_without_required_fields()
    {
        // Crear un usuario autenticado (puede ser un administrador o barbero)
        $user = User::factory()->create();

        // Enviar una solicitud POST sin datos
        $response = $this->actingAs($user, 'sanctum')->postJson('/api/appointments', []);

        // Verificar que la API responde con un error 422 (Unprocessable Entity)
        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'barber_id',
                'appointment_date',
                'start_time',
                'end_time',
                'services'
            ]);

        dump($response->json());
    }


    /** @test */
    public function it_fails_when_end_time_is_before_or_equal_to_start_time()
    {
        // Crear un usuario autenticado
        $user = User::factory()->create();

        // Crear un barbero
        $barber = \App\Models\Barber::factory()->create();

        // Crear una cita donde `end_time` es igual a `start_time`
        $response = $this->actingAs($user, 'sanctum')->postJson('/api/appointments', [
            'barber_id' => $barber->id,
            'appointment_date' => now()->format('Y-m-d'),
            'start_time' => '10:00:00',
            'end_time' => '09:00:00',
            'services' => [1, 2]
        ]);

        dump($response->json());
        // Verificar que Laravel devuelve un error de validación
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['end_time']);
    }
    /** @test */
    public function obtener_citas_by_client_and_status()
    {

        $client1 = Client::factory()->create();
        $client2 = Client::factory()->create();
        //Encontrar el usuario a partir de la relacion cliente a persona y persona a user
        $user = $client1->person->user;

        // Autenticar usuario antes de crear la cita
        $this->actingAs($user, 'sanctum');
        // Crear citas para `client1` con diferentes estados
        Appointment::factory()->count(7)->create([
            'client_id' => $client1->id,
        ]);
        // Crear citas para `client1` con estado 3 "Reservado"
        Appointment::factory()->count(3)->create([
            'client_id' => $client1->id,
            'status_id' => 3,
        ]);

        // Crear citas para `client2` con diferentes estados
        Appointment::factory()->count(6)->create([
            'client_id' => $client2->id,
        ]);

        // Hacer la solicitud para reservar la cita
        $response = $this->getJson("/api/clients/appointments?client_id={$client1->id}&status_id=3");

        // Mostrar el JSON en la terminal para debug si falla
        dump($response->json());

        // Verificar que la respuesta es 200
        $response->assertStatus(200);
        // Obtener los datos de la respuesta
        $data = $response->json('data');
        // Si la respuesta no tiene datos, la prueba debe fallar
        $this->assertNotEmpty($data, "La respuesta no contiene datos");

        // Recorrer cada cita y verificar que `status_id` sea 3
        foreach ($data as $appointment) {
            $this->assertEquals(3, $appointment['status_id'], "Se encontró una cita con status_id diferente de 3");
            $this->assertEquals($client1->id, $appointment['client_id'], "Se encontró una cita con client_id diferente de " . $client1->id);
        }
    }


    /** @test */
    public function obtener_citas_by_client()
    {

        $client1 = Client::factory()->create();
        $client2 = Client::factory()->create();
        //Encontrar el usuario a partir de la relacion cliente a persona y persona a user
        $user = $client1->person->user;

        // Autenticar usuario antes de crear la cita
        $this->actingAs($user, 'sanctum');
        // Crear citas para `client1` con diferentes estados
        Appointment::factory()->count(13)->create([
            'client_id' => $client1->id,
        ]);

        // Crear citas para `client2` con diferentes estados
        Appointment::factory()->count(6)->create([
            'client_id' => $client2->id,
        ]);

        // Hacer la solicitud para reservar la cita
        $response = $this->getJson("/api/clients/appointments?client_id={$client1->id}");

        // Mostrar el JSON en la terminal para debug si falla
        dump($response->json());

        // Verificar que la respuesta es 200
        $response->assertStatus(200)
            ->assertJsonCount(13, 'data');
        // Obtener los datos de la respuesta
        $data = $response->json('data');

        // Si la respuesta no tiene datos, la prueba debe fallar
        $this->assertNotEmpty($data, "La respuesta no contiene datos");

        // Recorrer cada cita y verificar que `client_id` sea el id del cliente solicitado
        foreach ($data as $appointment) {
            $this->assertEquals($client1->id, $appointment['client_id'], "Se encontró una cita con client_id diferente de " . $client1->id);
        }
    }
}
