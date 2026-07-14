@extends('layouts.app')


@section('content')


<h1>
Usuarios
</h1>


<a href="{{ route('usuarios.create') }}">
Nuevo usuario
</a>



<table class="table">


<tr>

<th>
Nombre
</th>

<th>
Correo
</th>

<th>
Rol
</th>

<th>
Estado
</th>

<th>
Acciones
</th>


</tr>



@foreach($usuarios as $usuario)


<tr>


<td>
{{ $usuario->nombre }}
</td>


<td>
{{ $usuario->correo }}
</td>


<td>
{{ $usuario->rol->nombre }}
</td>


<td>

{{ $usuario->activo ? 'Activo':'Inactivo' }}

</td>


<td>


<a href="{{ route('usuarios.edit',$usuario) }}">
Editar
</a>


<form method="POST"
action="{{ route('usuarios.destroy',$usuario) }}">

@csrf
@method('DELETE')


<button>
Eliminar
</button>


</form>



</td>


</tr>


@endforeach



</table>


@endsection