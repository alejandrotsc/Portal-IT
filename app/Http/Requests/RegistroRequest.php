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
                'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
                'not_regex:/^\s+$/',
            ],


            'username' => [
                'required',
                'string',
                'min:4',
                'max:100',
                'unique:usuarios,username',
                'regex:/^[a-zA-Z0-9._-]+$/',
                'not_regex:/\s/',
            ],


            'correo' => [
                'required',
                'string',
                'email:rfc',
                'max:200',
                'unique:usuarios,correo',
                'regex:/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/',
            ],


            'password' => [
                'required',
                'string',
                'confirmed',
                'min:8',
                'max:255',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*?&#]/',
                'not_regex:/\s/',
            ],

        ];
    }



    public function messages(): array
    {
        return [

            // Nombre

            'nombre.required' =>
                'Debe ingresar el nombre completo.',

            'nombre.min' =>
                'El nombre debe tener al menos 3 caracteres.',

            'nombre.max' =>
                'El nombre no puede superar los 200 caracteres.',

            'nombre.regex' =>
                'El nombre solo puede contener letras y espacios.',

            'nombre.not_regex' =>
                'El nombre no puede estar vacío.',



            // Usuario

            'username.required' =>
                'Debe ingresar un nombre de usuario.',

            'username.min' =>
                'El usuario debe tener mínimo 4 caracteres.',

            'username.max' =>
                'El usuario no puede superar los 100 caracteres.',

            'username.unique' =>
                'El nombre de usuario ya está registrado.',

            'username.regex' =>
                'El usuario solo puede contener letras, números, puntos, guiones y guiones bajos.',

            'username.not_regex' =>
                'El usuario no puede contener espacios.',



            // Correo

            'correo.required' =>
                'Debe ingresar un correo electrónico.',

            'correo.email' =>
                'Debe ingresar un correo electrónico válido.',

            'correo.max' =>
                'El correo no puede superar los 200 caracteres.',

            'correo.unique' =>
                'El correo electrónico ya está registrado.',

            'correo.regex' =>
                'El correo debe estar escrito correctamente y en minúsculas.',



            // Password

            'password.required' =>
                'Debe ingresar una contraseña.',

            'password.confirmed' =>
                'Las contraseñas no coinciden.',

            'password.min' =>
                'La contraseña debe tener al menos 8 caracteres.',

            'password.max' =>
                'La contraseña no puede superar los 255 caracteres.',

            'password.regex' =>
                'La contraseña debe incluir mayúscula, minúscula, número y símbolo.',

            'password.not_regex' =>
                'La contraseña no puede contener espacios.',

        ];
    }



    protected function prepareForValidation(): void
    {
        $this->merge([

            'nombre' =>
                preg_replace('/\s+/', ' ', trim((string) $this->nombre)),


            'username' =>
                strtolower(trim((string) $this->username)),


            'correo' =>
                strtolower(trim((string) $this->correo)),

        ]);
    }
}