<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory_exits extends Model
{
    use HasFactory;

    protected $fillable = [
        'exit_type',
        'exit_date',
        'note',               
        'itbis',              
        'total',             
        'created_by',
        'updated_by'       
    ];
    
    protected $casts = [
      
        'created_at'=> 'datetime',
        'updated_at'=> 'datetime'
    ];



  /*  public static function boot()
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
    }*/
}
