<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'client_id',
        'appointment_id',
        'total_amount',
        'tax_amount',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
      
        'created_at'=> 'datetime',
        'updated_at'=> 'datetime',
    ];
   
        public function client()
        {
            return $this->belongsTo(Client::class);
        }
    
        public function appointment()
        {
            return $this->belongsTo(Appointment::class);
        }
        public function InvoiceDetails()
        {
            return $this->hasMany(InvoiceDetail::class);
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

