<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    {{-- Necesario para solicitudes POST de Laravel --}}
    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    {{-- Endpoint del chatbot con streaming --}}
    <meta
        name="chatbot-stream-endpoint"
        content="{{ route('chatbot.stream') }}"
    >

    <meta
    name="chatbot-warmup-endpoint"
    content="{{ route('chatbot.warm-up') }}"
    >

    <title>
        @yield('title', 'Portal TI')
    </title>

    <script src="https://cdn.tailwindcss.com"></script>

    <script
        defer
        src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"
    ></script>

    <script src="https://unpkg.com/lucide@latest"></script>

        <link
    rel="icon"
    type="image/x-icon"
    href="{{ asset('img/logo-it.ico') }}"
    >

    <link
        rel="stylesheet"
        href="{{ asset('css/app.css') }}"
    >
</head>



<body 
    class="min-h-screen bg-background"
    x-data="portalApp()"
    x-init="init()"
>


@include('partials.header')


<main class="max-w-[1300px] mx-auto px-8 lg:px-12 py-10 space-y-10">

    @yield('content')

</main>


<footer class="border-t border-border mt-16 py-6">

    <div class="max-w-5xl mx-auto px-6 flex flex-col sm:flex-row items-center justify-between gap-2">

        <p class="text-xs text-muted-foreground">
            © {{ date('Y') }} Portal de Gestión TI — Área de Tecnología e Información
        </p>

        <p class="text-xs text-muted-foreground">
            v2.4.1 · Lunes a Viernes 08:00–18:00
        </p>

    </div>

</footer>


<script src="{{ asset('js/portal.js') }}"></script>
</body>

</html>