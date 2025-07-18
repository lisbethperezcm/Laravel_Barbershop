<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Services\ReportService;


class ReportController extends Controller
{
    protected $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function dailySummary()
    {
        $today = Carbon::today();
        $summary = $this->reportService->getSummary($today, $today);

        // Barberos activos para el día de actual
        $dayOfWeek = $today->dayOfWeekIso;
        $activeBarbersCount = $this->reportService->getActiveBarbersByDay($dayOfWeek);


        // Devolver la respuesta en formato JSON
        return response()->json([
            'total_scheduled'   => $summary['total_scheduled'],
            'total_completed'   => $summary['total_completed'],
            'total_income'      => $summary['total_income'],
            'active_barbers'    => $activeBarbersCount,
            'errorCode' => '200'
        ], 200);
    }

    public function weeklySummary()
    {
        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        $summary = $this->reportService->getSummary($startOfWeek, $endOfWeek);

        return response()->json($summary);
    }

    public function monthlySummary()
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $summary = $this->reportService->getSummary($startOfMonth, $endOfMonth);

        return response()->json($summary);
    }



    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
