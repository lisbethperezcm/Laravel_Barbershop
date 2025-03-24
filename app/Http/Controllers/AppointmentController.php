<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Client;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Http\Requests\GetAppointmentsRequest;
use App\Http\Resources\AppointmentCollection;
use App\Notifications\AppointmentNotification;

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
     * Store a newly created resource in storage.
     */
    public function store(AppointmentRequest $request)
    {

        $user = auth()->user();
       if(!$user){
            return response()->json(['error' => 'Usuario no autenticado.'], 401);
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
         $appointment->status_id = 3;
        
 
         // Guardar la cita
         $appointment->save();
 
         // Asociar los servicios a la cita mediante la tabla pivote
         $appointment->services()->attach($request->services);

        // $appointment = Appointment::with(['barber', 'client', 'services', 'createdBy.person'])->findOrFail($appointment->id);

         

      
        $appointment->client->person->user->notify(new AppointmentNotification($appointment));
    
 
        $appointment = new AppointmentResource($appointment);
         // Retornar respuesta
         return response()->json([
             'message' => 'Cita creada exitosamente.',
             'appointment' =>$appointment
         ], 201);
     }
    

    /**
     * Display the specified resource.
     */
    public function show(Appointment $appointment)
    {
        //Consultar la cita uniendo las relaciones de los otros modelos
       $appointment = Appointment::with(['barber', 'client', 'services', 'createdBy.person'])->findOrFail($appointment->id);

     
        //Retornar la cita encontrada formateada con AppointmentResource
        return new AppointmentResource($appointment);
    }
    



    public function getAppointmentsByClient(GetAppointmentsRequest $request)
{
        // Obtener el client_id y status_id del request (si viene)
        $client_id = $request->input('client_id');
        $status_id= $request->input('status_id');


    if (!$client_id) {
        $user = Auth::user();
        
        // Verificar si el usuario autenticado tiene un cliente asociado
        if (!$user || !$user->person || !$user->person->client) {
            return response()->json(['message' => 'Cliente no encontrado'], 404);

            $client_id = $user->person->client->id;
        }

      
    }

    $appointments = Appointment::with(['barber', 'client', 'services', 'createdBy.person'])
                                  ->where('client_id', $client_id)
                                  //->byStatus($status_id)
                                  ->when($status_id, fn($query) => $query->where('status_id', $status_id))
                                  ->get();
                           

    return new AppointmentCollection($appointments);
}
    /**
     * Update the specified resource in storage.
     */
    
     public function update(Request $request, Appointment $appointment)
{
    // Obtener los datos de la solicitud sin validación
   
    $appointment = Appointment::findOrFail($appointment->id);    
        
    
    $validatedData = $request->all();

    // Actualizar la cita con los datos recibidos
    $appointment->update($validatedData);

    // Si se enviaron servicios, actualizarlos
    if (isset($validatedData['services'])) {
        $appointment->services()->sync($validatedData['services']);
    }

    // Devolver la cita actualizada
    return response()->json([
        'message' => 'Appointment updated successfully',
        'data' => new AppointmentResource($appointment) // Devuelve el recurso actualizado
    ], 200);
}

    


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Appointment $appointment)
    {
        //
    }
}
