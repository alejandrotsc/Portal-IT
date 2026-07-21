@extends('layouts.app')

@section('title', 'Nueva solicitud')

@section('content')

<form
    id="solicitudForm"
    method="POST"
    action="{{ route('solicitudes.store') }}"
>
    @csrf

    <div class="min-h-screen bg-background">

        <main class="max-w-5xl mx-auto px-6 py-10">

            {{-- HEADER --}}
<section class="flex items-start justify-between gap-4 mb-8">

    <div>

        <h1 class="text-xl font-semibold text-foreground">
            Nueva solicitud de servicio
        </h1>

        <p class="text-sm text-muted-foreground mt-1">
            Selecciona el servicio que necesitas y completa la información.
        </p>

    </div>


    <a
        href="{{ route('mis-solicitudes') }}"
        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl
               border border-border text-sm font-medium text-foreground
               hover:bg-muted transition"
    >
        <i data-lucide="history" class="w-4 h-4"></i>

        Mis solicitudes
    </a>

</section>


            {{-- ERRORES DE VALIDACIÓN --}}
            @if($errors->any())

                <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4">

                    <div class="flex items-start gap-3">

                        <i
                            data-lucide="circle-alert"
                            class="w-5 h-5 text-red-600 shrink-0 mt-0.5"
                        ></i>

                        <div>

                            <p class="text-sm font-semibold text-red-700">
                                Revisa la información ingresada
                            </p>

                            <ul class="mt-2 space-y-1 text-xs text-red-600">

                                @foreach($errors->all() as $error)

                                    <li>
                                        {{ $error }}
                                    </li>

                                @endforeach

                            </ul>

                        </div>

                    </div>

                </div>

            @endif


            <div class="space-y-8">

                {{-- PASO 1 --}}
                <section class="bg-card rounded-2xl border border-border overflow-hidden">

                    <div class="px-6 py-4 border-b border-border flex items-center gap-3">

                        <span
                            class="w-6 h-6 rounded-full bg-primary text-white
                                   text-xs font-semibold flex items-center justify-center"
                        >
                            1
                        </span>

                        <h2 class="text-sm font-semibold text-foreground">
                            ¿Qué necesitas hoy?
                        </h2>

                        <span
                            id="cambiarCategoria"
                            class="hidden ml-auto text-xs text-muted-foreground
                                   hover:text-foreground cursor-pointer transition"
                        >
                            Cambiar selección
                        </span>

                    </div>


                    <div class="p-5">

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">

                            @php
                                $categorias = [
                                    [
                                        'id' => 'computadora',
                                        'icon' => 'monitor',
                                        'title' => 'Computadora o accesorios',
                                        'desc' => 'Teclado, mouse, pantalla, audífonos u otro periférico',
                                        'color' => '#2563eb',
                                        'bg' => '#eff6ff',
                                    ],
                                    [
                                        'id' => 'programa',
                                        'icon' => 'package',
                                        'title' => 'Instalar un programa',
                                        'desc' => 'Solicitar instalación de una aplicación en tu equipo',
                                        'color' => '#7c3aed',
                                        'bg' => '#f5f3ff',
                                    ],
                                    [
                                        'id' => 'acceso',
                                        'icon' => 'key-round',
                                        'title' => 'Solicitar un acceso',
                                        'desc' => 'Permisos a sistemas, carpetas o recursos de red',
                                        'color' => '#0891b2',
                                        'bg' => '#ecfeff',
                                    ],
                                    [
                                        'id' => 'vpn',
                                        'icon' => 'wifi',
                                        'title' => 'VPN / Acceso remoto',
                                        'desc' => 'Configurar o solicitar acceso a la red desde fuera de la oficina',
                                        'color' => '#059669',
                                        'bg' => '#ecfdf5',
                                    ],
                                    [
                                        'id' => 'impresora',
                                        'icon' => 'printer',
                                        'title' => 'Impresoras',
                                        'desc' => 'Configurar o conectar impresora',
                                        'color' => '#d97706',
                                        'bg' => '#fffbeb',
                                    ],
                                    [
                                        'id' => 'cuenta',
                                        'icon' => 'shield-check',
                                        'title' => 'Cuenta o contraseña',
                                        'desc' => 'Restablecer contraseña, desbloquear cuenta o acceso al sistema',
                                        'color' => '#dc2626',
                                        'bg' => '#fef2f2',
                                    ],
                                    [
                                        'id' => 'cambio',
                                        'icon' => 'refresh-cw',
                                        'title' => 'Cambio o configuración de equipo',
                                        'desc' => 'Reemplazar equipo o cambiar configuración',
                                        'color' => '#0d9488',
                                        'bg' => '#f0fdfa',
                                    ],
                                    [
                                        'id' => 'otra',
                                        'icon' => 'help-circle',
                                        'title' => 'Otra solicitud',
                                        'desc' => 'Cualquier otra necesidad de TI no listada',
                                        'color' => '#64748b',
                                        'bg' => '#f8fafc',
                                    ],
                                ];
                            @endphp


                            @foreach($categorias as $categoria)

                                <button
                                    type="button"
                                    data-id="{{ $categoria['id'] }}"
                                    data-color="{{ $categoria['color'] }}"
                                    data-bg="{{ $categoria['bg'] }}"
                                    class="categoria-card relative group flex flex-col
                                           items-start gap-3 p-4 rounded-xl border-2
                                           border-border text-left transition-all
                                           duration-200 hover:-translate-y-1
                                           hover:border-primary/40 hover:shadow-lg
                                           hover:shadow-black/10 bg-white cursor-pointer"
                                >

                                    <span
                                        class="check-categoria hidden absolute top-2.5 right-2.5
                                               w-4 h-4 rounded-full bg-primary
                                               items-center justify-center"
                                    >
                                        <i data-lucide="check" class="w-3 h-3 text-white"></i>
                                    </span>


                                    <div
                                        class="icon-container w-9 h-9 rounded-lg
                                               flex items-center justify-center
                                               transition-colors"
                                        style="background-color: {{ $categoria['bg'] }}"
                                    >
                                        <i
                                            data-lucide="{{ $categoria['icon'] }}"
                                            class="w-[17px] h-[17px]"
                                            style="color: {{ $categoria['color'] }}"
                                        ></i>
                                    </div>


                                    <div>

                                        <p class="text-xs font-semibold text-foreground leading-snug">
                                            {{ $categoria['title'] }}
                                        </p>

                                        <p class="text-[11px] text-muted-foreground mt-1 leading-relaxed hidden sm:block">
                                            {{ $categoria['desc'] }}
                                        </p>

                                    </div>

                                </button>

                            @endforeach

                        </div>

                    </div>

                </section>


                {{-- PASO 2 --}}
                <section
                    id="formularioSolicitud"
                    class="hidden bg-card rounded-2xl border border-border overflow-hidden"
                >

                    <div class="px-6 py-4 border-b border-border flex items-center gap-3">

                        <span
                            class="w-6 h-6 rounded-full bg-primary text-white
                                   text-xs font-semibold flex items-center justify-center"
                        >
                            2
                        </span>

                        <h2 class="text-sm font-semibold text-foreground">
                            Cuéntanos un poco más
                        </h2>

                        <span
                            id="categoriaSeleccionada"
                            class="ml-auto hidden text-xs font-medium
                                   px-2.5 py-1 rounded-full border"
                        ></span>

                    </div>


                    <div class="px-6 py-5 space-y-5">

                        <input
                            type="hidden"
                            name="categoria"
                            id="categoria"
                            value="{{ old('categoria') }}"
                        >


                        <div>

                            <label
                                for="asunto"
                                class="block text-xs font-semibold text-muted-foreground
                                       uppercase tracking-widest mb-2"
                            >
                                Asunto

                                <span class="text-primary">
                                    *
                                </span>
                            </label>

                            <input
                                id="asunto"
                                type="text"
                                name="asunto"
                                value="{{ old('asunto') }}"
                                data-required="true"
                                maxlength="255"
                                autocomplete="off"
                                class="w-full px-3.5 py-2.5 rounded-lg border
                                       border-border bg-white text-sm
                                       focus:outline-none focus:border-primary
                                       focus:ring-2 focus:ring-primary/10"
                                placeholder="Describe brevemente tu solicitud"
                            >

                            @error('asunto')

                                <p class="mt-1.5 text-xs text-red-600">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>


                        <div>

                            <label
                                for="descripcion"
                                class="block text-xs font-semibold text-muted-foreground
                                       uppercase tracking-widest mb-2"
                            >
                                Descripción

                                <span class="text-primary">
                                    *
                                </span>
                            </label>

                            <textarea
                                id="descripcion"
                                name="descripcion"
                                data-required="true"
                                rows="4"
                                class="w-full px-3.5 py-2.5 rounded-lg border
                                       border-border bg-white text-sm
                                       focus:outline-none focus:border-primary
                                       focus:ring-2 focus:ring-primary/10 resize-none"
                                placeholder="Cuéntanos qué necesitas y para qué lo necesitas"
                            >{{ old('descripcion') }}</textarea>

                            @error('descripcion')

                                <p class="mt-1.5 text-xs text-red-600">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>


                        {{-- Los campos según la categoría se insertan mediante JS --}}
                        <div
                            id="camposDinamicos"
                            class="space-y-5"
                        ></div>

                    </div>

                </section>


                {{-- BOTONES --}}
