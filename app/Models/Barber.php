<?php


namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Barber extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'person_id', // Relación con la tabla Persona
        'status', // 'active' o 'inactive'

    ];
    protected $casts = [
        'deleted_at' => 'datetime',
    ];



    public function getCurrentMonthNetIncome(): float
    {
        $startDate = Carbon::now()->startOfMonth()->toDateString();
        $endDate = Carbon::now()->endOfMonth()->toDateString();
    
        $commissionRate = $this->commission->current_percentage ?? 0;
        $commissionRate = $commissionRate / 100;
    
        $invoices = $this->invoices()
            ->whereHas('appointment', fn($q) => $q->where('status_id', 7)) // Cita completada
            ->where('status_id', 8) // Factura pagada
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->get();
    
        $totalServices = $invoices->sum('total');
        $totalCommission = $totalServices * $commissionRate;
    
        return $totalServices - $totalCommission;
    }
    

    public function person()
    {
        return $this->belongsTo(Person::class);
    }
    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
    public function appointments()
    {

        return $this->hasMany(Appointment::class, 'appointment_service');
    }
    public function commission()
    {
        return $this->hasOne(BarberCommission::class, 'barber_id');
    }

    public function invoices()
    {

        return $this->hasMany(Invoice::class);
    }
    public function barberDispatches()
    {

        return $this->hasMany(BarberDispatch::class);
    }
}
