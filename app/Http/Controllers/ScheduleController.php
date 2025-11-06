<?php

namespace App\Http\Controllers;

use App\Http\Requests\ScheduleRequest;
use App\Models\Appointment;
use Carbon\Carbon;
use App\Models\Schedule;
use Illuminate\Http\Request;
use App\Http\Resources\ScheduleResource;
use App\Models\Barber;

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
            'message' => 'Horarios obtenidos exitosamente.',
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

        $request->validate([
            'date'     => ['required', 'date'],
            'duration' => ['required', 'integer', 'min:1'],
            'barber_id' => ['nullable', 'integer', 'exists:barbers,id'],
        ]);

        //
        $barberId = $request->barber_id ?? ($user->person->barber->id ?? null);
        $date = $request->date;
        $dayOfWeek = Carbon::parse($date)->dayOfWeekIso;
        $duration = $request->duration; // Duración del servicio en minutos

        $suggestedBarber = null;

        if (!$barberId) {
            $barberId = $this->pickLeastLoadedBarberIdByCount($date);
            $suggestedBarber = $barberId;



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
            ->where('status_id', 1) // Validar que el horario esté activo
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
            ->whereIn('status_id', [3, 5, 7])
            ->orderBy('start_time')
            ->get(['start_time', 'end_time']);


        $freeSlots = [];
        $currentStart = $workStart->copy();

        foreach ($appointments as $appointment) {
            $appointmentStart = Carbon::parse($appointment->start_time);
            $appointmentEnd   = Carbon::parse($appointment->end_time);

            if ($currentStart->lt($appointmentStart)) {
                $freeSlots[] = [
                    'start_time' => $currentStart->copy(),
                    'end_time'   => $appointmentStart->copy(),
                ];
            }
            $currentStart = $appointmentEnd->copy();
        }

        if ($currentStart->lt($workEnd)) {
            $freeSlots[] = [
                'start_time' => $currentStart->copy(),
                'end_time'   => $workEnd->copy(),
            ];
        }

        /* ============================
   ⚠️ Ajuste por almuerzo 
   ============================ */
        $lunchStart = null;
        $lunchEnd   = null;
        if ($barberId) {
            $barber = Barber::find($barberId);
            if ($barber && $barber->lunch_start && $barber->lunch_end && $barber->lunch_end > $barber->lunch_start) {
                $lunchStart = Carbon::parse($barber->lunch_start);
                $lunchEnd   = Carbon::parse($barber->lunch_end);
            }
        }

        if ($lunchStart && $lunchEnd) {
            $adjustedFree = [];
            foreach ($freeSlots as $slot) {
                $s = $slot['start_time'];
                $e = $slot['end_time'];

                // sin solape con almuerzo → se queda igual
                if ($e->lte($lunchStart) || $s->gte($lunchEnd)) {
                    $adjustedFree[] = ['start_time' => $s, 'end_time' => $e];
                    continue;
                }

                // el almuerzo recorta por el medio 
                if ($s->lt($lunchStart) && $e->gt($lunchEnd)) {
                    $adjustedFree[] = ['start_time' => $s->copy(),          'end_time' => $lunchStart->copy()];
                    $adjustedFree[] = ['start_time' => $lunchEnd->copy(),    'end_time' => $e->copy()];
                    continue;
                }

                // el almuerzo tapa el inicio del hueco → corre inicio al fin del almuerzo
                if ($s->lt($lunchEnd) && $e->gt($lunchEnd)) {
                    $adjustedFree[] = ['start_time' => $lunchEnd->copy(), 'end_time' => $e->copy()];
                    continue;
                }

                // el almuerzo tapa el final del hueco → recorta final al inicio del almuerzo
                if ($s->lt($lunchStart) && $e->gt($lunchStart)) {
                    $adjustedFree[] = ['start_time' => $s->copy(), 'end_time' => $lunchStart->copy()];
                    continue;
                }
            }
            $freeSlots = $adjustedFree;
        }

        // 🔹 Generar intervalos disponibles dentro de los espacios vacíos
        // 1) Generar los slots
        $availableSlots = [];

        foreach ($freeSlots as $slot) {
            $start = $slot['start_time']->copy();
            $end   = $slot['end_time']->copy();

            while ($start->copy()->addMinutes($duration)->lte($end)) {
                $availableSlots[] = [
                    'start_time' => $start->format('H:i'), // siempre 24h y con cero a la izquierda
                    'end_time'   => $start->copy()->addMinutes($duration)->format('H:i'),
                ];
                $start->addMinutes($duration);
            }
        }

        // 2) Asegurar que sea un array “puro”
        $availableSlots = is_array($availableSlots) ? $availableSlots : (array) $availableSlots;

        // 3) Ordenar por start_time de forma robusta (en minutos)
        usort($availableSlots, function ($a, $b) {
            [$ah, $am] = array_map('intval', explode(':', $a['start_time']));
            [$bh, $bm] = array_map('intval', explode(':', $b['start_time']));
            return ($ah * 60 + $am) <=> ($bh * 60 + $bm);
        });

        // 4) Responder JSON
        $response = [
            'data' => $availableSlots,
            'errorCode' => 200,
        ];

        // Solo agregar suggested_barber si fue autoasignado
        if (!is_null($suggestedBarber)) {
            $response['suggested_barber'] = $suggestedBarber;
        }

        return response()->json($response, 200);
    }

    public function getLunchTime(Barber $barber)
    {
        $barber = Barber::findOrFail($barber->id);

        return response()->json([
            'lunch_start' => $barber->lunch_start,
            'lunch_end'   => $barber->lunch_end,
            'errorCode'   => '200'
        ], 200);
    }

    public function updateOrCreateLunchTime(Request $request,)
    {
        $request->validate([
            'barber_id'   => ['required', 'integer', 'exists:barbers,id'],
            'lunch_start' => ['required', 'date_format:H:i:s'],
            'lunch_end'   => ['required', 'date_format:H:i:s', 'after:lunch_start'],
        ]);

        $barber = Barber::findOrFail($request->barber_id);

        Barber::updateOrCreate(
            ['id' => $barber->id],
            [
                'lunch_start' => $request->lunch_start,
                'lunch_end'   => $request->lunch_end,
            ]
        );

        return response()->json([
            'message' => 'Horario de almuerzo actualizado exitosamente.',
            'data' => [
                'lunch_start' => $barber->lunch_start,
                'lunch_end'   => $barber->lunch_end,
            ],
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

    /* Seleccionar el barbero con menos citas en una fecha dada */



    private function pickLeastLoadedBarberIdByCount(string $date): ?int
    {
        $day    = Carbon::parse($date);
        $isoDow = $day->dayOfWeekIso;
        $year   = (int) $day->year;
        $month  = (int) $day->month;

        // Estados que bloquean agenda
        $blockingStatuses = [3, 5, 7];

        return Barber::query()
            // Solo barberos con horario ACTIVO ese día de la semana
            ->whereExists(function ($q) use ($isoDow) {
                $q->selectRaw(1)
                    ->from('schedules')
                    ->whereColumn('schedules.barber_id', 'barbers.id')
                    ->where('schedules.day_id', $isoDow)
                    ->where('schedules.status_id', 1); // activo
            })

            // Conteo del DÍA (LEFT JOIN con alias a_day)
            ->leftJoin('appointments as a_day', function ($join) use ($date, $blockingStatuses) {
                $join->on('a_day.barber_id', '=', 'barbers.id')
                    ->whereDate('a_day.appointment_date', $date)
                    ->whereIn('a_day.status_id', $blockingStatuses);
            })

            // Conteo del MES (LEFT JOIN con alias a_mon)
            ->leftJoin('appointments as a_mon', function ($join) use ($year, $month, $blockingStatuses) {
                $join->on('a_mon.barber_id', '=', 'barbers.id')
                    ->whereYear('a_mon.appointment_date', $year)
                    ->whereMonth('a_mon.appointment_date', $month)
                    ->whereIn('a_mon.status_id', $blockingStatuses);
            })

            ->select('barbers.id')
            // DISTINCT evita inflar los conteos por el doble join
            ->selectRaw('COALESCE(COUNT(DISTINCT a_day.id), 0)  AS day_count')
            ->selectRaw('COALESCE(COUNT(DISTINCT a_mon.id), 0)  AS month_count')
            ->groupBy('barbers.id')

            // Desempate: día → mes → aleatorio
            ->orderBy('day_count', 'asc')
            ->orderBy('month_count', 'asc')
            ->orderByRaw('RAND()') // MySQL; usa random() si es Postgres

            ->value('barbers.id');
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(ScheduleRequest $request, Barber $barber)
    {

        $barber = Barber::findOrFail($barber->id);
        $schedule = $barber->schedule;
        // Validación ligera basada en lo que aceptamos actualizar  

        if ($request->filled('schedules')) {
            foreach ($request->input('schedules') as $sch) {
                Schedule::updateOrCreate(
                    [
                        'barber_id' => $barber->id,
                        'day_id'    => $sch['day_id'],
                    ],
                    [
                        'start_time' => $sch['start_time'],
                        'end_time'   => $sch['end_time'],
                        'status_id'  => $sch['status_id'] ?? '1',
                    ]
                );
            }
        }

        // Devolver los horarios del barbero actualizados
        $barber->load(['schedules' => fn($q) => $q->orderBy('day_id')]);



        return response()->json([
            'message'   => 'Horarios actualizados exitosamente',
            'data' => ScheduleResource::collection($barber->schedules),
            'errorCode' => '200',
        ], 200);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Schedule $schedule)
    {
        //
    }
}
