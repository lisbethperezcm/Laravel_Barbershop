<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'description' => 'required|string|max:500',
            'sale_price' => 'required|numeric|min:0',
            'unit_cost' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'itbis' => 'required|numeric|min:0',
            'status_id' => 'required|exists:estatus,id', 
            'created_by' => 'nullable|exists:users,id'
        ];
    }
}
