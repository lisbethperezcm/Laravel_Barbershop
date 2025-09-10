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
    //Funcion para filtrar la fecha de los despachos de barberos
    public function scopeDateRange($query, ?string $start, ?string $end)
    {
        if ($start && $end) {
            return $query->whereBetween('dispatch_date', [$start, $end]);
        }
        if ($start) {
            return $query->whereDate('dispatch_date', '>=', $start);
        }
        if ($end) {
            return $query->whereDate('dispatch_date', '<=', $end);
        }
        return $query;
    }

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
