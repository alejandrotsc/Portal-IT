@extends('layouts.auth')


@section('content')


<div class="auth-page">

    <div class="auth-shell">


        <div class="shell-brand">


            <div class="brand-content">


                <div class="brand-mark">

                    <img
                        src="{{ asset('img/tvc.png') }}"
                        class="brand-logo"
                        alt="Logo"
                    >

                </div>


                <h1>
                    Portal de Gestiones TI
                </h1>


                <p>
                    Plataforma centralizada para la gestión
                    de solicitudes tecnológicas.
                </p>


            </div>



            <div class="trust-note">

                <svg width="24" height="24" viewBox="0 0 20 20" fill="none">

                    <path d="M10 1.5l7 2.8v4.2c0 4.7-2.9 8.6-7 9.5-4.1-.9-7-4.8-7-9.5V4.3L10 1.5z"
                    stroke="currentColor"
                    stroke-width="1.3"/>


                    <path d="M7 10l2 2 4-4.5"
                    stroke="currentColor"
                    stroke-width="1.3"
                    stroke-linecap="round"
                    stroke-linejoin="round"/>

                </svg>



                <div>

                    <strong>
                        Sistema protegido
                    </strong>


                    <span>
                        Acceso exclusivo para usuarios autorizados
                    </span>

                </div>


            </div>




            <div class="brand-footer">

                © {{ date('Y') }} PORTAL DE GESTIONES TI

            </div>


        </div>





        <div class="mobile-brand-strip">


            <img
                src="{{ asset('img/tvc.png') }}"
                alt="Logo"
            >


            <span>
                acceso protegido
            </span>


        </div>








        <div class="shell-form">


            <span class="card-eyebrow">
                Acceso interno
            </span>



            <h2>
                Bienvenido
            </h2>



            <p class="subtitle">
                Ingrese sus credenciales para continuar
            </p>






            @if($errors->any())


            <div class="alert error">


                <svg width="16" height="16" viewBox="0 0 16 16" fill="none">

                    <circle cx="8" cy="8" r="7"
                    stroke="currentColor"
                    stroke-width="1.4"/>


                    <path d="M8 5v4M8 11h.01"
                    stroke="currentColor"
                    stroke-width="1.4"
                    stroke-linecap="round"/>

                </svg>



                <span>
                    Por favor, revise los campos marcados.
                </span>


            </div>


            @endif







            <form method="POST"
            action="{{ route('login.authenticate') }}">


                @csrf







                <div class="input-group">


                    <label for="login">
                        Usuario o correo electrónico
                    </label>





                    <div class="input-wrap @error('login') input-invalid @enderror">


                        <svg width="17" height="17" viewBox="0 0 20 20" fill="none">

                            <path d="M17 17c0-3-3.1-5-7-5s-7 2-7 5"
                            stroke="currentColor"
                            stroke-width="1.4"
                            stroke-linecap="round"/>


                            <circle cx="10" cy="6.5" r="3.5"
                            stroke="currentColor"
                            stroke-width="1.4"/>

                        </svg>





                        <input

                            id="login"

                            type="text"

                            name="login"

                            value="{{ old('login') }}"

                            placeholder="usuario@televicentro.com"

                            autocomplete="username"

                            required

                            autofocus

                        >



                    </div>





                    @error('login')

                    <small class="input-error">

                        {{ $message }}

                    </small>

                    @enderror





                </div>

                



                <div class="input-group">


                    <label for="password">

                        Contraseña

                    </label>





                    <div class="input-wrap @error('password') input-invalid @enderror">


                        <svg width="17" height="17" viewBox="0 0 20 20" fill="none">

                            <rect x="4" y="9"
                            width="12"
                            height="8"
                            rx="2"
                            stroke="currentColor"
                            stroke-width="1.4"/>


                            <path d="M6.5 9V6.5a3.5 3.5 0 0 1 7 0V9"
                            stroke="currentColor"
                            stroke-width="1.4"/>

                        </svg>





                        <input

                            id="password"

                            type="password"

                            name="password"

                            placeholder="••••••••"

                            autocomplete="current-password"

                            required

                        >



                    </div>





                    @error('password')

                    <small class="input-error">

                        {{ $message }}

                    </small>

                    @enderror





                </div>









                <div class="forgot">

                    <a href="{{ route('password.request') }}">

                        ¿Olvidaste tu contraseña?

                    </a>


                </div>









                <button class="btn-primary" type="submit">

                    Ingresar

                </button>





            </form>









            <div class="divider">

                <span></span>

                o

                <span></span>

            </div>









            <a
                href="{{ route('register') }}"
                class="btn-secondary"
            >

                Crear una cuenta

            </a>









            <div class="security-note">


                <svg width="14" height="14" viewBox="0 0 16 16" fill="none">

                    <path d="M8 1.5l5.5 2.2v3.3c0 3.7-2.3 6.8-5.5 7.5-3.2-.7-5.5-3.8-5.5-7.5V3.7L8 1.5z"
                    stroke="currentColor"
                    stroke-width="1.2"/>

                </svg>



                Esta plataforma registra intentos de acceso por motivos de seguridad, conforme a la política interna de TI.


            </div>






        </div>





    </div>


</div>



@endsection