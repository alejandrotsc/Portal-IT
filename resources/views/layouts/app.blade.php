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
    |--------------------------------------------------------------------------
    | Tema inicial
    |--------------------------------------------------------------------------
    |
    | Aplica el tema guardado antes de cargar los estilos para evitar que
    | aparezca brevemente el modo claro.
    |
    --}}

    <script>
        (() => {
            try {
                const savedTheme =
                    localStorage.getItem(
                        'theme'
                    );

                document.documentElement
                    .classList
                    .toggle(
                        'dark',
                        savedTheme === 'dark'
                    );
            } catch (error) {
                console.warn(
                    '[Tema] No fue posible recuperar el tema guardado.',
                    error
                );
            }
        })();
    </script>


    {{--
    |--------------------------------------------------------------------------
    | Tailwind CSS
    |--------------------------------------------------------------------------
    --}}

    <script src="https://cdn.tailwindcss.com"></script>


    {{--
    |--------------------------------------------------------------------------
    | Configuración de Tailwind
    |--------------------------------------------------------------------------
    --}}

    <script>
        tailwind.config = {
            darkMode: 'class',
        };
    </script>


    {{--
    |--------------------------------------------------------------------------
    | Alpine.js
    |--------------------------------------------------------------------------
    --}}

   
{{--
    |--------------------------------------------------------------------------
    | Aplicación principal
    |--------------------------------------------------------------------------
    |
    | Define portalApp() desde el archivo público actual.
    |
    --}}

    <script src="{{ asset('js/portal.js') }}"></script>

    {{--
    |--------------------------------------------------------------------------
    | JavaScript compilado con Vite
    |--------------------------------------------------------------------------
    |
    | Lucide se importa localmente desde resources/js/app.js, por lo que ya
    | no es necesario cargarlo desde un CDN.
    |
    --}}

    @vite([
    'resources/css/app.css',
    'resources/js/app.js',
])


    {{-- Estilos específicos agregados por las vistas --}}

    @stack('styles')
</head>


<body
    class="min-h-screen bg-background text-foreground"
    x-data="portalApp()"
    x-init="init()"
>

    @include('partials.header')


    <main
        class="mx-auto max-w-[1300px]
               space-y-10 px-8 py-10
               lg:px-12"
    >
        @yield('content')
    </main>


    <footer class="mt-16 border-t border-border py-6">

        <div
            class="mx-auto flex max-w-5xl
                   flex-col items-center
                   justify-between gap-2 px-6
                   sm:flex-row"
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



    {{-- Scripts específicos agregados por las vistas --}}

    @stack('scripts')

</body>
</html>