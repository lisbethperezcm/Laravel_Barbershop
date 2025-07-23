<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Barber;
use App\Models\Invoice;
use App\Models\Appointment;
use App\Helpers\GeneralHelper;

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
        $start = $start->toDateString();
        $end = $end->toDateString();

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

    public function getTotalIncome(Carbon $start, Carbon $end)
    {
        return Invoice::whereRaw('DATE(created_at) BETWEEN ? AND ?', [$start, $end])
            ->where('status_id', 8) // 8 es el estatus de factura pagada
            ->sum('total');
    }

    /**
     * Obtiene los ingresos anuales agrupados por mes para un año específico.
     *
     * Esta función consulta las facturas pagadas (status_id = 8) del año proporcionado (o el año actual si no se especifica)

     */
    public function getYearlyIncomeByMonth($year = null)
    {
        $year = $year ?? now()->year;

        $incomes = Invoice::selectRaw('MONTH(created_at) as month, SUM(total) as total_income')
            ->whereYear('created_at', $year)
            ->where('status_id', 8) // Solo facturas pagadas
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        $monthNames = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
        ];

        $result = collect(range(1, 12))->map(function ($month) use ($incomes, $monthNames) {
            $income = $incomes[$month]->total_income ?? 0;
            return [
                'month' => $month,
                'month_name' => $monthNames[$month],
                'total_income' => GeneralHelper::getFloat($income),
            ];
        });

        return response()->json($result);
    }
}
