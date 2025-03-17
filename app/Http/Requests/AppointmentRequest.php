<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use App\Models\Appointment;
use Illuminate\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class AppointmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'client_id' => 'nullable|integer|exists:clients,id',
            'barber_id' => 'required|integer|exists:barbers,id',
            'appointment_date' =>'required|date_format:Y-m-d',
            'start_time' => 'required|date_format:H:i:s', // Hora de inicio debe ser válida
            'end_time' => [
            'required',
            'date_format:H:i:s',
            function ($attribute, $value, $fail) {
                $start_time = Carbon::createFromFormat('H:i:s', $this->start_time);
                $end_time = Carbon::createFromFormat('H:i:s', $value);

                // Verificar si la hora de fin es posterior a la de inicio
                if ($end_time <= $start_time) {
                    $fail('La hora de fin debe ser posterior a la hora de inicio.');
                }
            },
        ],
        
            'services' => 'required|array', // Lista de servicios
            'services.*' => 'exists:services,id', // Los IDs de los servicios deben existir
        ];
    }


    public function withValidator(Validator $validator)
{
    
    $validator->after(function ($validator) {
        // Si falta algún campo, no hacer la consulta para evitar errores SQL
        if (!$this->has(['barber_id', 'appointment_date', 'start_time', 'end_time','services'])) {
            return;
        }

        $barberId = $this->barber_id;
        $appointmentDate = $this->appointment_date;
        $start_time = $this->start_time;
        $end_time = $this->end_time;

        $exists = Appointment::where('barber_id', $barberId)
            ->where('appointment_date', $appointmentDate)
            ->where(function ($query) use ($start_time, $end_time) {
                $query->whereBetween('start_time', [$start_time, $end_time])
                      ->orWhereBetween('end_time', [$start_time, $end_time])
                      ->orWhere(function ($query) use ($start_time, $end_time) {
                          $query->where('start_time', '<', $start_time)
                                ->where('end_time', '>', $end_time);
                      });
            })
            ->exists();

        if ($exists) {
            $validator->errors()->add('start_time', 'El barbero ya tiene una cita en este horario.');
        }
    });
}
}
