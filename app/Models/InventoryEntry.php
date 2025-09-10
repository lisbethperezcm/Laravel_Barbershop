<?php
// app/Models/InventoryEntry.php

namespace App\Models;

use Dotenv\Parser\Entry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryEntry extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'entry_type',   // p.ej. "Compra"
        'entry_date',   // fecha de la entrada (Y-m-d)
        'invoice_number',
        'note',
        'total',
        'created_by',
        'updated_by'  
    ];

    protected $casts = [
      
        'created_at'=> 'datetime',
        'updated_at'=> 'datetime'
    ];

     //Funcion para filtrar la fecha de las entradas de inventario 
    public function scopeDateRange($query, ?string $start, ?string $end)
    {
        if ($start && $end) {
            return $query->whereBetween('entry_date', [$start, $end]);
        }
        if ($start) {
            return $query->whereDate('entry_date', '>=', $start);
        }
        if ($end) {
            return $query->whereDate('entry_date', '<=', $end);
        }
        return $query;
    }

    public function scopeInvoiceNumber($query, ?string $invoiceNumber)
    {
        if ($invoiceNumber) {
            return $query->where('invoice_number', 'like', '%' . $invoiceNumber . '%');
        }
        return $query;
    }

    /** Relación con los detalles de la entrada */
    public function entryDetails()
    {
        return $this->hasMany(EntryDetail::class, 'entry_id');
    }
    
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
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
