<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>
        Portal Gestiones TI
    </title>
    
    <link
    rel="icon"
    type="image/x-icon"
    href="{{ asset('img/logo-it.ico') }}"
    >

    {{-- Tailwind compilado --}}
    <link
        rel="stylesheet"
        href="{{ asset('css/app.css') }}"
    >

    {{-- Estilos exclusivos de autenticación --}}
    <link
        rel="stylesheet"
        href="{{ asset('css/auth.css') }}"
    >

    @stack('styles')

</head>

<body class="m-0 min-h-screen bg-slate-50">

    @yield('content')

    <script
        src="{{ asset('js/app.js') }}"
        defer
    ></script>

    @stack('scripts')

</body>

</html>