<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReviewRequest extends FormRequest
{
    /**
     * Determinar si el usuario está autorizado a hacer esta request.
     */
    public function authorize(): bool
    {
        return true; // Permitir todas, puedes personalizar con policies
    }

    /**
     * Reglas de validación para las reseñas.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'client_id'        => 'required|integer|exists:clients,id',
            'appointment_id' => 'required|integer|exists:appointments,id|unique:barber_reviews,appointment_id',
            'rating'           => 'required|integer|between:1,5',
            'comment'          => 'nullable|string|max:1000',
        ];
    }

    /**
     * Mensajes de error personalizados.
     */
    public function messages(): array
    {
        return [
            'client_id.integer'          => 'El cliente debe ser un número válido.',
            'client_id.exists'           => 'El cliente seleccionado no existe.',

            'barber_id.integer'          => 'El barbero debe ser un número válido.',
            'barber_id.exists'           => 'El barbero seleccionado no existe.',

            'appointment_date.required'  => 'La fecha de la cita es obligatoria.',
            'appointment_date.date_format' => 'La fecha de la cita debe tener el formato Y-m-d.',

            'rating.required'            => 'La calificación es obligatoria.',
            'rating.between'             => 'La calificación debe estar entre 1 y 5.',

            'comment.string'             => 'El comentario debe ser un texto válido.',
        ];
    }
}
