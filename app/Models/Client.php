<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;
    protected $fillable = [
        'person_id', // Relación con la tabla Persona
];

public function person(){

    //Relacion entre el modelo cliente y el modelo persona
    return $this->belongsTo(Person::class);
    
}
public function appointments()
{
    return $this->hasMany(Appointment::class);
}

}
