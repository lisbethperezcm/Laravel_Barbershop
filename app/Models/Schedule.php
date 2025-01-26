<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'barber_id', 
        'day_id', 
        'start_time', 
        'end_time', 
        'status',
    ];


    public function barber()
    {
        return $this->belongsTo(Barber::class); 
    }

    public function day()
    {
        return $this->belongsTo(Day::class); 
    }

    public static function boot()
    {
        parent::boot();
    
        static::updating(function ($person) {
            // El usuario que está modificando el registro
            // Supongamos que el usuario actual es accesible desde Auth
            $person->updated_by = auth()->user()->id;
        });
    }
}
