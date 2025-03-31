<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarberDispatch extends Model
{
    use HasFactory;

    protected $table = 'barber_dispatches'; 

    protected $fillable = [
        'exit_id',
        'barber_id',
        'dispatch_date',
        'status_id',
        'created_by',
        'updated_by',
    ];


    // Relación con la salida de inventario
    public function inventoryExit()
    {
        return $this->belongsTo(InventoryExit::class, 'exit_id');
    }

    /**
     * Relación con la tabla barbero
     */
    public function barber()
    {

        return $this->belongsTo(Barber::class);
    }
    /**
     * Relación con la tabla status
     */
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

            // El usuario que está creando el registro es accesible desde Auth
            $person->created_by = auth()->user()->id;
        });

        static::updating(function ($person) {
            // El usuario que está modificando el registro es accesible desde Auth
            $person->updated_by = auth()->user()->id;
        });
    }
}
