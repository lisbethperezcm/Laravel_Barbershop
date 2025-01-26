<?php

namespace App\Http\Requests;

use Carbon\Carbon;
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
}
