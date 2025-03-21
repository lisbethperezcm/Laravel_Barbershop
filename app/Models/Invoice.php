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
        'total',               
        'itbis',              
        'status_id',           
        'payment_type_id',    
        'created_by',
        'updated_by',
        'reference_number',    
        'approvation_number',  
        'is_deleted'           
    ];

    protected $casts = [
      
        'created_at'=> 'datetime',
        'updated_at'=> 'datetime',
        'is_deleted' => 'boolean'
    ];
   
        public function client()
        {
            return $this->belongsTo(Client::class);
        }
    
        public function appointment()
        {
            return $this->belongsTo(Appointment::class);
        }
        public function invoiceDetails()
        {
            return $this->hasMany(InvoiceDetail::class);
        }

        public function paymentType()
        {
        return $this->belongsTo(PaymentType::class);
        }
    
/*
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
    }*/
}

