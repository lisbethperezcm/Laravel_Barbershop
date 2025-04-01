<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BarberCommission extends Model
{
    use SoftDeletes, HasFactory;

    protected $table = 'barber_commissions';

    protected $fillable = [
        'barber_id',
        'current_percentage',
        'previous_percentage',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function barber()
    {
        return $this->belongsTo(Barber::class);
    }
}
