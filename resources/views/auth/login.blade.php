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

                {{-- Línea azul lateral --}}
                <div
                    class="absolute inset-y-0 left-0 w-1 bg-blue-600"
                    aria-hidden="true"
                ></div>


                {{-- Decoración --}}
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


                    <div
                        class="flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[11px] font-medium text-emerald-700"
                    >

                        <span class="relative flex h-2 w-2">

                            <span
                                class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-40"
                            ></span>

                            <span
                                class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"
                            ></span>

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


                        {{-- Mensaje institucional --}}
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


                        <span
                            class="h-2.5 w-2.5 rounded-full bg-emerald-500 shadow-[0_0_0_4px_rgba(16,185,129,0.12)]"
                        ></span>

                    </div>


                    {{-- Encabezado --}}
                    <div>

                        <div class="flex items-center gap-2">

                            <span class="h-2 w-2 rounded-full bg-blue-600"></span>

                            <span class="text-xs font-semibold uppercase tracking-[0.12em] text-blue-600">
                                Acceso interno
                            </span>

                        </div>


                        <h2 class="mt-5 text-3xl font-semibold tracking-[-0.035em] text-slate-950">
                            Bienvenido de nuevo
                        </h2>


                        <p class="mt-3 text-sm leading-6 text-slate-500">
                            Ingresa tus credenciales corporativas para acceder
                            a tus gestiones de TI.
                        </p>

                    </div>


                    {{-- Error --}}
                    @if($errors->any())

                        <div
                            role="alert"
                            class="auth-alert mt-7 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3.5"
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
                                    No se pudo iniciar sesión
                                </p>

                                <p class="mt-1 text-xs leading-5 text-red-600">
                                    Verifica las credenciales ingresadas e inténtalo nuevamente.
                                </p>

                            </div>

                        </div>

                    @endif


                    <form
                        method="POST"
                        action="{{ route('login.authenticate') }}"
                        class="mt-8 space-y-5"
                        id="loginForm"
                    >

                        @csrf


                        {{-- Usuario --}}
                        <div>

                            <label
                                for="login"
                                class="mb-2 block text-sm font-medium text-slate-700"
                            >
                                Usuario o correo electrónico
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
                                        <path d="M20 21a8 8 0 0 0-16 0"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>

                                </div>


                                <input
                                    id="login"
                                    type="text"
                                    name="login"
                                    value="{{ old('login') }}"
                                    placeholder="usuario@televicentro.com"
                                    autocomplete="username"
                                    required
                                    autofocus
                                    class="block h-12 w-full rounded-xl border bg-white pl-11 pr-4 text-sm text-slate-900 outline-none transition duration-200 placeholder:text-slate-400
                                    @error('login')
                                        border-red-400 focus:border-red-500 focus:ring-4 focus:ring-red-100
                                    @else
                                        border-slate-300 hover:border-slate-400 focus:border-blue-600 focus:ring-4 focus:ring-blue-100
                                    @enderror"
                                >

                            </div>


                            @error('login')

                                <p class="mt-2 text-xs font-medium text-red-600">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>


                        {{-- Contraseña --}}
                        <div>

                            <div class="mb-2 flex items-center justify-between gap-4">

                                <label
                                    for="password"
                                    class="block text-sm font-medium text-slate-700"
                                >
                                    Contraseña
                                </label>


                                <a
                                    href="{{ route('password.request') }}"
                                    class="text-xs font-semibold text-blue-600 transition hover:text-blue-700 hover:underline"
                                >
                                    ¿Olvidaste tu contraseña?
                                </a>

                            </div>


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
                                        <rect x="4" y="10" width="16" height="10" rx="2"></rect>
                                        <path d="M8 10V7a4 4 0 0 1 8 0v3"></path>
                                    </svg>

                                </div>


                                <input
                                    id="password"
                                    type="password"
                                    name="password"
                                    placeholder="Ingresa tu contraseña"
                                    autocomplete="current-password"
                                    required
                                    class="block h-12 w-full rounded-xl border bg-white pl-11 pr-12 text-sm text-slate-900 outline-none transition duration-200 placeholder:text-slate-400
                                    @error('password')
                                        border-red-400 focus:border-red-500 focus:ring-4 focus:ring-red-100
                                    @else
                                        border-slate-300 hover:border-slate-400 focus:border-blue-600 focus:ring-4 focus:ring-blue-100
                                    @enderror"
                                >


                                <button
                                    type="button"
                                    id="togglePassword"
                                    class="absolute inset-y-0 right-0 flex w-12 items-center justify-center rounded-r-xl text-slate-400 transition hover:text-slate-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-blue-500"
                                    aria-label="Mostrar contraseña"
                                    aria-pressed="false"
                                >

                                    <svg
                                        id="eyeOpen"
                                        class="h-[18px] w-[18px]"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        aria-hidden="true"
                                    >
                                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>


                                    <svg
                                        id="eyeClosed"
                                        class="hidden h-[18px] w-[18px]"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        aria-hidden="true"
                                    >
                                        <path d="M3 3l18 18"></path>
                                        <path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"></path>
                                        <path d="M9.9 4.2A10.5 10.5 0 0 1 12 4c6.5 0 10 8 10 8a16.7 16.7 0 0 1-2.1 3.2"></path>
                                        <path d="M6.2 6.2C3.5 8 2 12 2 12s3.5 8 10 8a9.8 9.8 0 0 0 4-.8"></path>
                                    </svg>

                                </button>

                            </div>


                            @error('password')

                                <p class="mt-2 text-xs font-medium text-red-600">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>


                        {{-- Mantener sesión --}}
                        <div class="flex items-center">

                            <label class="inline-flex cursor-pointer items-center gap-2.5">

                                <input
                                    type="checkbox"
                                    name="remember"
                                    value="1"
                                    @checked(old('remember'))
                                    class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                >

                                <span class="text-sm text-slate-600">
                                    Mantener mi sesión iniciada
                                </span>

                            </label>

                        </div>


                        <button
                            type="submit"
                            id="loginButton"
                            class="group flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 text-sm font-semibold text-white shadow-sm transition duration-200 hover:bg-blue-700 hover:shadow-md focus:outline-none focus-visible:ring-4 focus-visible:ring-blue-200 active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-70"
                        >

                            <span id="loginButtonText">
                                Ingresar al portal
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


                    {{-- Seguridad --}}
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
                            Los intentos de acceso son registrados conforme
                            a la política interna de seguridad de TI.
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

