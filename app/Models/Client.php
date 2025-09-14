<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use SoftDeletes, HasFactory;
    protected $fillable = [
        'person_id', // Relationship with the Person table
    ];
    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the last three services used by the client.
     *
     * This method retrieves the last three completed appointments of the client,
     * and then collects all unique services from those appointments.
     *
     * @return \Illuminate\Support\Collection
     */
    public function lastThreeServices()
    {

        $appointments = $this->appointments()
            ->where('status_id', 7) // Completed appointments
            ->orderBy('appointment_date', 'desc')
            ->take(3)
            ->with('services')
            ->get();


        // Return only the IDs of the services
        return $appointments->flatMap(function ($appointment) {
            return $appointment->services->pluck('id');
        })->unique()->values()->toArray();
    }
    public function person()
    {

        //Relacion entre el modelo cliente y el modelo persona
        return $this->belongsTo(Person::class)->withTrashed();
    }
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }


    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
    
    public function review()
    {
        return $this->hasMany(BarberReview::class);
    }
}
