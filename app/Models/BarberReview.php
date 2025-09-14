<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarberReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'barber_id',
        'appointment_id',
        'rating',
        'comment',
    ];

        protected $casts = [
        'created_at'=> 'datetime',
        'updated_at'=> 'datetime'
    ];
    /**
     * Relación con la tabla cliente
     */

     public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function barber()
    {
        return $this->belongsTo(Barber::class, 'barber_id');
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

}
