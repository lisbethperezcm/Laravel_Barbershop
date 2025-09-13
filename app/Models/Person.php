<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Person extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'phone_number',
        'address',
        'user_id',
        'updated_by'
    ];
    protected $casts = [
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];


    public function scopeFullNameLike($query, $name)
    {
        return $query->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', '%' . $name . '%');
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
    public function barber()
    {
        return $this->hasOne(Barber::class);
    }

    public function client()
    {
        return $this->hasOne(Client::class);
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
