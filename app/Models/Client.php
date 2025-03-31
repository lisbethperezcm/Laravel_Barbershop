<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use SoftDeletes, HasFactory;
    protected $fillable = [
        'person_id', // Relación con la tabla Persona
    ];
    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    public function person()
    {

        //Relacion entre el modelo cliente y el modelo persona
        return $this->belongsTo(Person::class);
    }
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }


    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