<div
    id="accionesSolicitud"
    class="hidden flex justify-end gap-3 pb-10"
>

    <button
        type="button"
        id="btnCancelar"
        class="px-5 py-2.5 rounded-xl border border-border
               text-sm text-muted-foreground hover:bg-muted"
    >
        Cancelar
    </button>


    <button
    type="submit"
    id="btnEnviar"
    class="px-5 py-2.5 rounded-xl bg-primary text-white
           text-sm font-medium flex items-center gap-2
           disabled:opacity-70 disabled:cursor-not-allowed"
>
    <i
        id="btnEnviarIcono"
        data-lucide="mail"
        class="w-4 h-4"
    ></i>

    <span id="btnEnviarTexto">
        Enviar solicitud
    </span>
</button>

</div>

            </div>

        </main>

    </div>

</form>


{{-- MODAL RESPUESTA --}}
@if(session('success'))

    <div
        id="modalSolicitud"
        class="fixed inset-0 bg-black/40 backdrop-blur-sm
               flex items-center justify-center z-50"
    >

        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full mx-5 p-6 text-center">

            <div
                class="w-14 h-14 rounded-full bg-green-100 text-green-600
                       flex items-center justify-center mx-auto mb-4"
            >
                <i data-lucide="check-circle" class="w-8 h-8"></i>
            </div>


            <h3 class="text-lg font-semibold text-gray-900">
                Solicitud enviada
            </h3>


            <p class="text-sm text-gray-500 mt-2">

                {{ session('success') }}

                <br>

                Se notificó al equipo de soporte TI mediante correo.

            </p>


            @if(session('folio'))

                <div
                    class="mt-4 bg-gray-100 rounded-xl py-3
                           text-sm font-semibold text-gray-700"
                >
                    {{ session('folio') }}
                </div>

            @endif


            <button
                type="button"
                id="cerrarModalSolicitud"
                class="mt-6 w-full px-4 py-2.5 rounded-xl
                       bg-primary text-white text-sm font-medium"
            >
                Entendido
            </button>

        </div>

    </div>

@endif


<script src="{{ asset('js/solicitudes.js') }}"></script>


<style>
    .spinner-envio {
        width: 16px;
        height: 16px;
        border: 2px solid rgba(255, 255, 255, 0.4);
        border-top-color: #ffffff;
        border-radius: 50%;
        display: inline-block;
        animation: girar-spinner 0.6s linear infinite;
    }

    @keyframes girar-spinner {
        to {
            transform: rotate(360deg);
        }
    }
</style>

@endsection