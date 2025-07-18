<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Barber;
use Carbon\Carbon;

class ReportService
{
    /**
     * Get a summary of appointments and barbers within a date range.
     *
     * @param Carbon $start
     * @param Carbon $end
     * @return array
     */
    public function getSummary(Carbon $start, Carbon $end)
    {
        $totalScheduled = Appointment::whereBetween('appointment_date', [$start, $end])->count();

        $totalCompleted = Appointment::whereBetween('appointment_date', [$start, $end])
            ->where('status_id', 7) // 7 es el estatus de cita completada
            ->count();

        $totalIncome = Invoice::whereRaw('DATE(created_at) BETWEEN ? AND ?', [$start, $end])
            ->where('status_id', 8)
            ->sum('total');


        return [
            'total_scheduled' => $totalScheduled,
            'total_completed' => $totalCompleted,
            'total_income' => $totalIncome,

        ];
    }

    public function getActiveBarbersByDay($dayOfWeek)
    {
        return Barber::whereHas('schedules', function ($q) use ($dayOfWeek) {
            $q->where('day_id', $dayOfWeek)
                ->where('status_id', 1); // 1 es el estatus activo
        })->count();
    }
}
