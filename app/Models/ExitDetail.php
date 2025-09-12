<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExitDetail extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
    'exit_id',
    'product_id', 
    'quantity', 
    'unit_cost'
    ];

    public $timestamps = false;     

    public function inventoryExit()
    {
        return $this->belongsTo(InventoryExit::class, 'exit_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }


}
