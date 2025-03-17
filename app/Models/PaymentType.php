<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentType extends Model
{
    use HasFactory;

    protected $table = 'payment_type';

    public $timestamps = false;   

    protected $fillable = [
        'name',
        'description'
    ];


    public function invoices()
{
    return $this->hasMany(Invoice::class);
}
}
