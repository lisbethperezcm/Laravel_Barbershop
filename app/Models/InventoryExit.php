<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryExit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'exit_type',
        'exit_date',
        'note',                     
        'total',             
        'created_by',
        'updated_by'       
    ];
    
    protected $casts = [
      
        'created_at'=> 'datetime',
        'updated_at'=> 'datetime'
    ];

     //Funcion para filtrar la fecha de las salidas de inventario 
    public function scopeDateRange($query, ?string $start, ?string $end)
    {
        if ($start && $end) {
            return $query->whereBetween('exit_date', [$start, $end]);
        }
        if ($start) {
            return $query->whereDate('exit_date', '>=', $start);
        }
        if ($end) {
            return $query->whereDate('exit_date', '<=', $end);
        }
        return $query;
    }


     // Relación con el detalle de salida de inventario 

    public function exitDetails()
    {
        return $this->hasMany(ExitDetail::class, 'exit_id');
    }

      // Relación con el despacho al barbero
      public function barberDispatch()
      {
          return $this->hasOne(BarberDispatch::class, 'exit_id')->withTrashed();
      }

       public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by')->withTrashed();
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
