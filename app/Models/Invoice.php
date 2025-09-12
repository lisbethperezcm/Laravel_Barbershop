<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'client_id',
        'barber_id',
        'appointment_id',
        'total',
        'itbis',
        'status_id',
        'payment_type_id',
        'created_by',
        'updated_by',
        'reference_number',
        'approvation_number'

    ];

    protected $casts = [

        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    public function barber()
    {
        return $this->belongsTo(Barber::class);
    }
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
    public function invoiceDetails()
    {
        return $this->hasMany(InvoiceDetail::class);
    }

    public function invoiceProductDetails()
{
    // ¡Solo productos!
    return $this->invoiceDetails()->whereNotNull('product_id');
}

    public function paymentType()
    {
        return $this->belongsTo(PaymentType::class);
    }
    public function status()
    {
        return $this->belongsTo(Status::class);
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
