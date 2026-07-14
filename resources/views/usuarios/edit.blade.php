@extends('layouts.app')


@section('content')


<h1>
Editar usuario
</h1>


<form method="POST"
action="{{ route('usuarios.update',$usuario) }}">


@csrf

@method('PUT')



<input 
name="nombre"
value="{{ $usuario->nombre }}">



<input 
name="correo"
value="{{ $usuario->correo }}">



<select name="rol_id">


@foreach($roles as $rol)

<option 
value="{{ $rol->id }}"
@if($usuario->rol_id == $rol->id)
selected
@endif
>

{{ $rol->nombre }}

</option>


@endforeach


</select>



<input 
type="password"
name="password"
placeholder="Nueva contraseña (opcional)">



<button>
Actualizar
</button>


</form>


@endsection