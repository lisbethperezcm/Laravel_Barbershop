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
        return [
            'appointment_id'       => 'nullable|exists:appointments,id|required_without:products',
            'client_id'            => 'required_without:appointment_id|exists:clients,id',

            'products'             => 'nullable|array|required_without:appointment_id',
            'products.*.id'        => 'required_with:products|exists:products,id',
            'products.*.quantity'  => 'required_with:products|integer|min:1',
        ];
    }
}
