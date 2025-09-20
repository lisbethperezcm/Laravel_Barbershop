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

    // ✅ Si no recibimos barber_id, auto-elegimos el barbero con menos citas que TRABAJE ese día
    if (!$barberId) {
        $barberId = $this->pickLeastLoadedWorkingBarberIdByCount($date, $dayOfWeek);
        if (!$barberId) {
            return response()->json([
                'barber_id' => 0,
                'data'      => [],
                'message'   => 'Ningún barbero disponible para esta fecha.',
                'errorCode' => '200'
            ], 200);
        }
    }

    // Obtener horario del barbero basado en el día de la semana
    $schedule = Schedule::where('barber_id', $barberId)
        ->where('day_id', $dayOfWeek)
        ->where('status', 'active') // Asegurarse de que el horario esté activo
        ->first();

    if (!$schedule) {
        //Retornar el arreglo vacio
        return response()->json([
            'barber_id' => $barberId,
            'message'   => 'El barbero seleccionado no trabaja en este dia',
            'errorCode' => '200',
            'data'      => []
        ], 200);
    }

    $workStart = Carbon::parse($schedule->start_time); // Inicio del horario del barbero
    $workEnd = Carbon::parse($schedule->end_time);     // Fin del horario del barbero

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

    //Retornar los espacios disponibles (🔥 ahora con barber_id usado)
    return response()->json([
        'barber_id' => $barberId,
        'data'      => $availableSlots,
        'errorCode' => '200'
    ], 200);
}

/**
 * Helper: devuelve el ID del barbero con MENOS citas ese día,
 * pero SOLO entre barberos que tienen Schedule ACTIVO para ese day_id.
 * (Usa conteo de citas del día; ajusta si quieres por minutos).
 */
private function pickLeastLoadedWorkingBarberIdByCount(string $date, int $dayOfWeek): ?int
{
    return \App\Models\Barber::query()
        ->whereExists(function ($q) use ($dayOfWeek) {
            $q->selectRaw(1)
              ->from('schedules')
              ->whereColumn('schedules.barber_id', 'barbers.id')
              ->where('schedules.day_id', $dayOfWeek)
              ->where('schedules.status', 'active');
        })
        ->leftJoin('appointments', function ($join) use ($date) {
            $join->on('appointments.barber_id', '=', 'barbers.id')
                 ->whereDate('appointments.appointment_date', $date);
        })
        ->select('barbers.id')
        ->selectRaw('COALESCE(COUNT(appointments.id), 0) AS total_appointments')
        ->groupBy('barbers.id')
        ->orderBy('total_appointments', 'asc')
        ->value('barbers.id');
}
