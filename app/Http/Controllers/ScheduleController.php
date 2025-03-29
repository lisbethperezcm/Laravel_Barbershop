<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Carbon\Carbon;
use App\Models\Schedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }
    public function getAvailableSlots(Request $request)
    {
        $barberId = $request->barber_id;
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
                'data' => [],
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


    /*
    public function getAvailableSlots(Request $request)
{
    $barberId = $request->barber_id;
    $date = $request->date;
    $dayOfWeek = Carbon::parse($date)->dayOfWeek;
    $duration = $request->duration;

    // Obtener horario del barbero para ese día
    $schedule = Schedule::where('barber_id', $barberId)
                       ->where('day_id', $dayOfWeek)
                       ->first();

    if (!$schedule) {
        return response()->json(['message' => 'El barbero no labora ese dia'], 404);
    }

    // Obtener citas reservadas para ese día, ordenadas por inicio
    $appointments = Appointment::where('barber_id', $barberId)
                               ->whereDate('appointment_date', $date)
                               ->orderBy('start_time')
                               ->get(['start_time', 'end_time']);

    // Definir el inicio y el final del horario laboral
    $start = Carbon::parse($schedule->start_time);
    $end = Carbon::parse($schedule->end_time);
    
    $availableSlots = [];

    // Revisar espacios entre citas
    $currentSlotStart = $start->copy();

    foreach ($appointments as $appointment) {
        $appointmentStart = Carbon::parse($appointment->start_time);
        $appointmentEnd = Carbon::parse($appointment->end_time);

        // Generar espacios antes de la cita reservada
        while ($currentSlotStart->addMinutes($duration)->lessThanOrEqualTo($appointmentStart)) {
            $availableSlots[] = [
                'start_time' => $currentSlotStart->copy()->subMinutes($duration)->format('H:i'),
                'end_time' => $currentSlotStart->copy()->format('H:i'),
            ];
        }

        // Permitir que una cita inicie justo cuando otra termina
        if ($currentSlotStart->equalTo($appointmentEnd)) {
            continue;
        }

        // Valida si el horario disponible es menor a una cita programada, si lo es entonces avanza al final de la cita
        if ($currentSlotStart->lessThan($appointmentEnd)) {
            $currentSlotStart = $appointmentEnd->copy();
        }
    }

    // Generar horarios después de la última cita hasta el final del día
    while ($currentSlotStart->addMinutes($duration)->lessThanOrEqualTo($end)) {
        $availableSlots[] = [
            'start_time' => $currentSlotStart->copy()->subMinutes($duration)->format('H:i'),
            'end_time' => $currentSlotStart->copy()->format('H:i'),
        ];
    }

    return response()->json($availableSlots);
}

*/


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
