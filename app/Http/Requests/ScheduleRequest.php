<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ScheduleRequest extends FormRequest
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
    public function rules(): array
    {
        return [
            'schedules' => 'required|array|min:1',
            'schedules.*.day_id' => 'required_with:schedules|integer|between:1,7',
            'schedules.*.start_time' => 'required_with:schedules|date_format:H:i:s|before:schedules.*.end_time',
            'schedules.*.end_time' => 'required_with:schedules|date_format:H:i:s|after:schedules.*.start_time',
        ];
    }


    public function messages(): array
{
    return [
        'schedules.required' => 'Debes enviar al menos un horario.',
        'schedules.array'    => 'El campo horarios debe ser un arreglo válido.',

        'schedules.*.day_id.required_with' => 'Cada horario debe incluir el día de la semana.',
        'schedules.*.day_id.integer'       => 'El identificador del día debe ser un número entero.',
        'schedules.*.day_id.between'       => 'El día debe estar entre 1 (Lunes) y 7 (Domingo).',

        'schedules.*.start_time.required_with' => 'Debes indicar la hora de inicio.',
        'schedules.*.start_time.date_format'   => 'La hora de inicio debe tener el formato HH:MM:SS.',
        'schedules.*.start_time.before'        => 'La hora de inicio debe ser anterior a la hora de fin.',

        'schedules.*.end_time.required_with' => 'Debes indicar la hora de fin.',
        'schedules.*.end_time.date_format'   => 'La hora de fin debe tener el formato HH:MM:SS.',
        'schedules.*.end_time.after'         => 'La hora de fin debe ser posterior a la hora de inicio.',
    ];
}
}
