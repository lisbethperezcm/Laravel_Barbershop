<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'sale_price',
        'unit_cost',
        'stock',
        'itbis',
        'status_id',
        'created_by',
        'updated_by',

    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    /* public function scopeActive($query)
    {
        return $query->where('is_deleted', false);
    }*/

    /**
     * Relación con la tabla status
     */
    public function status()
    {
        return $this->belongsTo(Status::class);
    }
    public function invoiceDetails()
    {
        return $this->hasMany(InvoiceDetail::class);
    }

    public function exitDetails()
    {
        return $this->hasMany(ExitDetail::class);
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
