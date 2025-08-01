<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Client;
use App\Models\Status;
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
        $appointments = Appointment::with(['barber', 'client', 'services', 'createdBy.person'])
            ->orderBy('appointment_date', 'desc')
            ->get();


        //Retornar el listado de citas formateada con AppointmentCollection
        return response()->json([
            'data' => new  AppointmentCollection($appointments),
            'errorCode' => '200'
        ], 200);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(AppointmentRequest $request)
    {

        //Obtener el usuario autenticado 
        $user = auth()->user();
        if (!$user) {
            return response([
                'message' => 'No se pudo autenticar al usuario.',
                'errorCode' => '401'
            ], 401);
        }

        $client_id = $request->client_id ?? $user->person->client->id ?? null;

        // Verificar si el usuario tiene un cliente asociado
        if (!$client_id) {
            return response([
                'message' => 'Cliente no encontrado.',
                'errorCode' => '404'
            ], 404);
        }

        $appointmentDate =$request->appointment_date;
        $start_time = Carbon::parse($request->start_time)->format('H:i:s');
        $end_time = Carbon::parse($request->end_time)->format('H:i:s');

        // Verificar si el cliente ya tiene otra cita en el mismo horario
        $clientConflict = Appointment::where('client_id', $client_id)
            ->where('appointment_date', $appointmentDate)
            ->where('status_id', '!=', 6)
            ->where(function ($query) use ($start_time, $end_time) {
                $query->where(function ($q) use ($start_time, $end_time) {
                    $q->where('start_time', '<', $end_time)
                        ->where('end_time', '>', $start_time);
                })->orWhere(function ($q) use ($start_time, $end_time) {
                    $q->where('start_time', '>=', $start_time)
                        ->where('end_time', '<=', $end_time);
                });
            })
            ->exists();

        if ($clientConflict) {
            return response()->json([
                'message' => 'El cliente ya tiene una cita programada en este horario con otro barbero.',
                 'errorCode' => '422'
            ], 422);
        }
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

        //Enviar confirmacion de la cita por correo
        $appointment->client->person->user->notify(new AppointmentNotification($appointment));


        $appointment = new AppointmentResource($appointment);
        // Retornar respuesta
        return response()->json([
            'message' => 'Cita creada exitosamente.',
            'appointment' => $appointment,
            'errorCode' => '201'
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
        return response()->json([
            'data' => new  AppointmentResource($appointment),
            'errorCode' => '200'
        ], 200);
    }

    public function getAppointmentsByClient(GetAppointmentsRequest $request)
    {

        //$user = auth()->user();
        // Obtener el status_id del request (si viene)
        $status_id = $request->input('status_id');
        $client_id = $request->client_id;

        //?? $user->person->client->id ?? null;

        // Verificar si el usuario autenticado tiene un cliente asociado
        if (!$client_id) {
            return response([
                'message' => 'Cliente no encontrado.',
                'errorCode' => '404'
            ], 404);
        }

        $appointments = Appointment::with(['barber', 'client', 'services', 'createdBy.person'])
            ->where('client_id', $client_id)
            //->byStatus($status_id)
            ->when($status_id, fn($query) => $query->where('status_id', $status_id))
            ->orderBy('appointment_date', 'desc')
            ->get();

        //Retornar el listado de citas formateada con AppointmentCollection
        return response()->json([
            'data' => new  AppointmentCollection($appointments),
            'errorCode' => '200'
        ], 200);
    }
    /**
     * Update the specified resource in storage.
     */


     /**PENDIENTE IMPLEMENTACION DEL AppointmentRequest */
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
            'message' => 'Cita actualizada con éxito',
            'data' => new AppointmentResource($appointment),
            'errorCode' => '200'
        ], 200);
    }
    /**
     * Actualizar el status de la cita
     */

    public function updateStatus(Request $request, Appointment $appointment)
    {
        $request->validate([
            'status' => 'required|exists:statuses,name',
        ]);

        $newStatusId = Status::where('name', $request->status)->value('id');

        // 🔹 Validar si la cita ya está en un estado que no se puede cambiar
        if ($appointment->status->name === 'Completado') {
            return response()->json([
                'message' => 'No puedes cambiar el estado de una cita que ya está completada.'
            ], 400);
        }

        if ($appointment->status->name === 'Cancelado' && $request->status !== 'Cancelado') {
            return response()->json([
                'message' => 'No puedes modificar una cita que ha sido cancelada.'
            ], 400);
        }

        $appointment->update([
            'status_id' => $newStatusId,
        ]);

        // 🔹 Recargar los datos con `fresh()`
        $appointment = $appointment->fresh();

        // Si la cita fue marcada como "Completado" y aún NO tiene factura se genera
        if ($appointment->status->name === 'Completado' && !$appointment->invoice) {

            $requestInvoice = new Request([
                'appointment_id' => $appointment->id,
                'status_id' => 8, // Estado inicial de la factura
            ]);
        }
        // Llamar al método store del InvoiceController
        app(InvoiceController::class)->store($requestInvoice);

        return response()->json([
            'message' => "Cita marcada como {$appointment->status->name}.",
            'appointment' => new AppointmentResource($appointment),
            'errorCode' => '200'
        ], 200);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Appointment $appointment)
    {

        $appointment->delete(); // Aplicar Soft Delete a la cita

        return response()->json([
            'message' => 'Cita eliminada correctamente',
            'errorCode' => 200
        ]);
    }
}
