<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;
    protected $fillable = [
        'client_id',
        'barber_id',
        'appointment_date',
        'start_time',
        'end_time',
        'status_id',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */

    protected $casts = [

        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    // Funcion para establecer el filtro de las citas por estatus

    public function scopeByStatus($query, $status_id = null)
    {
        if (!is_null($status_id)) { // Si `status_id` no es null, aplica el filtro
            return $query->where('status_id', $status_id);
        }

        return $query; // Si `status_id` es null, devuelve la consulta sin modificarla
    }



    //Relaciones del modelo 
    public function client()
    {

        return $this->belongsTo(Client::class);
    }
    public function barber()
    {

        return $this->belongsTo(Barber::class);
    }
    public function services()
    {

        return $this->belongsToMany(Service::class, 'appointment_service');
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public static function boot()
    {
        parent::boot();

        static::creating(function ($person) {


            // El usuario que está creando el registro
            // Supongamos que el usuario actual es accesible desde Auth
            $person->created_by = auth()->user()->id;
        });

        static::updating(function ($person) {
            // El usuario que está modificando el registro
            // Supongamos que el usuario actual es accesible desde Auth
            $person->updated_by = auth()->user()->id;
        });
    }
}
