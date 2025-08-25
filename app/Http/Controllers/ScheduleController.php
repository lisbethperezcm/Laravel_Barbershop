<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Carbon\Carbon;
use App\Models\Schedule;
use Illuminate\Http\Request;
use App\Http\Resources\ScheduleResource;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Obtener el barbero del request o del usuario autenticado
        $user = auth()->user();
        $barberId = $request->input('barber_id') ?? ($user->person->barber->id ?? null);

        if (!$barberId) {
            return response()->json([
                'message' => 'No se encontró el barbero.',
                'errorCode' => '404'
            ], 404);
        }

        $schedules = Schedule::where('barber_id', $barberId)->get();

        return response()->json([
            'data' => ScheduleResource::collection($schedules),
            'errorCode' => '200'
        ], 200);
    }
    public function getAvailableSlots(Request $request)
    {

        $user = auth()->user();
        if (!$user) {
            return response([
                'message' => 'No se pudo autenticar al usuario.',
                'errorCode' => '401'
            ], 401);
        }

        
        //
        $barberId = $request->barber_id ?? ($user->person->barber->id ?? null);
        $date = $request->date;
        $dayOfWeek = Carbon::parse($date)->dayOfWeek;
        $duration = $request->duration; // Duración del servicio en minutos

        // Obtener horario del barbero basado en el día de la semana
        $schedule = Schedule::where('barber_id', $barberId)
            ->where('day_id', $dayOfWeek)
            ->first();

        if (!$schedule) {
            //Retornar el arreglo vacio
            return response()->json([
                'message' => 'El barbero seleccionado no trabaja en este dia',
                'errorCode' => '200'
            ], 200);
        }

        $workStart = Carbon::parse($schedule->start_time); // Inicio del horario del barbero
        $workEnd = Carbon::parse($schedule->end_time); // Fin del horario del barbero

        // 2Obtener todas las citas reservadas de ese día
        $appointments = Appointment::where('barber_id', $barberId)
            ->whereDate('appointment_date', $date)
            ->orderBy('start_time')
            ->get(['start_time', 'end_time']);

        // Determinar los espacios vacíos entre citas
        $freeSlots = [];
        $currentStart = $workStart->copy();

        foreach ($appointments as $appointment) {
            $appointmentStart = Carbon::parse($appointment->start_time);
            $appointmentEnd = Carbon::parse($appointment->end_time);

            //Obtener espacios libre entre el horario actual y las citas
            if ($currentStart->lt($appointmentStart)) {
                $freeSlots[] = [
                    'start_time' => $currentStart->copy(),
                    'end_time' => $appointmentStart->copy(),
                ];
            }

            // Avanzar al final de la cita 
            $currentStart = $appointmentEnd->copy();
        }

        //Recorrer los espacios libres hasta finalizar al final de la jornada
        if ($currentStart->lt($workEnd)) {
            $freeSlots[] = [
                'start_time' => $currentStart->copy(),
                'end_time' => $workEnd->copy(),
            ];
        }

        // Generar intervalos disponibles dentro de los espacios vacíos
        $availableSlots = [];

        foreach ($freeSlots as $slot) {
            $start = $slot['start_time'];
            $end = $slot['end_time'];

            while ($start->addMinutes($duration)->lte($end)) {
                $availableSlots[] = [
                    'start_time' => $start->copy()->subMinutes($duration)->format('H:i'),
                    'end_time' => $start->copy()->format('H:i'),
                ];
            }
        }

        //Retornar los espacios disponibles
        return response()->json([
            'data' => $availableSlots,
            'errorCode' => '200'
        ], 200);
    }


   public function toggleStatus(Request $request)
{ 
    $id = $request->input('id');
    $schedule = Schedule::find($id);

    if (!$schedule) {
        return response()->json([
            'message' => 'Horario no encontrado.',
            'errorCode' => '404'
        ], 404);
    }

    // Solo validar conflictos si el horario está ACTIVO y se va a inactivar
    if ($schedule->status_id == 1) {
        $mysqlDayId = ($schedule->day_id == 7) ? 1 : $schedule->day_id + 1;
        $hasConflict = Appointment::where('barber_id', $schedule->barber_id)
            ->where('status_id', 3)
            ->whereDate('appointment_date', '>', now()->toDateString())
            ->whereRaw('DAYOFWEEK(appointment_date) = ?', [$mysqlDayId])
            ->exists();

        if ($hasConflict) {
            return response()->json([
                'message' => 'No puedes inactivar este horario porque el barbero tiene citas futuras en ese día. Modifica o cancela esas citas primero.',
                'errorCode' => '409'
            ], 409);
        }
    }

    // Cambia el estado: 1 = activo, 2 = inactivo
    $schedule->status_id = ($schedule->status_id == 1) ? 2 : 1;
    $schedule->save();

    return response()->json([
        'message' => 'Estado del horario actualizado exitosamente.',
        'data' => $schedule,
        'errorCode' => '200'
    ], 200);
}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Schedule $schedule)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Schedule $schedule)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Schedule $schedule)
    {
        //
    }
}
