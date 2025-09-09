<?php

namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class RegisterRequest extends FormRequest
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
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'phone_number' => 'nullable|string|max:15',
        'address' => 'nullable|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'nullable|min:8|confirmed',
        'role_id' => 'required|numeric',
        'commission' => 'nullable|integer|min:0|max:99',
        ];
    }


    public function messages(): array
{
    return [

        'last_name.required' => 'El apellido es obligatorio.',
        'last_name.string' => 'El apellido debe ser un texto.',
        'last_name.max' => 'El apellido no debe superar los 255 caracteres.',

        'phone_number.string' => 'El teléfono debe ser un texto.',
        'phone_number.max' => 'El teléfono no debe superar los 15 caracteres.',

        'address.string' => 'La dirección debe ser un texto.',
        'address.max' => 'La dirección no debe superar los 255 caracteres.',

        'email.required' => 'El correo electrónico es obligatorio.',
        'email.email' => 'El correo debe ser una dirección válida.',
        'email.unique' => 'Este correo ya está registrado.',

        'password.required' => 'La contraseña es obligatoria.',
        'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        'password.confirmed' => 'La confirmación de la contraseña no coincide.',

        'role_id.required' => 'El rol es obligatorio.',
        'role_id.numeric' => 'El rol debe ser un valor numérico.',

        'commission.integer' => 'La comisión debe ser un número entero.',
        'commission.min' => 'La comisión no puede ser menor a 0.',
        'commission.max' => 'La comisión no puede ser mayor a 99.',
    ];
}

protected function failedValidation(Validator $validator)
{
    $requiredFields = ['first_name', 'last_name', 'email', 'password', 'role_id'];

    $failed = $validator->failed(); // Devuelve reglas que fallaron, ejemplo:
    // ['first_name' => ['Required' => []], ...]

    // Verificar si alguna de las reglas fallidas es 'Required' en los campos clave
    $hasRequiredError = collect($requiredFields)->contains(function ($field) use ($failed) {
        return isset($failed[$field]['Required']);
    });

    if ($hasRequiredError) {
        throw new HttpResponseException(response()->json([
            'message' => 'Revisar faltan campos requeridos.',
            'errors' => $validator->errors(),
            'errorCode' => 422
        ], 422));
    }

    // Si no es por campos requeridos, usa la respuesta por defecto
    parent::failedValidation($validator);
}

}
