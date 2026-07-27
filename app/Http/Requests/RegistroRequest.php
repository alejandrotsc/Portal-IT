<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegistroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => [
                'required',
                'string',
                'min:3',
                'max:200',
                'regex:/^[\pL\s.\'-]+$/u',
            ],

            'correo' => [
                'required',
                'string',
                'email:rfc',
                'max:200',

                // Solo dominios corporativos autorizados
                'regex:/^[A-Z0-9._%+\-]+@televicentro\.(com|hn)$/i',

                'unique:usuarios,correo',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' =>
                'Debe ingresar el nombre completo.',

            'nombre.min' =>
                'El nombre debe tener al menos 3 caracteres.',

            'nombre.max' =>
                'El nombre no puede superar los 200 caracteres.',

            'nombre.regex' =>
                'El nombre contiene caracteres no permitidos.',

            'correo.required' =>
                'Debe ingresar un correo electrónico.',

            'correo.email' =>
                'Debe ingresar un correo electrónico válido.',

            'correo.max' =>
                'El correo no puede superar los 200 caracteres.',

            'correo.regex' =>
                'Solo se permiten correos con dominio @televicentro.com o @televicentro.hn',

            'correo.unique' =>
                'El correo electrónico ya está registrado.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $nombre = preg_replace(
            '/\s+/',
            ' ',
            trim((string) $this->nombre)
        );

        $this->merge([
            'nombre' => $nombre,

            'correo' => mb_strtolower(
                trim((string) $this->correo)
            ),
        ]);
    }
}