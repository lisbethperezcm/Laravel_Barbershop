<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InventoryEntryRequest extends FormRequest
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
        $rules = [
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity'   => 'required|integer|min:1',
        ];

        if ($this->isMethod('post')) {
            // STORE: productos requeridos y con al menos 1 item
            $rules += [
                'entry_type' => 'required|string',
                'entry_date' => 'required|date',
                'products' => 'required|array|min:1',

            ];
        } else {
            // UPDATE: productos opcional, pero si viene:
            // - debe ser array
            // - no puede estar vacío (min:1)
            // - cada item debe ser válido
            $rules += [
                'products' => 'sometimes|array|min:1',
                'products.*.product_id' => 'required_with:products|exists:products,id',
                'products.*.quantity'   => 'required_with:products|integer|min:1',
            ];
        }

        return $rules;
    }

    public function messages(): array
{
    return [
        // Campos principales
        'entry_type.required' => 'El tipo de entrada es obligatorio.',
        'entry_type.string'   => 'El tipo de entrada debe ser un texto válido.',
        'entry_date.required' => 'La fecha de la entrada es obligatoria.',
        'entry_date.date'     => 'La fecha de la entrada no tiene un formato válido.',

        // Validación del array de productos
        'products.required'   => 'Debes enviar al menos un producto.',
        'products.array'      => 'El campo productos debe ser un arreglo.',
        'products.min'        => 'Debes incluir al menos un producto en la lista.',

        // Productos individuales
        'products.*.product_id.required'      => 'Cada producto debe tener un identificador.',
        'products.*.product_id.required_with' => 'Cada producto debe tener un identificador.',
        'products.*.product_id.exists'        => 'Alguno de los productos seleccionados no existe en el inventario.',

        'products.*.quantity.required'        => 'Cada producto debe tener una cantidad.',
        'products.*.quantity.required_with'   => 'Cada producto debe tener una cantidad.',
        'products.*.quantity.integer'         => 'La cantidad de cada producto debe ser un número entero.',
        'products.*.quantity.min'             => 'La cantidad mínima por producto es 1.',
    ];
}

}
