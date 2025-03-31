<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InvoiceDetail extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'invoice_id',
        'service_id',
        'product_id',
        'quantity',
        'price'
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
    ];

    public $timestamps = false;
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
