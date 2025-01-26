<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Client;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AppointmentRequest;
use App\Http\Resources\AppointmentCollection;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $appointments = Appointment::with(['barber', 'client', 'services','createdBy.person'])->get();

        //colección de barberos
        return new AppointmentCollection($appointments);
    }

    /**
     * Show the form for creating a new resource.
     */
  
    /**
     * Store a newly created resource in storage.
     */
    public function store(AppointmentRequest $request)
    {

        $user = auth()->user();
       if(!$user){
            return response()->json(['error' => 'Usuario no aa autenticado.'], 401);
        }
       
 
        $client_id = $request->client_id ?? $user->person->client->id ?? null;

 // Verificar si el usuario tiene un cliente asociado
 if (!$client_id) {
     return response()->json(['error' => 'El usuario no tiene un cliente asociado.'], 400);
 }
         // Obtener el cliente asociado
       //$client = Client::findOrFail($client_id);
 
         // Crear la cita
         $appointment = new Appointment();
         $appointment->client_id = $client_id;
         $appointment->barber_id = $request->barber_id;
         $appointment->appointment_date = $request->appointment_date;
         $appointment->start_time = Carbon::parse($request->start_time)->format('H:i:s');
         $appointment->end_time = Carbon::parse($request->end_time)->format('H:i:s');
        
 
         // Guardar la cita
         $appointment->save();
 
         // Asociar los servicios a la cita mediante la tabla pivote
         $appointment->services()->attach($request->services);
 
         // Retornar respuesta
         return response()->json([
             'message' => 'Cita creada exitosamente.',
             'appointment' => $appointment,
         ], 201);
     }
    

    /**
     * Display the specified resource.
     */
    public function show(Appointment $appointment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Appointment $appointment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Appointment $appointment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Appointment $appointment)
    {
        //
    }
}
