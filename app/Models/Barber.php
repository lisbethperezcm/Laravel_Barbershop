<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Barber extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'person_id', // Relación con la tabla Persona
        'status', // 'active' o 'inactive'

    ];
    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    public function person()
    {
        return $this->belongsTo(Person::class);
    }
    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
    public function appointments()
    {

        return $this->hasMany(Appointment::class, 'appointment_service');
    }
    public function commission()
    {
        return $this->hasOne(BarberCommission::class, 'barber_id');
    }

    public function invoices()
    {

        return $this->hasMany(Invoice::class);
    }
    public function barberDispatches()
    {

        return $this->hasMany(BarberDispatch::class);
    }
}
