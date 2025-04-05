<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoginRequest extends FormRequest
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
            'email' => ['required', 'email', 'exists:users,email'],
            'password' => 'required'
        ];
    }

    
    public function messages(): array
    {
        return [
    
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El correo electrónico debe tener un formato válido.',
            'email.exists' => 'El correo electrónico no está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
        ];
    }
    protected function failedValidation(Validator $validator)
{
    $requiredFields = ['email','password'];

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