@endsection


@push('styles')

<style>

    .auth-grid {
        background-image:
            linear-gradient(
                to right,
                rgba(148, 163, 184, 0.08) 1px,
                transparent 1px
            ),
            linear-gradient(
                to bottom,
                rgba(148, 163, 184, 0.08) 1px,
                transparent 1px
            );

        background-size: 28px 28px;
    }


    .auth-shell {
        animation:
            auth-shell-enter
            0.55s
            cubic-bezier(0.22, 1, 0.36, 1)
            both;
    }


    .auth-form-content {
        animation:
            auth-form-enter
            0.65s
            0.08s
            cubic-bezier(0.22, 1, 0.36, 1)
            both;
    }


    .auth-alert {
        animation:
            auth-alert-enter
            0.3s
            ease-out
            both;
    }


    @keyframes auth-shell-enter {

        from {
            opacity: 0;
            transform:
                translateY(18px)
                scale(0.985);
        }

        to {
            opacity: 1;
            transform:
                translateY(0)
                scale(1);
        }

    }


    @keyframes auth-form-enter {

        from {
            opacity: 0;
            transform: translateX(16px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }

    }


    @keyframes auth-alert-enter {

        from {
            opacity: 0;
            transform: translateY(-5px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }

    }


    @media (prefers-reduced-motion: reduce) {

        .auth-shell,
        .auth-form-content,
        .auth-alert {
            animation: none !important;
        }

        *,
        *::before,
        *::after {
            transition-duration: 0.01ms !important;
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
        }

    }

</style>

@endpush


@push('scripts')

<script>

    document.addEventListener('DOMContentLoaded', function () {

        const passwordInput =
            document.getElementById('password');

        const togglePassword =
            document.getElementById('togglePassword');

        const eyeOpen =
            document.getElementById('eyeOpen');

        const eyeClosed =
            document.getElementById('eyeClosed');


        if (
            passwordInput &&
            togglePassword &&
            eyeOpen &&
            eyeClosed
        ) {

            togglePassword.addEventListener('click', function () {

                const isHidden =
                    passwordInput.type === 'password';


                passwordInput.type =
                    isHidden
                        ? 'text'
                        : 'password';


                eyeOpen.classList.toggle(
                    'hidden',
                    isHidden
                );


                eyeClosed.classList.toggle(
                    'hidden',
                    !isHidden
                );


                togglePassword.setAttribute(
                    'aria-label',
                    isHidden
                        ? 'Ocultar contraseña'
                        : 'Mostrar contraseña'
                );


                togglePassword.setAttribute(
                    'aria-pressed',
                    isHidden
                        ? 'true'
                        : 'false'
                );


                passwordInput.focus();

            });

        }


        const loginForm =
            document.getElementById('loginForm');

        const loginButton =
            document.getElementById('loginButton');

        const loginButtonText =
            document.getElementById('loginButtonText');

        const loginArrow =
            document.getElementById('loginArrow');

        const loginSpinner =
            document.getElementById('loginSpinner');


        if (
            loginForm &&
            loginButton &&
            loginButtonText &&
            loginArrow &&
            loginSpinner
        ) {

            loginForm.addEventListener('submit', function () {

                if (!loginForm.checkValidity()) {
                    return;
                }


                loginButton.disabled = true;

                loginButtonText.textContent =
                    'Verificando acceso...';

                loginArrow.classList.add('hidden');

                loginSpinner.classList.remove('hidden');

            });

        }

    });

</script>

@endpush