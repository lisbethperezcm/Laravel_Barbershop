<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EntryDetail extends Model
{
    use HasFactory ,SoftDeletes;

    protected $fillable = [
    'entry_id',
    'product_id', 
    'quantity', 
    'unit_cost'
    ];

    public $timestamps = false;     

    public function inventoryEntry()
    {
        return $this->belongsTo(InventoryEntry::class, 'entry_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }


}
