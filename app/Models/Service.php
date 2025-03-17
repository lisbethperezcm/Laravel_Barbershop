<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'current_price',
        'previous_price', 
        'duration',
        'updated_by'
    ];

    protected $casts = [
        
        'created_at'=> 'datetime',
        'updated_at'=> 'datetime',
    ];

    public function appointments()
    {
        return $this->belongsToMany(Appointment::class, 'appointment_service');
    }

    public function invoiceDetails()
    {
        return $this->hasMany(InvoiceDetail::class);
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
