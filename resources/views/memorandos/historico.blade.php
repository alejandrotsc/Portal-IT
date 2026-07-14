@extends('layouts.app')

@section('content')

@vite([
        'resources/css/app.css'
    ])


<div class="historico-container">

    <div class="historico-header">
        <h1>Histórico de Memorandos</h1>
        <p>Consulta y descarga los memorandos generados anteriormente.</p>
    </div>


    @if(count($memorandos) == 0)

        <div class="alert-info">
            No existen memorandos registrados.
        </div>

    @else

        <div class="table-card">

            <table class="historico-table">

                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Tipo</th>
                        <th>Fecha</th>
                        <th>Colaborador</th>
                        <th>Ubicación</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>


                <tbody>

                    @foreach($memorandos as $memo)

                    <tr>

                        <td>
                            <span class="codigo">
                                {{ $memo['codigo'] ?? '-' }}
                            </span>
                        </td>


                        <td>
                            {{ $memo['tipo'] ?? '-' }}
                        </td>


                        <td>
                            {{ $memo['fecha'] ?? '-' }}
                        </td>


                        <td>
                            {{ $memo['datos']['colaborador'] ?? '-' }}
                        </td>


                        <td>
                            {{ $memo['datos']['ubicacion'] ?? '-' }}
                        </td>


                        <td>

                            @php
                                $estado = $memo['estado'] ?? 'Sin estado';
                            @endphp


                            @if($estado == 'Generado')

                                <span class="badge badge-success">
                                    {{ $estado }}
                                </span>

                            @elseif($estado == 'Pendiente')

                                <span class="badge badge-warning">
                                    {{ $estado }}
                                </span>

                            @else

                                <span class="badge badge-default">
                                    {{ $estado }}
                                </span>

                            @endif

                        </td>


                        <td>

                            @if(isset($memo['archivo']))

                                <a 
                                    href="{{ asset('storage/'.$memo['archivo']) }}"
                                    target="_blank"
                                    class="btn-download">

                                    📄 Descargar

                                </a>

                            @else

                                <span class="no-file">
                                    No disponible
                                </span>

                            @endif

                        </td>


                    </tr>

                    @endforeach


                </tbody>


            </table>

        </div>


    @endif


</div>


@endsection