<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetAppointmentsRequest extends FormRequest
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
            'client_id' => 'nullable|exists:clients,id',
            'status_id' => 'nullable|exists:statuses,id',
            'barber_id' => 'nullable|exists:barbers,id',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date',
            'name'       => 'nullable|string|max:100',
        ];
    }
}
