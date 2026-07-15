@extends('layouts.app')


@section('content')

<div class="min-h-screen bg-background">

    <main class="max-w-5xl mx-auto px-6 py-8 space-y-6">


        <div class="bg-card rounded-2xl border border-border p-6">

            <h1 class="text-xl font-semibold">
                Incidencia {{ $incidencia->codigo }}
            </h1>


            <div class="mt-5 space-y-3 text-sm">

                <p>
                    <strong>Título:</strong>
                    {{ $incidencia->titulo }}
                </p>


                <p>
                    <strong>Descripción:</strong>
                    {{ $incidencia->descripcion }}
                </p>


                <p>
                    <strong>Estado:</strong>
                    {{ $incidencia->estado }}
                </p>


                <p>
                    <strong>Prioridad:</strong>
                    {{ $incidencia->prioridad }}
                </p>

            </div>

        </div>



        @if($incidencia->archivos->count())

        <div class="bg-card rounded-2xl border border-border p-6">

            <h2 class="font-semibold mb-4">
                Archivos adjuntos
            </h2>


            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

                @foreach($incidencia->archivos as $archivo)

                <div>

                    <img 
                    src="{{ asset('storage/'.$archivo->ruta) }}"
                    class="rounded-xl border w-full h-32 object-cover">


                    <p class="text-xs mt-2">
                        {{ $archivo->nombre_original }}
                    </p>


                </div>


                @endforeach

            </div>

        </div>

        @endif


    </main>

</div>


@endsection