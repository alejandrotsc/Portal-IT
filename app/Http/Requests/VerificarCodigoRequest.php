<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerificarCodigoRequest extends FormRequest
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
                'string',
                'email:rfc',
                'max:200',
            ],

            'codigo' => [
                'required',
                'digits:6',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'correo.required' =>
                'No se encontró el correo que debe verificarse.',

            'correo.email' =>
                'El correo electrónico no es válido.',

            'codigo.required' =>
                'Debe ingresar el código de verificación.',

            'codigo.digits' =>
                'El código debe contener exactamente 6 dígitos.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'correo' => mb_strtolower(
                trim((string) $this->correo)
            ),

            'codigo' => preg_replace(
                '/\D/',
                '',
                (string) $this->codigo
            ),
        ]);
    }
}