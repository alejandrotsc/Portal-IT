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
                                Verificación de cuenta
                            </p>

                        </div>

                    </div>


                    <div class="flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[11px] font-medium text-emerald-700">

                        <span class="relative flex h-2 w-2">

                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-40"></span>

                            <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>

                        </span>

                        Enlace activo

                    </div>

                </div>


                {{-- Contenido --}}

                <div class="relative z-10 flex flex-1 items-center px-10 py-12 pl-12 xl:px-12 xl:pl-14">

                    <div class="max-w-md">

                        <span class="text-xs font-semibold uppercase tracking-[0.14em] text-blue-600">
                            Un último paso
                        </span>

                        <h1 class="mt-5 text-4xl font-semibold leading-[1.15] tracking-[-0.035em] text-slate-950">
                            Confirma tu correo para proteger tu cuenta.
                        </h1>

                        <p class="mt-5 text-[15px] leading-7 text-slate-600">
                            Te enviamos un código de seis dígitos a tu correo
                            electrónico. Ingrésalo para completar tu registro
                            y comenzar a usar el Portal TI.
                        </p>

                        <div class="mt-10 border-l-2 border-blue-200 pl-4">

                            <p class="text-sm italic leading-6 text-slate-500">
                                Nunca compartas este código. El equipo de TI
                                no te lo solicitará por ningún medio.
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
                                    Verificación de cuenta
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
                                Confirma tu correo
                            </span>

                        </div>

                        <h2 class="mt-5 text-3xl font-semibold tracking-[-0.035em] text-slate-950">
                            Ingresa el código
                        </h2>

                        <p class="mt-3 text-sm leading-6 text-slate-500">
                            Enviamos un código de seis dígitos a:
                        </p>

                        <p class="mt-1 break-all text-sm font-semibold text-slate-800">
                            {{ $correo }}
                        </p>

                    </div>


                    {{-- Mensaje de éxito --}}

                    @if(session('success'))

                        <div
                            role="status"
                            class="auth-alert mt-7 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3.5"
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
                                    Código enviado
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
                                    No se pudo verificar el código
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


                    {{-- Formulario de verificación --}}

                    <form
                        method="POST"
                        action="{{ route('register.verify') }}"
                        class="mt-8"
                        id="verificationForm"
                    >

                        @csrf

                        <input
                            type="hidden"
                            name="correo"
                            value="{{ $correo }}"
                        >


                        <label
                            for="codigo"
                            class="mb-2 block text-sm font-medium text-slate-700"
                        >
                            Código de verificación
                        </label>


                        <input
                            id="codigo"
                            name="codigo"
                            type="text"
                            value="{{ old('codigo') }}"
                            placeholder="000000"
                            autocomplete="one-time-code"
                            inputmode="numeric"
                            pattern="[0-9]{6}"
                            minlength="6"
                            maxlength="6"
                            required
                            autofocus
                            class="block h-16 w-full rounded-xl border bg-white px-4 text-center text-2xl font-bold tracking-[0.45em] text-slate-900 outline-none transition duration-200 placeholder:text-slate-300
                            @error('codigo')
                                border-red-400 focus:border-red-500 focus:ring-4 focus:ring-red-100
                            @else
                                border-slate-300 hover:border-slate-400 focus:border-blue-600 focus:ring-4 focus:ring-blue-100
                            @enderror"
                        >


                        @error('codigo')

                            <p class="mt-2 text-xs font-medium text-red-600">
                                {{ $message }}
                            </p>

                        @enderror


                        <p class="mt-3 text-xs leading-5 text-slate-500">
                            El código vence en 5 minutos y solamente puede utilizarse una vez.
                        </p>


                        <button
                            type="submit"
                            id="verificationButton"
                            class="group mt-6 flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 text-sm font-semibold text-white shadow-sm transition duration-200 hover:bg-blue-700 hover:shadow-md focus:outline-none focus-visible:ring-4 focus-visible:ring-blue-200 active:scale-[0.99] disabled:cursor-not-allowed disabled:opacity-70"
                        >

                            <span id="verificationButtonText">
                                Verificar y continuar
                            </span>

                            <svg
                                id="verificationArrow"
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
                                id="verificationSpinner"
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


                    {{-- ESTADO DEL REENVÍO --}}

                    <div
                        id="resendEmailStatus"
                        role="status"
                        aria-live="polite"
                        class="mt-7 hidden items-start gap-3 rounded-xl border px-4 py-3.5"
                    >
                        <div
                            id="resendEmailStatusIcon"
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg"
                        ></div>

                        <div class="min-w-0">

                            <p
                                id="resendEmailStatusTitle"
                                class="text-sm font-semibold"
                            ></p>

                            <p
                                id="resendEmailStatusMessage"
                                class="mt-1 text-xs leading-5"
                            ></p>

                        </div>
                    </div>


                    {{-- Reenviar código --}}

                    <div class="mt-8 border-t border-slate-200 pt-7">

                        <p class="text-center text-sm text-slate-500">
                            ¿No recibiste el código?
                        </p>

                        <form
                            method="POST"
                            action="{{ route('register.resend') }}"
                            class="mt-3"
                            id="resendForm"
                        >

                            @csrf

                            <input
                                type="hidden"
                                name="correo"
                                value="{{ $correo }}"
                            >

                            <button
                                type="submit"
                                id="resendButton"
                                class="flex h-11 w-full items-center justify-center rounded-xl border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 transition duration-200 hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus-visible:ring-4 focus-visible:ring-blue-100 disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                Reenviar código
                            </button>

                        </form>

                        <p class="mt-5 text-center text-sm text-slate-500">

                            <a
                                href="{{ route('register') }}"
                                class="font-semibold text-blue-600 transition hover:text-blue-700 hover:underline"
                            >
                                Volver al registro
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
                            Nunca compartas este código. El equipo de TI no te lo solicitará.
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
            transform: translateY(18px) scale(0.985);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }

    }


    @keyframes auth-form-enter {

        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
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
            const codigo =
                document.getElementById(
                    'codigo'
                );

            const verificationForm =
                document.getElementById(
                    'verificationForm'
                );

            const verificationButton =
                document.getElementById(
                    'verificationButton'
                );

            const verificationButtonText =
                document.getElementById(
                    'verificationButtonText'
                );

            const verificationArrow =
                document.getElementById(
                    'verificationArrow'
                );

            const verificationSpinner =
                document.getElementById(
                    'verificationSpinner'
                );

            const resendForm =
                document.getElementById(
                    'resendForm'
                );

            const resendButton =
                document.getElementById(
                    'resendButton'
                );

            const resendStatus =
                document.getElementById(
                    'resendEmailStatus'
                );

            const resendStatusIcon =
                document.getElementById(
                    'resendEmailStatusIcon'
                );

            const resendStatusTitle =
                document.getElementById(
                    'resendEmailStatusTitle'
                );

            const resendStatusMessage =
                document.getElementById(
                    'resendEmailStatusMessage'
                );


            let reenviando =
                false;

            let seguimientoActual =
                0;


            codigo?.addEventListener(
                'input',
                function () {
                    this.value =
                        this.value
                            .replace(
                                /\D/g,
                                ''
                            )
                            .slice(
                                0,
                                6
                            );
                }
            );


            if (
                verificationForm
                && verificationButton
                && verificationButtonText
                && verificationArrow
                && verificationSpinner
            ) {
                verificationForm.addEventListener(
                    'submit',
                    () => {
                        if (
                            !verificationForm.checkValidity()
                        ) {
                            return;
                        }

                        verificationButton.disabled =
                            true;

                        verificationButtonText.textContent =
                            'Verificando...';

                        verificationArrow.classList.add(
                            'hidden'
                        );

                        verificationSpinner.classList.remove(
                            'hidden'
                        );
                    }
                );
            }


            resendForm?.addEventListener(
                'submit',
                async event => {
                    event.preventDefault();

                    if (
                        reenviando
                        || !resendForm.reportValidity()
                    ) {
                        return;
                    }

                    reenviando =
                        true;

                    bloquearReenvio();

                    mostrarEstadoReenvio(
                        'queued',
                        'Preparando nuevo código',
                        'Estamos generando un nuevo código de verificación.'
                    );

                    try {
                        const response =
                            await fetch(
                                resendForm.action,
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
                                            resendForm
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
                            mostrarEstadoReenvio(
                                'success',
                                'Código enviado',
                                data.message
                                    ?? 'Enviamos un nuevo código de verificación.'
                            );

                            return;
                        }

                        if (
                            data.email?.failed === true
                            || estado === 'fallido'
                        ) {
                            mostrarEstadoReenvio(
                                'warning',
                                'No se pudo enviar el código',
                                data.message
                                    ?? 'Puedes intentar reenviarlo nuevamente dentro de unos minutos.'
                            );

                            return;
                        }

                        if (
                            !data.email?.delivery_id
                        ) {
                            mostrarEstadoReenvio(
                                'success',
                                'Solicitud procesada',
                                data.message
                                    ?? 'Si la cuenta está pendiente, recibirá un nuevo código.'
                            );

                            return;
                        }

                        mostrarEstadoReenvio(
                            'queued',
                            'Código en procesamiento',
                            data.message
                                ?? 'El nuevo código fue agregado a la cola de correo.'
                        );

                        vigilarEstadoCorreo(
                            data.email.delivery_id
                        );

                    } catch (error) {
                        mostrarEstadoReenvio(
                            'error',
                            'No se pudo reenviar el código',
                            error?.message
                                ?? 'Intenta nuevamente dentro de unos minutos.'
                        );

                    } finally {
                        reenviando =
                            false;

                        restaurarReenvio();
                    }
                }
            );


            function bloquearReenvio() {
                if (!resendButton) {
                    return;
                }

                resendButton.disabled =
                    true;

                resendButton.textContent =
                    'Procesando...';
            }


            function restaurarReenvio() {
                if (!resendButton) {
                    return;
                }

                resendButton.disabled =
                    false;

                resendButton.textContent =
                    'Reenviar código';
            }


            async function vigilarEstadoCorreo(
                deliveryId
            ) {
                if (
                    !deliveryId
                    || !window.authEmailStatusUrl
                ) {
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
                            mostrarEstadoReenvio(
                                'success',
                                'Código enviado',
                                'El nuevo código de verificación fue enviado a tu correo.'
                            );

                            return;
                        }

                        if (
                            data.email?.failed === true
                            || estado === 'fallido'
                        ) {
                            mostrarEstadoReenvio(
                                'warning',
                                'No se pudo enviar el código',
                                'El envío falló después de los intentos configurados. Puedes solicitar otro código.'
                            );

                            return;
                        }

                        mostrarEstadoReenvio(
                            'queued',
                            estado === 'enviando'
                                ? 'Enviando nuevo código'
                                : 'Código en procesamiento',
                            estado === 'enviando'
                                ? 'El servidor está enviando el nuevo código.'
                                : 'El nuevo código continúa esperando en la cola de correo.'
                        );

                    } catch (error) {
                        console.warn(
                            'No se pudo consultar el estado del reenvío:',
                            error
                        );
                    }
                }

                mostrarEstadoReenvio(
                    'queued',
                    'Código todavía en procesamiento',
                    'El correo continuará procesándose en segundo plano.'
                );
            }


            function mostrarEstadoReenvio(
                type,
                title,
                message
            ) {
                if (
                    !resendStatus
                    || !resendStatusIcon
                    || !resendStatusTitle
                    || !resendStatusMessage
                ) {
                    return;
                }

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

                resendStatus.className =
                    `mt-7 flex items-start gap-3 rounded-xl border px-4 py-3.5 ${estilo.box}`;

                resendStatusIcon.className =
                    `flex h-8 w-8 shrink-0 items-center justify-center rounded-lg ${estilo.icon}`;

                resendStatusIcon.innerHTML =
                    estilo.svg;

                resendStatusTitle.className =
                    `text-sm font-semibold ${estilo.title}`;

                resendStatusTitle.textContent =
                    title;

                resendStatusMessage.className =
                    `mt-1 text-xs leading-5 ${estilo.message}`;

                resendStatusMessage.textContent =
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
                    ?? 'No fue posible reenviar el código.';
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