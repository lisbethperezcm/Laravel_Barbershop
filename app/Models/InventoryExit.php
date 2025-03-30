<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryExit extends Model
{
    use HasFactory;

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

     // Relación con el detalle de salida de inventario 

    public function exitDetails()
    {
        return $this->hasMany(ExitDetail::class, 'exit_id');
    }

      // Relación con el despacho al barbero
      public function barberDispatch()
      {
          return $this->hasOne(BarberDispatch::class, 'exit_id');
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
