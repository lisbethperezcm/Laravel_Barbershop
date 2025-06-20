<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CareTip extends Model
{
    use HasFactory, SoftDeletes;

     protected $fillable = [
        'service_id',
        'name',
        'description',
        'created_by',
        'updated_by',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
