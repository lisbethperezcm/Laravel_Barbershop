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
        $barberId = $request->barber_id; // Barber ID
        $date = $request->date; // Selected date
        $dayOfWeek = Carbon::parse($date)->dayOfWeek; // 0 = Sunday, 6 = Saturday
        $duration = $request->duration; // Duration of the service in minutes (e.g., 30, 60)
    
        // 1. Get barber's working hours using 'day_id'
        $schedule = Schedule::where('barber_id', $barberId)
                           ->where('day_id', $dayOfWeek)  // Relating to the day of the week
                           ->first();
    
        if (!$schedule) {
            return response()->json(['message' => 'The barber does not work on this day'], 404);
        }
    
        // 2. Get reserved appointments for that date
        $appointments = Appointment::where('barber_id', $barberId)
                                   ->whereDate('appointment_date', $date)
                                   ->get(['start_time', 'end_time']);
    
        // 3. Generate time slots based on the barber's working hours and the provided duration
        $start = Carbon::parse($schedule->start_time); // Barber's start time
        $end = Carbon::parse($schedule->end_time); // Barber's end time
        $availableSlots = [];
    
        while ($start->lt($end)) {
            $slotEnd = $start->copy()->addMinutes($duration); // Create slot based on the service duration
            $isSlotAvailable = true;
    
            // Check if the slot overlaps with any appointment
            foreach ($appointments as $appointment) {
                $appointmentStart = Carbon::parse($appointment->start_time);
                $appointmentEnd = Carbon::parse($appointment->end_time);
    
                if (
                    ($start->lt($appointmentEnd) && $slotEnd->gt($appointmentStart))
                ) {
                    $isSlotAvailable = false;
                    break;
                }
            }
    
            if ($isSlotAvailable) {
                $availableSlots[] = [
                    'start_time' => $start->format('H:i'),
                    'end_time' => $slotEnd->format('H:i')
                ];
            }
    
            // Move to the next slot based on the service duration
            $start->addMinutes($duration);
        }
    
        return response()->json($availableSlots);
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
