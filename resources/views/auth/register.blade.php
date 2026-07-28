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
            {{-- PANEL INSTITUCIONAL --}}
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
                            Crea tu acceso al Portal de Gestiones TI.
                        </h1>

                        <p class="mt-5 text-[15px] leading-7 text-slate-600">
                            Registra tu cuenta para reportar problemas técnicos,
                            solicitar servicios y consultar el seguimiento de tus
                            gestiones desde un solo lugar.
                        </p>


                        <div class="mt-9 space-y-5">

                            <div class="flex items-start gap-4">

                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-blue-100 bg-blue-50 text-blue-600">

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

                                <div>

                                    <p class="text-sm font-semibold text-slate-800">
                                        Verificación por correo
                                    </p>

                                    <p class="mt-1 text-xs leading-5 text-slate-500">
                                        Recibirás un código de seis dígitos para
                                        confirmar que el correo te pertenece.
                                    </p>

                                </div>

                            </div>


                            <div class="flex items-start gap-4">

                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-blue-100 bg-blue-50 text-blue-600">

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
                                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"></path>
                                        <path d="M9 12l2 2 4-4"></path>
                                    </svg>

                                </div>

                                <div>

                                    <p class="text-sm font-semibold text-slate-800">
                                        Acceso sin contraseña
                                    </p>

                                    <p class="mt-1 text-xs leading-5 text-slate-500">
                                        Después de verificar tu cuenta podrás iniciar
                                        sesión mediante un enlace seguro.
                                    </p>

                                </div>

                            </div>

                        </div>


                        <div class="mt-10 border-l-2 border-blue-200 pl-4">

                            <p class="text-sm italic leading-6 text-slate-500">
                                Utiliza información válida y una dirección de correo
                                a la que tengas acceso.
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

                            Registro protegido

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
                                Registro sin contraseña
                            </span>

                        </div>

                        <h2 class="mt-5 text-3xl font-semibold tracking-[-0.035em] text-slate-950">
                            Crear una cuenta
                        </h2>

                        <p class="mt-3 text-sm leading-6 text-slate-500">
                            Ingresa tu nombre y correo. Te enviaremos un código
                            para verificar tu cuenta.
                        </p>

                    </div>


                    {{-- Errores --}}

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
                                    No se pudo completar el registro
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


                    {{-- ESTADO DEL ENVÍO DEL CÓDIGO --}}

                    <div
                        id="registerEmailStatus"
                        role="status"
                        aria-live="polite"
                        class="mt-7 hidden items-start gap-3 rounded-xl border px-4 py-3.5"
                    >
                        <div
                            id="registerEmailStatusIcon"
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg"
                        ></div>

                        <div class="min-w-0">

                            <p
                                id="registerEmailStatusTitle"
                                class="text-sm font-semibold"
                            ></p>

                            <p
                                id="registerEmailStatusMessage"
                                class="mt-1 text-xs leading-5"
                            ></p>

                        </div>
                    </div>


                    {{-- Formulario --}}

                    <form
                        method="POST"
                        action="{{ route('register.store') }}"
                        class="mt-8 space-y-5"
                        id="registerForm"
                    >

                        @csrf


                        {{-- Nombre --}}

                        <div>

                            <label
                                for="nombre"
                                class="mb-2 block text-sm font-medium text-slate-700"
                            >
                                Nombre completo
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
                                    id="nombre"
                                    name="nombre"
                                    type="text"
                                    value="{{ old('nombre') }}"
                                    placeholder="Nombre y apellido"
                                    autocomplete="name"
                                    maxlength="200"
                                    required
                                    autofocus
                                    class="block h-12 w-full rounded-xl border bg-white pl-11 pr-4 text-sm text-slate-900 outline-none transition duration-200 placeholder:text-slate-400
                                    @error('nombre')
                                        border-red-400 focus:border-red-500 focus:ring-4 focus:ring-red-100
                                    @else
                                        border-slate-300 hover:border-slate-400 focus:border-blue-600 focus:ring-4 focus:ring-blue-100
                                    @enderror"
                                >

                            </div>

                            @error('nombre')

                                <p class="mt-2 text-xs font-medium text-red-600">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>


                        {{-- Correo --}}

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
                                    name="correo"
                                    type="email"
                                    value="{{ old('correo') }}"
                                    placeholder="nombre@televicentro.com"
                                    autocomplete="email"
                                    inputmode="email"
                                    maxlength="200"
                                    required
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


                        {{-- Explicación --}}

                        <div class="flex items-start gap-3 rounded-xl border border-blue-100 bg-blue-50/70 px-4 py-3.5">

                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white text-blue-600 shadow-sm">

                                <svg
                                    class="h-4 w-4"
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
                                    <path d="M9 14h6"></path>
                                </svg>

                            </div>

                            <p class="text-xs leading-5 text-slate-600">
                                Después de crear la cuenta recibirás un código de seis
                                dígitos. El código vencerá en 5 minutos.
                            </p>

                        </div>


                        {{-- Botón --}}

                        <button
                            type="submit"
                            id="registerButton"
                            class="group flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 text-sm font-semibold text-white shadow-sm transition duration-200 hover:bg-blue-700 hover:shadow-md focus:outline-none focus-visible:ring-4 focus-visible:ring-blue-200 active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-70"
                        >

                            <span id="registerButtonText">
                                Crear cuenta y verificar correo
                            </span>

                            <svg
                                id="registerArrow"
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
                                id="registerSpinner"
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


                    {{-- Login --}}

                    <div class="mt-8 border-t border-slate-200 pt-7">

                        <p class="text-center text-sm text-slate-500">

                            ¿Ya tienes una cuenta?

                            <a
                                href="{{ route('login') }}"
                                class="ml-1 font-semibold text-blue-600 transition hover:text-blue-700 hover:underline"
                            >
                                Iniciar sesión
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
                            Tu cuenta no utilizará contraseña. El acceso se realizará
                            únicamente mediante enlaces enviados a tu correo.
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
    window.authEmailStatusUrl =
        @json(
            url(
                '/auth/email-status/__DELIVERY_ID__'
            )
        );


    document.addEventListener(
        'DOMContentLoaded',
        () => {
            const form =
                document.getElementById(
                    'registerForm'
                );

            const button =
                document.getElementById(
                    'registerButton'
                );

            const buttonText =
                document.getElementById(
                    'registerButtonText'
                );

            const arrow =
                document.getElementById(
                    'registerArrow'
                );

            const spinner =
                document.getElementById(
                    'registerSpinner'
                );

            const statusBox =
                document.getElementById(
                    'registerEmailStatus'
                );

            const statusIcon =
                document.getElementById(
                    'registerEmailStatusIcon'
                );

            const statusTitle =
                document.getElementById(
                    'registerEmailStatusTitle'
                );

            const statusMessage =
                document.getElementById(
                    'registerEmailStatusMessage'
                );


            if (
                !form
                || !button
                || !buttonText
                || !arrow
                || !spinner
            ) {
                return;
            }


            let enviando =
                false;

            let seguimientoActual =
                0;


            form.addEventListener(
                'submit',
                async event => {
                    event.preventDefault();

                    if (
                        enviando
                        || !form.reportValidity()
                    ) {
                        return;
                    }

                    enviando = true;
                    bloquearBoton();

                    mostrarEstado(
                        'queued',
                        'Creando cuenta',
                        'Estamos registrando tu información.'
                    );

                    try {
                        const response =
                            await fetch(
                                form.action,
                                {
                                    method:
                                        'POST',

                                    headers: {
                                        'Accept':
                                            'application/json',

                                        'X-Requested-With':
                                            'XMLHttpRequest',
                                    },

                                    body:
                                        new FormData(
                                            form
                                        ),
                                }
                            );

                        const responseText =
                            await response.text();

                        let data;

                        try {
                            data =
                                JSON.parse(
                                    responseText
                                );

                        } catch {
                            throw new Error(
                                'El servidor devolvió una respuesta inválida.'
                            );
                        }

                        if (
                            !response.ok
                            || data.success !== true
                        ) {
                            throw new Error(
                                obtenerMensajeError(
                                    data
                                )
                            );
                        }

                        const estado =
                            String(
                                data.email?.status
                                ?? ''
                            ).toLowerCase();

                        if (
                            data.email?.sent === true
                            || estado === 'enviado'
                        ) {
                            mostrarEstadoEnviado(
                                data.message
                            );

                            redirigirVerificacion(
                                data.redirect
                            );

                            return;
                        }

                        if (
                            data.email?.failed === true
                            || estado === 'fallido'
                        ) {
                            mostrarEstadoFallido(
                                data.message
                            );

                            redirigirVerificacion(
                                data.redirect,
                                2200
                            );

                            return;
                        }

                        mostrarEstadoCola(
                            data.message
                        );

                        if (
                            data.email?.delivery_id
                        ) {
                            vigilarEstadoCorreo(
                                data.email.delivery_id,
                                data.redirect
                            );

                        } else {
                            redirigirVerificacion(
                                data.redirect
                            );
                        }

                    } catch (error) {
                        mostrarEstado(
                            'error',
                            'No se pudo completar el registro',
                            error?.message
                                ?? 'Revisa la información e intenta nuevamente.'
                        );

                    } finally {
                        enviando = false;
                        restaurarBoton();
                    }
                }
            );


            function bloquearBoton() {
                button.disabled =
                    true;

                buttonText.textContent =
                    'Creando cuenta...';

                arrow.classList.add(
                    'hidden'
                );

                spinner.classList.remove(
                    'hidden'
                );
            }


            function restaurarBoton() {
                button.disabled =
                    false;

                buttonText.textContent =
                    'Crear cuenta y verificar correo';

                arrow.classList.remove(
                    'hidden'
                );

                spinner.classList.add(
                    'hidden'
                );
            }


            function mostrarEstadoCola(
                message
            ) {
                mostrarEstado(
                    'queued',
                    'Código en procesamiento',
                    message
                        ?? 'Tu cuenta fue creada y el código de verificación está siendo enviado.'
                );
            }


            function mostrarEstadoEnviado(
                message
            ) {
                mostrarEstado(
                    'success',
                    'Código enviado',
                    message
                        ?? 'Tu cuenta fue creada y el código de verificación fue enviado.'
                );
            }


            function mostrarEstadoFallido(
                message
            ) {
                mostrarEstado(
                    'warning',
                    'Cuenta creada con advertencia',
                    message
                        ?? 'La cuenta fue creada, pero no pudimos enviar el código. Podrás solicitar uno nuevo.'
                );
            }


            async function vigilarEstadoCorreo(
                deliveryId,
                redirectUrl
            ) {
                if (
                    !deliveryId
                    || !window.authEmailStatusUrl
                ) {
                    redirigirVerificacion(
                        redirectUrl
                    );

                    return;
                }

                const seguimientoId =
                    ++seguimientoActual;

                for (
                    let consulta = 1;
                    consulta <= 20;
                    consulta++
                ) {
                    await esperar(
                        1500
                    );

                    if (
                        seguimientoId
                        !== seguimientoActual
                    ) {
                        return;
                    }

                    try {
                        const url =
                            window.authEmailStatusUrl.replace(
                                '__DELIVERY_ID__',
                                encodeURIComponent(
                                    deliveryId
                                )
                            );

                        const response =
                            await fetch(
                                url,
                                {
                                    headers: {
                                        'Accept':
                                            'application/json',

                                        'X-Requested-With':
                                            'XMLHttpRequest',
                                    },

                                    cache:
                                        'no-store',
                                }
                            );

                        if (!response.ok) {
                            continue;
                        }

                        const data =
                            await response.json();

                        const estado =
                            String(
                                data.email?.status
                                ?? ''
                            ).toLowerCase();

                        if (
                            data.email?.sent === true
                            || estado === 'enviado'
                        ) {
                            mostrarEstadoEnviado();

                            redirigirVerificacion(
                                redirectUrl,
                                900
                            );

                            return;
                        }

                        if (
                            data.email?.failed === true
                            || estado === 'fallido'
                        ) {
                            mostrarEstadoFallido();

                            redirigirVerificacion(
                                redirectUrl,
                                1800
                            );

                            return;
                        }

                        mostrarEstadoCola(
                            estado === 'enviando'
                                ? 'El servidor está enviando tu código de verificación.'
                                : 'El código continúa esperando en la cola de correo.'
                        );

                    } catch (error) {
                        console.warn(
                            'No se pudo consultar el estado del código:',
                            error
                        );
                    }
                }

                mostrarEstadoCola(
                    'El código continúa procesándose en segundo plano.'
                );

                redirigirVerificacion(
                    redirectUrl,
                    1200
                );
            }


            function redirigirVerificacion(
                url,
                delay = 1200
            ) {
                if (!url) {
                    return;
                }

                window.setTimeout(
                    () => {
                        window.location.assign(
                            url
                        );
                    },
                    delay
                );
            }


            function mostrarEstado(
                type,
                title,
                message
            ) {
                const estilos = {
                    queued: {
                        box:
                            'border-blue-200 bg-blue-50',

                        icon:
                            'bg-blue-100 text-blue-600',

                        title:
                            'text-blue-800',

                        message:
                            'text-blue-700',

                        svg:
                            '<svg class="h-4 w-4 animate-pulse" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 2"></path></svg>',
                    },

                    success: {
                        box:
                            'border-emerald-200 bg-emerald-50',

                        icon:
                            'bg-emerald-100 text-emerald-600',

                        title:
                            'text-emerald-800',

                        message:
                            'text-emerald-700',

                        svg:
                            '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 6L9 17l-5-5"></path></svg>',
                    },

                    warning: {
                        box:
                            'border-amber-200 bg-amber-50',

                        icon:
                            'bg-amber-100 text-amber-600',

                        title:
                            'text-amber-800',

                        message:
                            'text-amber-700',

                        svg:
                            '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 9v4"></path><path d="M12 17h.01"></path><path d="M10.3 3.7 2.6 17a2 2 0 0 0 1.7 3h15.4a2 2 0 0 0 1.7-3L13.7 3.7a2 2 0 0 0-3.4 0Z"></path></svg>',
                    },

                    error: {
                        box:
                            'border-red-200 bg-red-50',

                        icon:
                            'bg-red-100 text-red-600',

                        title:
                            'text-red-800',

                        message:
                            'text-red-700',

                        svg:
                            '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="M9 9l6 6M15 9l-6 6"></path></svg>',
                    },
                };

                const estilo =
                    estilos[type]
                    ?? estilos.error;

                statusBox.className =
                    `mt-7 flex items-start gap-3 rounded-xl border px-4 py-3.5 ${estilo.box}`;

                statusIcon.className =
                    `flex h-8 w-8 shrink-0 items-center justify-center rounded-lg ${estilo.icon}`;

                statusIcon.innerHTML =
                    estilo.svg;

                statusTitle.className =
                    `text-sm font-semibold ${estilo.title}`;

                statusTitle.textContent =
                    title;

                statusMessage.className =
                    `mt-1 text-xs leading-5 ${estilo.message}`;

                statusMessage.textContent =
                    message;
            }


            function obtenerMensajeError(
                data
            ) {
                const primerGrupo =
                    data?.errors
                        ? Object.values(
                            data.errors
                        )[0]
                        : null;

                if (
                    Array.isArray(
                        primerGrupo
                    )
                    && primerGrupo[0]
                ) {
                    return primerGrupo[0];
                }

                return data?.message
                    ?? data?.error
                    ?? 'No se pudo completar el registro.';
            }


            function esperar(
                milisegundos
            ) {
                return new Promise(
                    resolve =>
                        window.setTimeout(
                            resolve,
                            milisegundos
                        )
                );
            }
        }
    );
</script>

@endpush