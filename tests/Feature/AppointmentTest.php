<?php

namespace Tests\Feature;

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
          'appointment_date'=>'2025-02-04',
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
               ->assertJsonStructure(['Cita creada exitosamente.']);

      // Verificar que la cita está en la base de datos
      $this->assertDatabaseHas('appointments', [
          'client_id' => $client->id,
          'barber_id' => $barber->id,
          'start_time' => '10:00:00',
          'end_time' => '10:30:00',
      ]);
  }
}
