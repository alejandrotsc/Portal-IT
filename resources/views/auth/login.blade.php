<link
    rel="icon"
    type="image/x-icon"
    href="{{ asset('img/logo-it.ico') }}"
>

@extends('layouts.auth')

@section('content')

<div class="relative min-h-screen overflow-hidden bg-slate-100 text-slate-900">

    {{-- Fondo decorativo --}}
    <div
        class="pointer-events-none absolute inset-0 auth-grid opacity-60"
        aria-hidden="true"
    ></div>

    <div
        class="pointer-events-none absolute -left-40 -top-40 h-[440px] w-[440px] rounded-full bg-blue-200/40 blur-3xl"
        aria-hidden="true"
    ></div>

    <div
        class="pointer-events-none absolute -bottom-52 -right-40 h-[480px] w-[480px] rounded-full bg-indigo-200/30 blur-3xl"
        aria-hidden="true"
    ></div>


    <main class="relative z-10 flex min-h-screen items-center justify-center px-4 py-8 sm:px-6 lg:px-8">

        <div
            class="auth-shell grid w-full max-w-6xl overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-[0_28px_80px_-35px_rgba(15,23,42,0.35)] lg:min-h-[720px] lg:grid-cols-[0.95fr_1.05fr]"
        >

            {{-- ===================================================== --}}
            {{-- PANEL IZQUIERDO --}}
            {{-- ===================================================== --}}

            <aside class="relative hidden overflow-hidden border-r border-slate-200 bg-slate-50 lg:flex lg:flex-col">

                <div
                    class="absolute inset-y-0 left-0 w-1 bg-blue-600"
                    aria-hidden="true"
                ></div>

                <div
                    class="pointer-events-none absolute -right-32 -top-32 h-80 w-80 rounded-full border-[45px] border-blue-100/80"
                    aria-hidden="true"
                ></div>

                <div
                    class="pointer-events-none absolute -bottom-44 -left-44 h-96 w-96 rounded-full border-[60px] border-slate-200/70"
                    aria-hidden="true"
                ></div>


                {{-- Encabezado --}}

                <div class="relative z-10 flex items-center justify-between border-b border-slate-200/80 px-10 py-7 pl-12 xl:px-12 xl:pl-14">

                    <div class="flex items-center gap-4">

                        <img
                            src="{{ asset('img/tvc.png') }}"
                            alt="Logo Televicentro"
                            class="h-11 w-auto object-contain"
                        >

                        <div>

                            <p class="text-sm font-semibold tracking-tight text-slate-900">
                                Portal de Gestiones TI
                            </p>

                            <p class="mt-0.5 text-xs text-slate-500">
                                Plataforma corporativa
                            </p>

                        </div>

                    </div>


                    <div class="flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[11px] font-medium text-emerald-700">

                        <span class="relative flex h-2 w-2">

                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-40"></span>

                            <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>

                        </span>

                        Disponible

                    </div>

                </div>


                {{-- Contenido --}}

                <div class="relative z-10 flex flex-1 items-center px-10 py-12 pl-12 xl:px-12 xl:pl-14">

                    <div class="max-w-md">

                        <span class="text-xs font-semibold uppercase tracking-[0.14em] text-blue-600">
                            Tecnologías de Información
                        </span>

                        <h1 class="mt-5 text-4xl font-semibold leading-[1.15] tracking-[-0.035em] text-slate-950">
                            Una forma más simple de gestionar tus servicios de TI.
                        </h1>

                        <p class="mt-5 text-[15px] leading-7 text-slate-600">
                            Centraliza tus solicitudes, reporta problemas técnicos
                            y consulta el seguimiento de cada gestión desde un solo lugar.
                        </p>

                        <div class="mt-10 border-l-2 border-blue-200 pl-4">

                            <p class="text-sm italic leading-6 text-slate-500">
                                El área de TI trabaja para mantener la continuidad
                                y disponibilidad de los servicios tecnológicos.
                            </p>

                        </div>

                    </div>

                </div>


                {{-- Footer --}}

                <div class="relative z-10 border-t border-slate-200/80 px-10 py-6 pl-12 xl:px-12 xl:pl-14">

                    <div class="flex items-center justify-between gap-4">

                        <p class="text-xs text-slate-400">
                            © {{ date('Y') }} Portal de Gestiones TI
                        </p>

                        <div class="flex items-center gap-2 text-xs text-slate-500">

                            <svg
                                class="h-3.5 w-3.5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"></path>
                                <path d="M9 12l2 2 4-4"></path>
                            </svg>

                            Acceso protegido

                        </div>

                    </div>

                </div>

            </aside>


            {{-- ===================================================== --}}
            {{-- FORMULARIO --}}
            {{-- ===================================================== --}}

            <section class="flex min-h-[680px] items-center justify-center bg-white px-6 py-10 sm:px-10 lg:px-14 xl:px-20">

                <div class="auth-form-content w-full max-w-md">

                    {{-- Marca móvil --}}

                    <div class="mb-10 flex items-center justify-between lg:hidden">

                        <div class="flex items-center gap-3">

                            <img
                                src="{{ asset('img/tvc.png') }}"
                                alt="Logo Televicentro"
                                class="h-9 w-auto object-contain"
                            >

                            <div>

                                <p class="text-sm font-semibold text-slate-900">
                                    Portal de Gestiones TI
                                </p>

                                <p class="text-xs text-slate-500">
                                    Plataforma corporativa
                                </p>

                            </div>

                        </div>

                        <span class="h-2.5 w-2.5 rounded-full bg-emerald-500 shadow-[0_0_0_4px_rgba(16,185,129,0.12)]"></span>

                    </div>


                    {{-- Encabezado --}}

                    <div>

                        <div class="flex items-center gap-2">

                            <span class="h-2 w-2 rounded-full bg-blue-600"></span>

                            <span class="text-xs font-semibold uppercase tracking-[0.12em] text-blue-600">
                                Acceso sin contraseña
                            </span>

                        </div>

                        <h2 class="mt-5 text-3xl font-semibold tracking-[-0.035em] text-slate-950">
                            Bienvenido de nuevo
                        </h2>

                        <p class="mt-3 text-sm leading-6 text-slate-500">
                            Ingresa tu correo electrónico y recibirás un enlace seguro
                            para acceder al Portal TI.
                        </p>

                    </div>


                    {{-- Mensaje de éxito --}}

                    @if(session('success'))

                        <div
                            role="status"
                            class="mt-7 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3.5"
                        >

                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">

                                <svg
                                    class="h-4 w-4"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true"
                                >
                                    <path d="M20 6L9 17l-5-5"></path>
                                </svg>

                            </div>

                            <div>

                                <p class="text-sm font-semibold text-emerald-800">
                                    Solicitud procesada
                                </p>

                                <p class="mt-1 text-xs leading-5 text-emerald-700">
                                    {{ session('success') }}
                                </p>

                            </div>

                        </div>

                    @endif


                    {{-- Errores --}}

                    @if($errors->any())

                        <div
                            role="alert"
                            class="mt-7 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3.5"
                        >

                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-red-100 text-red-600">

                                <svg
                                    class="h-4 w-4"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true"
                                >
                                    <circle cx="12" cy="12" r="9"></circle>
                                    <path d="M12 8v4"></path>
                                    <path d="M12 16h.01"></path>
                                </svg>

                            </div>

                            <div>

                                <p class="text-sm font-semibold text-red-800">
                                    No se pudo procesar la solicitud
                                </p>

                                <ul class="mt-1 space-y-1 text-xs leading-5 text-red-600">

                                    @foreach($errors->all() as $error)

                                        <li>
                                            {{ $error }}
                                        </li>

                                    @endforeach

                                </ul>

                            </div>

                        </div>

                    @endif


                    {{-- Formulario --}}

                    <form
                        method="POST"
                        action="{{ route('login.authenticate') }}"
                        class="mt-8 space-y-6"
                        id="loginForm"
                    >

                        @csrf


                        <div>

                            <label
                                for="correo"
                                class="mb-2 block text-sm font-medium text-slate-700"
                            >
                                Correo electrónico
                            </label>

                            <div class="relative">

                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">

                                    <svg
                                        class="h-[18px] w-[18px]"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        aria-hidden="true"
                                    >
                                        <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                                        <path d="m3 7 9 6 9-6"></path>
                                    </svg>

                                </div>

                                <input
                                    id="correo"
                                    type="email"
                                    name="correo"
                                    value="{{ old('correo') }}"
                                    placeholder="nombre@televicentro.com"
                                    autocomplete="email"
                                    inputmode="email"
                                    maxlength="200"
                                    required
                                    autofocus
                                    class="block h-12 w-full rounded-xl border bg-white pl-11 pr-4 text-sm text-slate-900 outline-none transition duration-200 placeholder:text-slate-400
                                    @error('correo')
                                        border-red-400 focus:border-red-500 focus:ring-4 focus:ring-red-100
                                    @else
                                        border-slate-300 hover:border-slate-400 focus:border-blue-600 focus:ring-4 focus:ring-blue-100
                                    @enderror"
                                >

                            </div>

                            @error('correo')

                                <p class="mt-2 text-xs font-medium text-red-600">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>


                        <button
                            type="submit"
                            id="loginButton"
                            class="group flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 text-sm font-semibold text-white shadow-sm transition duration-200 hover:bg-blue-700 hover:shadow-md focus:outline-none focus-visible:ring-4 focus-visible:ring-blue-200 active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-70"
                        >

                            <span id="loginButtonText">
                                Enviar enlace de acceso
                            </span>

                            <svg
                                id="loginArrow"
                                class="h-4 w-4 transition-transform duration-200 group-hover:translate-x-0.5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >
                                <path d="M5 12h14"></path>
                                <path d="M13 6l6 6-6 6"></path>
                            </svg>

                            <svg
                                id="loginSpinner"
                                class="hidden h-5 w-5 animate-spin"
                                viewBox="0 0 24 24"
                                fill="none"
                                aria-hidden="true"
                            >
                                <circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="9"
                                    stroke="currentColor"
                                    stroke-width="3"
                                ></circle>

                                <path
                                    class="opacity-90"
                                    fill="currentColor"
                                    d="M21 12a9 9 0 0 0-9-9v3a6 6 0 0 1 6 6h3z"
                                ></path>
                            </svg>

                        </button>

                    </form>


                    {{-- Registro --}}

                    <div class="mt-8 border-t border-slate-200 pt-7">

                        <p class="text-center text-sm text-slate-500">

                            ¿Aún no tienes una cuenta?

                            <a
                                href="{{ route('register') }}"
                                class="ml-1 font-semibold text-blue-600 transition hover:text-blue-700 hover:underline"
                            >
                                Crear una cuenta
                            </a>

                        </p>

                    </div>


                    {{-- Información de seguridad --}}

                    <div class="mt-8 flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3.5">

                        <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-white text-slate-500 shadow-sm">

                            <svg
                                class="h-3.5 w-3.5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"></path>
                                <path d="M9 12l2 2 4-4"></path>
                            </svg>

                        </div>

                        <p class="text-xs leading-5 text-slate-500">
                            El enlace será válido durante 5 minutos y podrá utilizarse
                            una sola vez. Nunca solicitaremos tu contraseña.
                        </p>

                    </div>


                    <p class="mt-8 text-center text-xs text-slate-400 lg:hidden">
                        © {{ date('Y') }} Portal de Gestiones TI
                    </p>

                </div>

            </section>

        </div>

    </main>

</div>


<script>

document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('loginForm');

    const button = document.getElementById('loginButton');

    const buttonText = document.getElementById('loginButtonText');

    const arrow = document.getElementById('loginArrow');

    const spinner = document.getElementById('loginSpinner');


    if (!form || !button) {
        return;
    }


    form.addEventListener('submit', function () {

        button.disabled = true;

        buttonText.textContent = 'Enviando enlace...';

        arrow?.classList.add('hidden');

        spinner?.classList.remove('hidden');

    });

});

</script>

@endsection