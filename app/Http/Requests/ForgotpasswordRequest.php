<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'correo' => [
                'required',
                'email',
                'max:200',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'correo.required' => 'Debe ingresar un correo electrónico.',
            'correo.email' => 'Debe ingresar un correo electrónico válido.',
            'correo.max' => 'El correo no puede superar los 200 caracteres.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'correo' => strtolower(trim((string) $this->correo)),
        ]);
    }
}