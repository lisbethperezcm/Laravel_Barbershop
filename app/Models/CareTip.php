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

    public static function getTipsByServices(array $serviceIds)
    {
        return self::whereIn('service_id', $serviceIds)
            ->with('service')
            ->get();
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
