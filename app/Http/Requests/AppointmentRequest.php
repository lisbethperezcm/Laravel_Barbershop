<?php

namespace App\Http\Requests;

use Log;
use Carbon\Carbon;
use App\Models\Schedule;
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
            'appointment_date' => 'required|date_format:Y-m-d',
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


    //Validaciones para la cita

    public function withValidator(Validator $validator)
    {

        $validator->after(function ($validator) {
            // Si falta algún campo, no hacer la consulta para evitar errores SQL
            if (!$this->has(['barber_id', 'appointment_date', 'start_time', 'end_time', 'services'])) {
                return;
            }

            $barberId = $this->barber_id;
            $appointmentDate =Carbon::parse($this->appointment_date);
            $start_time =Carbon::parse( $this->start_time);
            $end_time = Carbon::parse($this->end_time);

            // Obtener horario del barbero
            $dayOfWeek =$appointmentDate->dayOfWeek;
            $schedule = Schedule::where('barber_id', $barberId)
            ->where('day_id', $dayOfWeek)
            ->first();
          
            if ($schedule) {
                $workStart =Carbon::parse($schedule->start_time);
                $workEnd =Carbon::parse($schedule->end_time);

                // Validar que la cita esté dentro del horario del barbero
                if ($start_time->lt($workStart) || $end_time->gt($workEnd)) {
                    $validator->errors()->add('start_time', 'La cita debe estar dentro del horario del barbero.');
                    return;
                }
            } else {
                // Si no hay horario definido para el barbero, rechazar la cita
                $validator->errors()->add('barber_id', 'El barbero no tiene un horario definido.');
                return;
            }

            
            $exists = Appointment::where('barber_id', $barberId)
                ->where('appointment_date', $appointmentDate)
                ->where(function ($query) use ($start_time, $end_time) {
                    $query->where(function ($q) use ($start_time, $end_time) {
                        //La nueva cita comienza dentro de una cita existente (pero no justo cuando termina)
                        $q->where('start_time', '<', $end_time)
                            ->where('end_time', '>', $start_time);
                    })
                        ->orWhere(function ($q) use ($start_time, $end_time) {
                            //La nueva cita abarca completamente una existente
                            $q->where('start_time', '>=', $start_time)
                                ->where('end_time', '<=', $end_time);
                        });
                })
                ->exists();
            if ($exists) {
                $validator->errors()->add('start_time', 'El barbero ya tiene una cita en este horario.');
            }
        });
    }
}
