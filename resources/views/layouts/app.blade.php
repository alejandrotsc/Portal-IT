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

    <link
        rel="icon"
        type="image/x-icon"
        href="{{ asset('img/logo-it.ico') }}"
    >

    {{--
        Aplica el tema guardado antes de cargar los estilos.
        Esto evita que aparezca primero el light mode.
    --}}
    <script>
        (() => {
            const savedTheme = localStorage.getItem('theme');

            document.documentElement.classList.toggle(
                'dark',
                savedTheme === 'dark'
            );
        })();
    </script>

    {{-- Tailwind CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Permite controlar dark mode mediante la clase .dark --}}
    <script>
        tailwind.config = {
            darkMode: 'class'
        };
    </script>

    {{-- Estilos compilados del portal --}}
    <link
        rel="stylesheet"
        href="{{ asset('css/app.css') }}"
    >

    {{-- Alpine --}}
    <script
        defer
        src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"
    ></script>

    {{-- Lucide --}}
    <script src="https://unpkg.com/lucide@latest"></script>

    {{-- JavaScript compilado con Vite --}}
    @vite('resources/js/app.js')
</head>

<body
    class="min-h-screen bg-background text-foreground"
    x-data="portalApp()"
    x-init="init()"
>
    @include('partials.header')

    <main
        class="mx-auto max-w-[1300px]
               space-y-10 px-8 py-10 lg:px-12"
    >
        @yield('content')
    </main>

    <footer class="mt-16 border-t border-border py-6">
        <div
            class="mx-auto flex max-w-5xl flex-col
                   items-center justify-between gap-2
                   px-6 sm:flex-row"
        >
            <p class="text-xs text-muted-foreground">
                © {{ date('Y') }} Portal de Gestión TI —
                Área de Tecnología e Información
            </p>

            <p class="text-xs text-muted-foreground">
                v1.0 · Lunes a Viernes 09:00–18:00
            </p>
        </div>
    </footer>

    <script src="{{ asset('js/portal.js') }}"></script>
</body>
</html>