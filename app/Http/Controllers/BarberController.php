<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Barber;
use App\Models\Person;
use App\Models\Schedule;
use Illuminate\Http\Request;
use App\Helpers\GeneralHelper;
use App\Models\BarberCommission;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\BarberResource;
use App\Http\Resources\BarberCollection;
use App\Http\Resources\BarberReportResource;

class BarberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $barbers = Barber::with(['person.user', 'commission'])->get();

        //colección de barberos
        return new BarberCollection($barbers);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Person $person, $request)
    {

        $barber = new Barber();
        $person->barber()->save($barber);

        // Crea horarios por defecto
        $defaultSchedules = [
            ['barber_id' => $barber->id, 'day_id' => 1, 'start_time' => '09:00:00', 'end_time' => '17:00:00', 'status' => 'active'], // Lunes
            ['barber_id' => $barber->id, 'day_id' => 2, 'start_time' => '09:00:00', 'end_time' => '17:00:00', 'status' => 'active'], // Martes
            ['barber_id' => $barber->id, 'day_id' => 3, 'start_time' => '09:00:00', 'end_time' => '17:00:00', 'status' => 'active'], // Miércoles
            ['barber_id' => $barber->id, 'day_id' => 4, 'start_time' => '09:00:00', 'end_time' => '17:00:00', 'status' => 'active'], // Jueves
            ['barber_id' => $barber->id, 'day_id' => 5, 'start_time' => '09:00:00', 'end_time' => '17:00:00', 'status' => 'active'], // Viernes
        ];

        foreach ($defaultSchedules as $schedule) {
            Schedule::create($schedule);
        }

        // Validar si el request tiene "commission" y crear el registro en BarberCommission
        if ($request->filled('commission')) {
            BarberCommission::create([
                'barber_id' => $barber->id,
                'current_percentage' => intval($request->commission), // Convertirlo en entero
            ]);
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(Barber $barber)
    {

        //Retornar la cita encontrada formateada con AppointmentResource
        return response()->json([
            'data' => new  BarberResource($barber),
            'errorCode' => '200'
        ], 200);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Barber $barber)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Barber $barber)
    {

        if ($barber->person) {
            $barber->person->delete(); // Soft Delete de la persona
        }
        if ($barber->person->user) {
            $barber->person->user->delete(); // Soft Delete del usuario
        }
        $barber->delete(); // Soft Delete: solo marca deleted_at

        return response()->json([
            'message' => 'Barbero eliminado correctamente',
            'errorCode' => 200
        ]);
    }

    public function calculateReport(Request $request)
    {
        $request->validate([
            'barber_id' => 'required|exists:barbers,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $barber = Barber::findOrFail($request->barber_id);
        $commissionRate = $barber->commission->current_percentage ?? 0;
        $commissionRate = $commissionRate / 100;

        // Obtener el total de servicios (filtrado con helper)
        $invoices = $barber->invoices()
            ->whereHas('appointment', fn($query) => $query->where('status_id', 7)) // Completado
            ->where('status_id', 8) // Pagada
            ->where(fn($query) => GeneralHelper::applyDateFilter($query, $request, DB::raw('DATE(created_at)')))
            ->with('client.person') // Include client details
            ->get();

        $totalServices = $invoices->sum('total');

        // Obtener el total de despachos (filtrado con helper)
        $dispatches = $barber->barberDispatches()
            ->whereHas('inventoryExit')
            ->where(fn($query) => GeneralHelper::applyDateFilter($query, $request, 'dispatch_date'))
            ->with('inventoryExit')
            ->get();


        $totalDispatches = $dispatches->sum(fn($dispatch) => $dispatch->inventoryExit->total);

        // Calculate commission and final balance

        $totalCommission = $totalServices * $commissionRate;
        $netIncome = $totalServices - $totalCommission;
        $finalBalance = $netIncome - $totalDispatches;



        return new BarberReportResource((object) [
            'barber_name' => $barber->person->first_name . ' ' . $barber->person->last_name,
            'total_services' => $totalServices,
            'commission_percentage' => $commissionRate * 100,
            'total_commission' => $totalCommission,
            'net_income' =>$netIncome, 
            'total_dispatches' =>$totalDispatches,
            'final_balance' => $finalBalance,
            'invoices' => $invoices,
            'dispatches' => $dispatches
        ]);
        
       
    }
}
