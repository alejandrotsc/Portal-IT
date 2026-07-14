@extends('layouts.app')


@section('content')


<h1>
Crear usuario
</h1>


<form method="POST"
action="{{ route('usuarios.store') }}">


@csrf


<input name="nombre" placeholder="Nombre">


<input name="username" placeholder="Usuario">


<input name="correo" placeholder="Correo">


<input type="password" name="password">


<select name="rol_id">


@foreach($roles as $rol)

<option value="{{ $rol->id }}">

{{ $rol->nombre }}

</option>


@endforeach


</select>



<button>
Guardar
</button>



</form>


@endsection