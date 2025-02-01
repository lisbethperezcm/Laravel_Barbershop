<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Barber;
use App\Models\Person;
use App\Models\Schedule;
use Illuminate\Http\Request;
use App\Http\Resources\BarberCollection;

class BarberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $barbers = Barber::with(['person.user'])->get();

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
    }

    /**
     * Display the specified resource.
     */
    public function show(Barber $barber)
    {
        //
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
        //
    }
}
