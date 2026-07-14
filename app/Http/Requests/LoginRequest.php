<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Determina si el usuario puede realizar esta solicitud.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación.
     */
    public function rules(): array
    {
        return [
            'login' => [
                'required',
                'string',
                'max:200',
            ],

            'password' => [
                'required',
                'string',
                'max:255',
            ],
        ];
    }

    /**
     * Mensajes personalizados.
     */
    public function messages(): array
    {
        return [
            'login.required' => 'Debe ingresar su usuario o correo electrónico.',
            'login.max' => 'El usuario o correo no puede superar los 200 caracteres.',

            'password.required' => 'Debe ingresar su contraseña.',
            'password.max' => 'La contraseña supera la longitud permitida.',
        ];
    }

    /**
     * Limpieza de datos antes de validar.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'login' => trim((string) $this->login),
        ]);
    }
}