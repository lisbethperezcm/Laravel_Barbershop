<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory;
    protected $fillable = ['name'];


public function products()
{
    return $this->hasMany(Product::class);
}

public function appointments()
{
    return $this->hasMany(Appointment::class);
}
public function invoices()
{
    return $this->hasMany(Invoice::class);
}

public function barberDispatches(){

    return $this->hasMany(BarberDispatch::class);
}
}