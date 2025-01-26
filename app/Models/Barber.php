<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barber extends Model
{
    use HasFactory;

    protected $fillable = [
        'person_id', // Relación con la tabla Persona
        'status', // 'active' o 'inactive'
 
    ];


    public function person()
{
    return $this->belongsTo(Person::class);
}
    public function schedules()
    {
        return $this->hasMany(Schedule::class); 
    }
    public function appointments(){

        return $this->hasMany(Appointment::class, 'appointment_service');
    }
    
}
