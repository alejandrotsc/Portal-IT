@extends('layouts.app')

@section('content')

<link rel="stylesheet" href="{{ asset('css/pases.css') }}">
<link rel="stylesheet" href="{{ asset('css/autorizacion.css') }}">

<div class="min-h-screen bg-background">


<form 
    id="documentForm"
    method="POST"
    action="{{ route('memorandos.store') }}">

    @csrf


    <input 
        type="hidden"
        name="tipo_documento"
        value="autorizacion">


    <input
        type="hidden"
        name="tipo_id"
        value="{{ $tipoAutorizacion->id }}">



    <div class="max-w-5xl mx-auto px-6 py-10 space-y-5">


        {{-- TITULO DE PAGINA --}}

        <div class="mb-8">



            <div class="flex items-start justify-between gap-4">


                <div>

                    <h1 class="text-xl font-semibold text-foreground tracking-tight">
                        Generación de memorandos internos
                    </h1>


                    <p class="text-sm text-muted-foreground mt-1">
                        Complete los campos requeridos para generar el documento oficial de autorización.
                    </p>

                </div>



                <span 
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-violet-50 text-violet-700 text-xs font-medium border border-violet-100">

                    <i data-lucide="file-text" class="w-3.5 h-3.5"></i>

                    Borrador

                </span>


            </div>


        </div>





        {{-- TIPO DE GESTIÓN --}}


        <div class="bg-card rounded-2xl border border-border overflow-hidden">


            <div class="px-6 py-4 border-b border-border flex items-center gap-3">


                <span class="w-6 h-6 rounded-full bg-primary text-white text-xs font-semibold flex items-center justify-center">
                    1
                </span>


                <h2 class="text-sm font-semibold text-foreground">
                    Tipo de gestión
                </h2>


            </div>



            <div class="px-6 py-5">

<div class="inline-flex items-center gap-3 px-4 py-3.5 rounded-xl border-2 border-primary bg-primary/5 cursor-pointer">

    {{-- Icono --}}
    <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center">

        <i data-lucide="file-text"
           class="w-4 h-4 text-primary">
        </i>

    </div>


    {{-- Información --}}
    <div class="mr-3">

        <p class="text-sm font-semibold text-primary">
            {{ $tipoAutorizacion->nombre_visual }}
        </p>


        <p class="text-xs text-muted-foreground mt-0.5">
            Solicitud de memorando de autorización
        </p>

    </div>


    {{-- Radio seleccionado --}}
    <div class="w-4 h-4 rounded-full border-2 border-primary bg-primary flex items-center justify-center">

        <div class="w-1.5 h-1.5 rounded-full bg-white"></div>

    </div>


</div>


            </div>


        </div>





        {{-- INFORMACIÓN DOCUMENTO --}}

        @include('memorandos.partials.informacion_documento')





        {{-- FORMULARIO ESPECÍFICO --}}

        @include('memorandos.formularios.autorizacion')





        {{-- OBSERVACIONES --}}

        @include('memorandos.partials.observaciones')




    </div>





    {{-- BOTONES --}}

    <div class="max-w-5xl mx-auto px-6 pb-10 flex justify-end gap-3">


        <button
            type="button"
            id="btnPreview"
            class="flex items-center gap-2 px-5 py-2.5 rounded-xl border border-border bg-white text-sm font-medium text-foreground hover:bg-muted transition">


            <i data-lucide="eye"
               class="w-4 h-4">
            </i>


            Ver preview


        </button>





        <button
            type="submit"
            class="flex items-center gap-2 px-6 py-2.5 rounded-xl bg-primary text-white text-sm font-medium hover:opacity-90 transition">


            <i data-lucide="download"
               class="w-4 h-4">
            </i>


            Generar documento


        </button>



    </div>



</form>


</div>



<script src="{{ asset('js/autorizacion.js') }}"></script>


@endsection