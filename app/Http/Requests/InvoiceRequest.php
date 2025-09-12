<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceRequest extends FormRequest
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
        $rules = [];

        if ($this->isMethod('post')) {
            // STORE: productos requeridos y con al menos 1 item
            $rules = [
                'client_id'            => 'required_without:appointment_id|exists:clients,id',
                'appointment_id'       => 'nullable|exists:appointments,id|required_without:products',
                'barber_id'            => 'nullable|exists:barbers,id',
                'products'             => 'nullable|array|required_without:appointment_id',
                'products.*.id'        => 'required_with:products|exists:products,id',
            'products.*.quantity'  => 'required_with:products|integer|min:1',

            ];
        } else {
            // UPDATE: productos opcional, pero si viene:
            // - debe ser array
            // - no puede estar vacío (min:1)
            // - cada item debe ser válido
            $rules = [
                'client_id'            => 'sometimes|exists:clients,id',
                'products' => 'sometimes|array',
                'services' => 'sometimes|array|min:1',
                'services.*.service_id'        => 'required_with:services|exists:services,id',
                'services.*.quantity'  => 'required_with:services|integer|min:1',
            ];
        }
        return $rules;
    }
}
