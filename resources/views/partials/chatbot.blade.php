{{-- ==========================================================
    ASISTENTE TI - DISEÑO CONVERSACIONAL VIVO

    Compatible con public/js/chatbot.js:
    chatbotWidget(), init(), sendAction(), executeAction(),
    send(), messages, aiMode, loading y draft.
========================================================== --}}

<style>
    @keyframes chatbot-fade-up {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .chatbot-welcome {
        animation: chatbot-fade-up 420ms ease-out both;
    }

    .chatbot-card {
        position: relative;
        isolation: isolate;
        overflow: hidden;
    }

    .chatbot-scrollbar {
        scrollbar-width: thin;
        scrollbar-color: rgba(148, 163, 184, 0.55) transparent;
    }

    .chatbot-scrollbar::-webkit-scrollbar {
        width: 6px;
    }

    .chatbot-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(148, 163, 184, 0.48);
        border-radius: 999px;
    }

    @media (prefers-reduced-motion: reduce) {
        .chatbot-welcome {
            animation: none !important;
        }
    }
</style>

<section>

    <div
        x-data="chatbotWidget({ storageKey: 'portal-it-chatbot-history-{{ auth()->id() ?? 'guest' }}' })"
        class="group/chatbot relative overflow-hidden rounded-2xl border border-border bg-card shadow-sm transition-all duration-300 hover:border-primary/15 hover:shadow-lg hover:shadow-blue-500/5 dark:border-slate-700/70 dark:bg-slate-900/70 dark:hover:border-blue-700/60 dark:hover:shadow-black/20"
    >

        {{-- ==================================================
            CABECERA
        =================================================== --}}

        <header
            class="relative flex flex-col gap-3 overflow-hidden border-b border-border bg-gradient-to-r from-primary/[0.055] via-white to-blue-50/40 px-5 py-4 dark:border-slate-700/70 dark:from-blue-950/30 dark:via-slate-900 dark:to-slate-900 sm:flex-row sm:items-center sm:justify-between"
        >

            <span class="absolute -right-12 -top-16 h-40 w-40 rounded-full bg-primary/10 blur-3xl pointer-events-none transition-all duration-500 group-hover/chatbot:bg-primary/20 motion-safe:group-hover/chatbot:scale-125"></span>

            <div class="relative z-10 flex items-center gap-3">

                <div
                    class="relative flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl border border-blue-200 bg-blue-50 text-blue-600 dark:border-blue-800 dark:bg-blue-950/60 dark:text-blue-400 shadow-sm transition-all duration-300 group-hover/chatbot:border-blue-300 group-hover/chatbot:bg-blue-100 dark:group-hover/chatbot:border-blue-700 dark:group-hover/chatbot:bg-blue-900/60 motion-safe:group-hover/chatbot:scale-105"
                >
                    <i
                        data-lucide="bot-message-square"
                        class="h-[18px] w-[18px] transition-transform duration-300 motion-safe:group-hover/chatbot:scale-110"
                    ></i>

                    <i
                        data-lucide="sparkles"
                        class="absolute right-1 top-1 h-2 w-2 text-blue-400"
                    ></i>

                    <span class="absolute -bottom-1 -right-1 flex h-3 w-3">
                        <span class="absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-60 animate-ping"></span>
                        <span class="relative inline-flex h-3 w-3 rounded-full border-2 border-white bg-emerald-500 dark:border-slate-900"></span>
                    </span>
                </div>

                <div>

                    <div class="flex items-center gap-2">
                        <h2 class="text-sm font-semibold text-foreground">
                            Asistente TI
                        </h2>

                    </div>

                    <p class="mt-0.5 text-xs text-muted-foreground">
                        Orientación y acceso a gestiones del Portal TI
                    </p>

                </div>

            </div>


            <div
                class="relative z-10 inline-flex w-fit items-center gap-1.5 rounded-lg border border-border bg-white/80 px-2.5 py-1.5 text-[11px] font-medium text-muted-foreground shadow-sm backdrop-blur dark:border-slate-700 dark:bg-slate-800/80 dark:text-slate-300"
            >
                <i
                    :data-lucide="aiMode ? 'message-square-text' : 'git-branch'"
                    class="h-3 w-3"
                ></i>

                <span
                    x-text="aiMode ? 'Consulta libre' : 'Asistencia guiada'"
                ></span>
            </div>

        </header>


        {{-- ==================================================
            CONVERSACIÓN
        =================================================== --}}

        <div
            id="chatbot-messages"
            x-ref="messages"
            class="chatbot-scrollbar min-h-[430px] max-h-[560px] space-y-5 overflow-y-auto bg-muted/30 dark:bg-slate-950/35 px-5 py-5 sm:px-6"
            aria-live="polite"
        >

            {{-- MENSAJE INICIAL --}}

            <div
                x-show="messages.length === 0"
                class="chatbot-welcome flex items-start gap-3"
            >

                <div
                    class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg border border-blue-200 bg-blue-50 text-blue-600 dark:border-blue-800 dark:bg-blue-950/60 dark:text-blue-400 shadow-sm"
                >
                    <i
                        data-lucide="bot"
                        class="h-4 w-4"
                    ></i>
                </div>


                <div class="w-full max-w-2xl">

                    <div
                        class="rounded-2xl rounded-tl-md border border-border bg-card px-4 py-3 shadow-sm dark:border-slate-700/70 dark:bg-slate-900"
                    >
                        <p class="text-sm leading-relaxed text-foreground">
                            Hola{{ auth()->check() ? ', '.explode(' ', trim(auth()->user()->nombre))[0] : '' }}.
                            Soy el asistente del Portal TI. ¿Qué necesitas gestionar hoy?
                        </p>
                    </div>


                    {{-- Menú principal unificado --}}

                    <div class="mt-3 grid grid-cols-1 items-stretch gap-2.5 sm:grid-cols-2">

                        <button
                            type="button"
                            @click="sendAction('problema.menu', 'Reportar un problema')"
                            :disabled="loading"
                            class="chatbot-card group/action grid min-h-[68px] w-full grid-cols-[36px_minmax(0,1fr)] items-start gap-3 rounded-xl border border-border bg-card p-3 text-left shadow-sm transition-all duration-300 hover:border-rose-200 hover:shadow-md dark:border-slate-700/70 dark:bg-slate-900 dark:hover:border-rose-800 dark:hover:bg-rose-950/15 motion-safe:hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-rose-100 text-rose-600 dark:bg-rose-950/45 dark:text-rose-300 transition-all duration-300 motion-safe:group-hover/action:scale-105">
                                <i data-lucide="triangle-alert" class="h-4 w-4 transition-transform duration-300 motion-safe:group-hover/action:scale-110"></i>
                            </span>

                            <span class="min-w-0 pt-0.5">
                                <span class="block text-xs font-semibold leading-5 text-foreground">
                                    Reportar un problema
                                </span>

                                <span class="mt-0.5 block text-[11px] leading-[1.45] text-muted-foreground">
                                    Algo no funciona correctamente
                                </span>
                            </span>
                        </button>


                        <button
                            type="button"
                            @click="sendAction('solicitud.menu', 'Solicitar un servicio')"
                            :disabled="loading"
                            class="chatbot-card group/action grid min-h-[68px] w-full grid-cols-[36px_minmax(0,1fr)] items-start gap-3 rounded-xl border border-border bg-card p-3 text-left shadow-sm transition-all duration-300 hover:border-cyan-200 hover:shadow-md dark:border-slate-700/70 dark:bg-slate-900 dark:hover:border-cyan-800 dark:hover:bg-cyan-950/15 motion-safe:hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-cyan-100 text-cyan-700 dark:bg-cyan-950/45 dark:text-cyan-300 transition-all duration-300 motion-safe:group-hover/action:scale-105">
                                <i data-lucide="clipboard-list" class="h-4 w-4 transition-transform duration-300 motion-safe:group-hover/action:scale-110"></i>
                            </span>

                            <span class="min-w-0 pt-0.5">
                                <span class="block text-xs font-semibold leading-5 text-foreground">
                                    Solicitar un servicio
                                </span>

                                <span class="mt-0.5 block text-[11px] leading-[1.45] text-muted-foreground">
                                    Equipos, programas o accesos
                                </span>
                            </span>
                        </button>


                        <button
                            type="button"
                            @click="sendAction('pase.menu', 'Gestionar un pase')"
                            :disabled="loading"
                            class="chatbot-card group/action grid min-h-[68px] w-full grid-cols-[36px_minmax(0,1fr)] items-start gap-3 rounded-xl border border-border bg-card p-3 text-left shadow-sm transition-all duration-300 hover:border-indigo-200 hover:shadow-md dark:border-slate-700/70 dark:bg-slate-900 dark:hover:border-indigo-800 dark:hover:bg-indigo-950/15 motion-safe:hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-950/45 dark:text-indigo-300 transition-all duration-300 motion-safe:group-hover/action:scale-105">
                                <i data-lucide="badge-check" class="h-4 w-4 transition-transform duration-300 motion-safe:group-hover/action:scale-110"></i>
                            </span>

                            <span class="min-w-0 pt-0.5">
                                <span class="block text-xs font-semibold leading-5 text-foreground">
                                    Gestionar un pase
                                </span>

                                <span class="mt-0.5 block text-[11px] leading-[1.45] text-muted-foreground">
                                    Pase menor o mayor a 24 horas
                                </span>
                            </span>
                        </button>


                        <button
                            type="button"
                            @click="sendAction('gestion.estado', 'Consultar mis gestiones')"
                            :disabled="loading"
                            class="chatbot-card group/action grid min-h-[68px] w-full grid-cols-[36px_minmax(0,1fr)] items-start gap-3 rounded-xl border border-border bg-card p-3 text-left shadow-sm transition-all duration-300 hover:border-sky-200 hover:shadow-md dark:border-slate-700/70 dark:bg-slate-900 dark:hover:border-sky-800 dark:hover:bg-sky-950/15 motion-safe:hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-sky-100 text-sky-600 dark:bg-sky-950/45 dark:text-sky-300 transition-all duration-300 motion-safe:group-hover/action:scale-105">
                                <i data-lucide="history" class="h-4 w-4 transition-transform duration-300 motion-safe:group-hover/action:scale-110"></i>
                            </span>

                            <span class="min-w-0 pt-0.5">
                                <span class="block text-xs font-semibold leading-5 text-foreground">
                                    Consultar mis gestiones
                                </span>

                                <span class="mt-0.5 block text-[11px] leading-[1.45] text-muted-foreground">
                                    Revisa estados y seguimientos
                                </span>
                            </span>
                        </button>


                        <button
                            type="button"
                            @click="sendAction('ai.enable', 'Escribir otra consulta')"
                            :disabled="loading"
                            class="chatbot-card group/action grid min-h-[68px] w-full grid-cols-[36px_minmax(0,1fr)] items-start gap-3 rounded-xl border border-dashed border-border bg-card p-3 text-left shadow-sm transition-all duration-300 hover:border-violet-200 hover:shadow-md dark:border-slate-700/70 dark:bg-slate-900 dark:hover:border-violet-800 dark:hover:bg-violet-950/15 motion-safe:hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-50 sm:col-span-2"
                        >
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-violet-100 text-violet-600 dark:bg-violet-950/45 dark:text-violet-300 transition-all duration-300 motion-safe:group-hover/action:scale-105">
                                <i data-lucide="message-square-text" class="h-4 w-4 transition-transform duration-300 motion-safe:group-hover/action:scale-110"></i>
                            </span>

                            <span class="min-w-0 pt-0.5">
                                <span class="block text-xs font-semibold leading-5 text-foreground">
                                    Escribir otra consulta
                                </span>

                                <span class="mt-0.5 block text-[11px] leading-[1.45] text-muted-foreground">
                                    Describe con tus propias palabras lo que necesitas
                                </span>
                            </span>
                        </button>

                    </div>
                </div>

            </div>


            {{-- ==================================================
                MENSAJES DINÁMICOS
            =================================================== --}}

            <template
                x-for="(msg, index) in messages"
                :key="msg.id ?? index"
            >

                <div
                    class="flex items-start gap-3"
                    :class="msg.from === 'user' ? 'flex-row-reverse' : ''"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                >

                    {{-- Avatar --}}

                    <div
                        class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-xl border shadow-sm"
                        :class="
                            msg.from === 'user'
                                ? 'border-blue-600 bg-blue-600 text-white'
                                : 'border-blue-200 bg-blue-50 text-blue-600 dark:border-blue-800 dark:bg-blue-950/60 dark:text-blue-400'
                        "
                    >
                        <i
                            :data-lucide="msg.from === 'user' ? 'user-round' : 'bot'"
                            class="h-4 w-4"
                        ></i>
                    </div>


                    <div
                        class="max-w-[88%] sm:max-w-2xl"
                        :class="msg.from === 'user' ? 'items-end' : 'items-start'"
                    >

                        {{-- Burbuja --}}

                        <div
                            class="rounded-2xl border px-4 py-3 shadow-[0_5px_16px_rgba(15,23,42,0.055)]"
                            :class="
                                msg.from === 'user'
                                    ? 'rounded-tr-md border-primary bg-primary text-white'
                                    : 'rounded-tl-md border-border bg-card text-foreground dark:border-slate-700/70 dark:bg-slate-900 dark:text-slate-200'
                            "
                        >

                            <p
                                x-show="msg.text"
                                class="whitespace-pre-line text-sm leading-relaxed"
                                x-text="msg.text"
                            ></p>


                            {{-- Estado de carga --}}

                            <div
                                x-show="msg.from === 'bot' && msg.streaming && !msg.text"
                                class="flex items-center gap-1.5 py-1 text-muted-foreground"
                                aria-label="El asistente está escribiendo"
                            >
                                <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-current opacity-60"></span>
                                <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-current opacity-60" style="animation-delay: 120ms"></span>
                                <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-current opacity-60" style="animation-delay: 240ms"></span>
                            </div>


                            {{-- Gestiones encontradas --}}

                            <template
                                x-if="!msg.streaming && msg.items && msg.items.length"
                            >
                                <div class="mt-3 space-y-2">

                                    <template
                                        x-for="(item, i) in msg.items"
                                        :key="item.id ?? i"
                                    >
                                        <a
                                            :href="item.url"
                                            class="group/item block rounded-xl border border-border bg-muted/30 p-3 shadow-sm transition-all duration-200 hover:border-primary/30 hover:bg-primary/[0.03] hover:shadow-md dark:border-slate-700/70 dark:bg-slate-950/30 dark:hover:border-blue-700/60 dark:hover:bg-blue-950/20 motion-safe:hover:-translate-y-0.5"
                                        >
                                            <div class="flex items-start justify-between gap-3">

                                                <div class="min-w-0">
                                                    <p
                                                        class="text-xs font-semibold text-foreground"
                                                        x-text="item.tipo ?? item.type ?? 'Gestión'"
                                                    ></p>

                                                    <p
                                                        class="mt-1 truncate text-[11px] text-muted-foreground"
                                                        x-text="item.title ?? item.titulo ?? item.codigo ?? ''"
                                                    ></p>
                                                </div>

                                                <div class="flex flex-shrink-0 items-center gap-2">
                                                    <span
                                                        class="rounded-full border border-border bg-card px-2 py-0.5 text-[10px] font-medium text-muted-foreground dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
                                                        x-text="item.status ?? item.estado ?? ''"
                                                    ></span>

                                                    <i
                                                        data-lucide="external-link"
                                                        class="h-3.5 w-3.5 text-muted-foreground transition-transform duration-200 group-hover/item:translate-x-0.5"
                                                    ></i>
                                                </div>

                                            </div>
                                        </a>
                                    </template>

                                </div>
                            </template>


                            {{-- Redirección --}}

                            <template
                                x-if="!msg.streaming && msg.redirect"
                            >
                                <a
                                    :href="msg.redirect.url"
                                    class="group/redirect mt-3 inline-flex items-center gap-2 rounded-lg bg-primary px-3 py-2 text-xs font-semibold text-white shadow-sm transition-all duration-200 hover:bg-primary/90 hover:shadow-md motion-safe:hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.98]"
                                >
                                    <span x-text="msg.redirect.label"></span>
                                    <i data-lucide="external-link" class="h-3.5 w-3.5 transition-transform duration-200 group-hover/redirect:translate-x-0.5"></i>
                                </a>
                            </template>


                            {{-- Nota de consulta automática --}}

                            <template
                                x-if="msg.ai && !msg.streaming"
                            >
                                <div
                                    class="mt-3 flex items-start gap-1.5 border-t border-border/70 pt-2 text-[10px] leading-relaxed text-muted-foreground dark:border-slate-700/70 dark:text-slate-400"
                                >
                                    <i data-lucide="info" class="mt-0.5 h-3 w-3 flex-shrink-0"></i>
                                    <span>
                                        Orientación generada automáticamente. Verifica los datos importantes.
                                    </span>
                                </div>
                            </template>


                            {{-- Información local de depuración --}}

                            @if(app()->environment('local'))
                                <template
                                    x-if="msg.intent && !msg.streaming"
                                >
                                    <div class="mt-3 text-[10px] text-muted-foreground">
                                        Intent:
                                        <span x-text="msg.intent.name ?? 'N/A'"></span>

                                        <template x-if="msg.intent.action">
                                            <span>
                                                · Acción:
                                                <span x-text="msg.intent.action"></span>
                                            </span>
                                        </template>
                                    </div>
                                </template>
                            @endif

                        </div>


                        {{-- Acciones rápidas fuera de la burbuja --}}

                        <template
                            x-if="!msg.streaming && msg.quick_actions && msg.quick_actions.length"
                        >
                            <div class="mt-2 grid grid-cols-1 gap-2 sm:grid-cols-2">

                                <template
                                    x-for="(action, i) in msg.quick_actions"
                                    :key="action.value ?? action.url ?? action.label ?? i"
                                >
                                    <button
                                        type="button"
                                        @click="executeAction(action, msg)"
                                        :disabled="loading"
                                        class="chatbot-card group/action flex items-center gap-2.5 rounded-xl border bg-card p-3 text-left shadow-sm transition-all duration-300 hover:shadow-md dark:bg-slate-900 dark:hover:bg-slate-800/70 motion-safe:hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-50"
                                        :class="chatbotActionAppearance(action).buttonClass"
                                    >
                                        <span
                                            class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg transition-all duration-300 motion-safe:group-hover/action:scale-105"
                                            :class="chatbotActionAppearance(action).iconClass"
                                        >
                                            <i
                                                :data-lucide="chatbotResolvedActionIcon(action)"
                                                class="h-3.5 w-3.5 transition-transform duration-300 motion-safe:group-hover/action:scale-110"
                                            ></i>
                                        </span>

                                        <span class="min-w-0 flex-1">
                                            <span
                                                class="block text-xs font-semibold leading-relaxed text-foreground"
                                                x-text="action.label"
                                            ></span>

                                            <span
                                                x-show="action.description"
                                                class="mt-0.5 block text-[11px] leading-relaxed text-muted-foreground"
                                                x-text="action.description"
                                            ></span>
                                        </span>

                                    </button>
                                </template>

                            </div>
                        </template>

                    </div>

                </div>

            </template>

        </div>


        {{-- ==================================================
            COMPOSITOR
        =================================================== --}}

        <footer class="border-t border-border bg-card px-5 py-4 dark:border-slate-700/70 dark:bg-slate-900/90 sm:px-6">

            <div class="mb-2 flex items-center justify-between gap-3">

                <p
                    class="text-[11px] text-muted-foreground"
                    x-text="
                        aiMode
                            ? 'Describe tu consulta en un máximo de 500 caracteres.'
                            : 'Selecciona una opción de la conversación para continuar.'
                    "
                ></p>

                <div class="flex flex-shrink-0 items-center gap-3">

                    <button
                        type="button"
                        @click="if (window.confirm('¿Deseas iniciar una conversación nueva?')) { startNewConversation() }"
                        :disabled="loading || messages.length === 0"
                        class="inline-flex items-center gap-1 text-[11px] font-medium text-muted-foreground transition-colors hover:text-foreground disabled:cursor-not-allowed disabled:opacity-40"
                    >
                        <i data-lucide="refresh-cw" class="h-3 w-3"></i>
                        Nueva conversación
                    </button>

                    <button
                        type="button"
                        @click="sendAction('menu.principal', 'Mostrar menú')"
                        :disabled="loading"
                        class="inline-flex items-center gap-1 text-[11px] font-medium text-primary hover:underline disabled:opacity-50"
                    >
                        <i data-lucide="layout-grid" class="h-3 w-3"></i>
                        Menú principal
                    </button>

                </div>

            </div>


            <form
    @submit.prevent="if (aiMode) { send() }"
    class="group/composer flex items-center gap-3 rounded-2xl border
           px-3.5 py-3 shadow-sm transition-all duration-300
           dark:!border-slate-700 dark:!bg-slate-900
           dark:focus-within:!border-blue-500
           dark:focus-within:!ring-4
           dark:focus-within:!ring-blue-500/15"
    :class="
        aiMode
            ? 'border-primary/40 bg-card focus-within:border-primary focus-within:ring-4 focus-within:ring-primary/10'
            : 'border-border bg-muted/40'
    "
>
    <span
    class="inline-flex flex-shrink-0 transition-all duration-200
           group-focus-within/composer:text-blue-600
           dark:group-focus-within/composer:text-blue-400
           motion-safe:group-focus-within/composer:scale-110"
    :class="
        aiMode
            ? 'text-primary dark:text-blue-400'
            : 'text-muted-foreground'
    "
>
    <i
        data-lucide="message-circle"
        class="h-4 w-4"
    ></i>
</span>

    <input
        x-ref="input"
        x-model="draft"
        type="text"
        maxlength="500"
        autocomplete="off"
        :disabled="loading || !aiMode"
        :placeholder="
            aiMode
                ? 'Escribe aquí tu consulta...'
                : 'Activa la consulta libre para escribir un mensaje'
        "
        class="min-w-0 flex-1 border-0 bg-transparent text-sm
               text-foreground outline-none
               placeholder:text-muted-foreground
               focus:outline-none focus:ring-0
               dark:text-slate-200 dark:placeholder:text-slate-500
               disabled:cursor-not-allowed disabled:opacity-60"
    >

    <button
        type="submit"
        :disabled="loading || !aiMode || !draft.trim()"
        class="group/send flex h-9 w-9 flex-shrink-0 items-center
               justify-center rounded-xl bg-primary text-white shadow-sm
               transition-all duration-200 hover:bg-primary/90
               hover:shadow-md motion-safe:hover:-translate-y-0.5
               active:translate-y-0 active:scale-[0.95]
               disabled:cursor-not-allowed disabled:opacity-40
               disabled:hover:translate-y-0 disabled:hover:shadow-sm"
        aria-label="Enviar consulta"
    >
        <i
            data-lucide="send"
            class="h-3.5 w-3.5 transition-transform duration-200"
        ></i>
    </button>
</form>

            </form>


            <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-2">

                <button
                    type="button"
                    @click="sendAction('problema.menu', 'Tengo un problema')"
                    :disabled="loading"
                    class="inline-flex items-center gap-1.5 text-[11px] font-medium text-muted-foreground transition-colors hover:text-primary disabled:opacity-50"
                >
                    <i data-lucide="triangle-alert" class="h-3 w-3"></i>
                    Problema técnico
                </button>

                <button
                    type="button"
                    @click="sendAction('gestion.estado', 'Consultar gestiones')"
                    :disabled="loading"
                    class="inline-flex items-center gap-1.5 text-[11px] font-medium text-muted-foreground transition-colors hover:text-primary disabled:opacity-50"
                >
                    <i data-lucide="history" class="h-3 w-3"></i>
                    Mis gestiones
                </button>

                <button
                    type="button"
                    @click="sendAction('ai.enable', 'Hacer una pregunta')"
                    :disabled="loading"
                    class="inline-flex items-center gap-1.5 text-[11px] font-medium text-muted-foreground transition-colors hover:text-primary disabled:opacity-50"
                >
                    <i data-lucide="message-square-text" class="h-3 w-3"></i>
                    Consulta libre
                </button>

            </div>

        </footer>

    </div>

</section>


<script>
    window.chatbotActionAppearance = function (action) {
        const normalize = function (value) {
            return String(value ?? '')
                .trim()
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '');
        };

        /*
         * El texto visible se analiza por separado para que el contexto
         * interno, por ejemplo "problema.internet", no fuerce el mismo
         * ícono sobre todas las respuestas del paso.
         */

        const label = normalize(
            action?.label
        );

        const value = normalize(
            action?.value
        );

        const source = [
            value,
            label,
            normalize(action?.url),
            normalize(action?.action),
            normalize(action?.description),
        ]
            .filter(Boolean)
            .join(' ');

        const appearance = (
            icon,
            buttonClass,
            iconClass
        ) => ({
            icon,
            buttonClass,
            iconClass,
        });

/*
        |--------------------------------------------------------------
        | No enciende
        |--------------------------------------------------------------
        */

        if (label.includes('ya encendio')) {
    return appearance(
        'circle-check',
        'border-border hover:border-emerald-200 dark:hover:border-emerald-800',
        'bg-emerald-100 text-emerald-600 dark:bg-emerald-950/45 dark:text-emerald-300'
    );
}

/*
        |--------------------------------------------------------------
        | Impresora
        |--------------------------------------------------------------
        */

if (label.includes('ya imprime')) {
    return appearance(
        'circle-check',
        'border-border hover:border-emerald-200 dark:hover:border-emerald-800',
        'bg-emerald-100 text-emerald-600 dark:bg-emerald-950/45 dark:text-emerald-300'
    );
}

if (label.includes('sigue sin imprimir')) {
    return appearance(
        'printer-x',
        'border-border hover:border-rose-200 dark:hover:border-rose-800',
        'bg-rose-100 text-rose-600 dark:bg-rose-950/45 dark:text-rose-300'
    );
}

        /*
        |--------------------------------------------------------------
        | Navegación y decisiones
        |--------------------------------------------------------------
        */

        if (
            label.includes('menu')
            || value.includes('menu.principal')
            || label === 'inicio'
        ) {
            return appearance(
                'layout-grid',
                'border-border hover:border-blue-200 dark:hover:border-blue-800',
                'bg-blue-100 text-blue-600 dark:bg-blue-950/45 dark:text-blue-300'
            );
        }

        if (
            label === 'volver'
            || label.startsWith('volver ')
            || label.includes('regresar')
            || label.includes('anterior')
        ) {
            return appearance(
                'undo-2',
                'border-border hover:border-slate-300 dark:hover:border-slate-600',
                'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300'
            );
        }

        if (
            label.includes('no estoy seguro')
            || label.includes('no estoy segura')
            || label === 'no se'
            || label.includes('tengo duda')
        ) {
            return appearance(
                'circle-help',
                'border-border hover:border-amber-200 dark:hover:border-amber-800',
                'bg-amber-100 text-amber-700 dark:bg-amber-950/45 dark:text-amber-300'
            );
        }

        if (
            label.includes('no aparece conectado')
            || label.includes('no esta conectado')
            || label.includes('sin conexion')
            || label.includes('desconectado')
        ) {
            return appearance(
                'wifi-off',
                'border-border hover:border-rose-200 dark:hover:border-rose-800',
                'bg-rose-100 text-rose-600 dark:bg-rose-950/45 dark:text-rose-300'
            );
        }

        if (
            label.includes('si aparece conectado')
            || label.includes('esta conectado')
            || label === 'conectado'
        ) {
            return appearance(
                'circle-check',
                'border-border hover:border-emerald-200 dark:hover:border-emerald-800',
                'bg-emerald-100 text-emerald-600 dark:bg-emerald-950/45 dark:text-emerald-300'
            );
        }

        if (
            label.includes('cable')
            || label.includes('ethernet')
        ) {
            return appearance(
                'cable',
                'border-border hover:border-blue-200 dark:hover:border-blue-800',
                'bg-blue-100 text-blue-600 dark:bg-blue-950/45 dark:text-blue-300'
            );
        }

        if (
            label.includes('luces')
            || label.includes('indicador encendido')
        ) {
            return appearance(
                'lightbulb',
                'border-border hover:border-amber-200 dark:hover:border-amber-800',
                'bg-amber-100 text-amber-700 dark:bg-amber-950/45 dark:text-amber-300'
            );
        }

        if (
            label.includes('sonido')
            || label.includes('ruido')
            || label.includes('pitido')
        ) {
            return appearance(
                'volume-2',
                'border-border hover:border-violet-200 dark:hover:border-violet-800',
                'bg-violet-100 text-violet-600 dark:bg-violet-950/45 dark:text-violet-300'
            );
        }

        if (
            label.includes('atascado')
            || label.includes('atasco')
        ) {
            return appearance(
                'file-x-2',
                'border-border hover:border-orange-200 dark:hover:border-orange-800',
                'bg-orange-100 text-orange-600 dark:bg-orange-950/45 dark:text-orange-300'
            );
        }

        if (
            label.includes('funciono')
            || label.includes('resuelto')
            || label.includes('ya funciona')
        ) {
            return appearance(
                'badge-check',
                'border-border hover:border-emerald-200 dark:hover:border-emerald-800',
                'bg-emerald-100 text-emerald-600 dark:bg-emerald-950/45 dark:text-emerald-300'
            );
        }

        /*
        |--------------------------------------------------------------
        | Acciones específicas de Outlook y correo
        |--------------------------------------------------------------
        |
        | Se evalúan usando solamente la etiqueta para impedir que el
        | contexto correo.* asigne el mismo icono a todas las opciones.
        |
        */

        if (label.includes('outlook no abre')) {
            return appearance(
                'app-window',
                'border-border hover:border-indigo-200 dark:hover:border-indigo-800',
                'bg-indigo-100 text-indigo-600 dark:bg-indigo-950/45 dark:text-indigo-300'
            );
        }

        if (
            label.includes('no puedo enviar')
            || label.includes('no envia')
        ) {
            return appearance(
                'send-horizontal',
                'border-border hover:border-sky-200 dark:hover:border-sky-800',
                'bg-sky-100 text-sky-600 dark:bg-sky-950/45 dark:text-sky-300'
            );
        }

        if (
            label.includes('no recibo correo')
            || label.includes('no recibe correo')
        ) {
            return appearance(
                'mail-x',
                'border-border hover:border-rose-200 dark:hover:border-rose-800',
                'bg-rose-100 text-rose-600 dark:bg-rose-950/45 dark:text-rose-300'
            );
        }

        if (label.includes('ya abrio')) {
            return appearance(
                'badge-check',
                'border-border hover:border-emerald-200 dark:hover:border-emerald-800',
                'bg-emerald-100 text-emerald-600 dark:bg-emerald-950/45 dark:text-emerald-300'
            );
        }

        if (label.includes('sigue sin abrir')) {
            return appearance(
                'circle-x',
                'border-border hover:border-rose-200 dark:hover:border-rose-800',
                'bg-rose-100 text-rose-600 dark:bg-rose-950/45 dark:text-rose-300'
            );
        }

        if (label.includes('ya puedo enviar')) {
            return appearance(
                'send',
                'border-border hover:border-emerald-200 dark:hover:border-emerald-800',
                'bg-emerald-100 text-emerald-600 dark:bg-emerald-950/45 dark:text-emerald-300'
            );
        }

        if (
            label.includes('continua el problema')
            || label.includes('continua fallando')
        ) {
            return appearance(
                'triangle-alert',
                'border-border hover:border-amber-200 dark:hover:border-amber-800',
                'bg-amber-100 text-amber-700 dark:bg-amber-950/45 dark:text-amber-300'
            );
        }

        if (label.includes('reportar incidencia')) {
            return appearance(
                'file-warning',
                'border-border hover:border-rose-200 dark:hover:border-rose-800',
                'bg-rose-100 text-rose-600 dark:bg-rose-950/45 dark:text-rose-300'
            );
        }

        /*
        |--------------------------------------------------------------
        | Diagnósticos del menú "Tengo un problema"
        |--------------------------------------------------------------
        |
        | Estas reglas se evalúan antes que las opciones genéricas para
        | que "No enciende" no se interprete como una respuesta "No".
        |
        */

        if (
            source.includes('internet')
            || source.includes('wifi')
            || source.includes('sin red')
            || source.includes('conexion')
        ) {
            return appearance(
                'wifi',
                'border-border hover:border-sky-200 dark:hover:border-sky-800',
                'bg-sky-100 text-sky-600 dark:bg-sky-950/45 dark:text-sky-300'
            );
        }

        if (
            source.includes('outlook')
            || source.includes('correo')
            || source.includes('email')
        ) {
            return appearance(
                'mail',
                'border-border hover:border-blue-200 dark:hover:border-blue-800',
                'bg-blue-100 text-blue-600 dark:bg-blue-950/45 dark:text-blue-300'
            );
        }

        if (
            source.includes('computadora lenta')
            || source.includes('equipo lento')
            || source.includes('muy lento')
            || source.includes('se congela')
            || source.includes('se traba')
        ) {
            return appearance(
                'gauge',
                'border-border hover:border-amber-200 dark:hover:border-amber-800',
                'bg-amber-100 text-amber-700 dark:bg-amber-950/45 dark:text-amber-300'
            );
        }

        if (
            source.includes('no enciende')
            || source.includes('no prende')
            || source.includes('no arranca')
            || source.includes('no inicia')
        ) {
            return appearance(
                'power',
                'border-border hover:border-rose-200 dark:hover:border-rose-800',
                'bg-rose-100 text-rose-600 dark:bg-rose-950/45 dark:text-rose-300'
            );
        }

        if (
            source.includes('impresora')
            || source.includes('imprimir')
        ) {
            return appearance(
                'printer',
                'border-border hover:border-orange-200 dark:hover:border-orange-800',
                'bg-orange-100 text-orange-600 dark:bg-orange-950/45 dark:text-orange-300'
            );
        }

        if (
            source.includes('sistema')
            || source.includes('aplicacion')
        ) {
            return appearance(
                'app-window',
                'border-border hover:border-indigo-200 dark:hover:border-indigo-800',
                'bg-indigo-100 text-indigo-600 dark:bg-indigo-950/45 dark:text-indigo-300'
            );
        }

        if (
            source.includes('teclado')
            || source.includes('mouse')
            || source.includes('periferico')
        ) {
            return appearance(
                'keyboard',
                'border-border hover:border-cyan-200 dark:hover:border-cyan-800',
                'bg-cyan-100 text-cyan-700 dark:bg-cyan-950/45 dark:text-cyan-300'
            );
        }

        if (
            source.includes('otro problema')
            || source.includes('otro inconveniente')
        ) {
            return appearance(
                'circle-help',
                'border-border hover:border-slate-300 dark:hover:border-slate-600',
                'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300'
            );
        }

            if (
                label === 'si'
                || label.startsWith('si ')
                || label.includes('confirmar')
                || label.includes('continuar')
            ) {
            return appearance(
                'circle-check',
                'border-border hover:border-emerald-200 dark:hover:border-emerald-800',
                'bg-emerald-100 text-emerald-600 dark:bg-emerald-950/45 dark:text-emerald-300'
            );
        }

            if (
                label === 'no'
                || label.startsWith('no ')
                || label.includes('cancelar')
                || label.includes('cerrar')
            ) {
            return appearance(
                'circle-x',
                'border-border hover:border-slate-300 dark:hover:border-slate-600',
                'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300'
            );
        }

        /*
        |--------------------------------------------------------------
        | Gestiones principales
        |--------------------------------------------------------------
        */

        if (
            source.includes('incidencia')
            || source.includes('problema')
            || source.includes('falla')
            || source.includes('error')
        ) {
            return appearance(
                'triangle-alert',
                'border-border hover:border-rose-200 dark:hover:border-rose-800',
                'bg-rose-100 text-rose-600 dark:bg-rose-950/45 dark:text-rose-300'
            );
        }

        if (
            source.includes('solicitud')
            || source.includes('servicio')
            || source.includes('requerimiento')
        ) {
            return appearance(
                'clipboard-list',
                'border-border hover:border-emerald-200 dark:hover:border-emerald-800',
                'bg-emerald-100 text-emerald-600 dark:bg-emerald-950/45 dark:text-emerald-300'
            );
        }

        if (
            source.includes('mayor')
            || source.includes('autorizacion')
            || source.includes('memorando')
        ) {
            return appearance(
                'file-check-2',
                'border-border hover:border-indigo-200 dark:hover:border-indigo-800',
                'bg-indigo-100 text-indigo-600 dark:bg-indigo-950/45 dark:text-indigo-300'
            );
        }

        if (
            source.includes('menor')
            || source.includes('temporal')
        ) {
            return appearance(
                'clock-3',
                'border-border hover:border-blue-200 dark:hover:border-blue-800',
                'bg-blue-100 text-blue-600 dark:bg-blue-950/45 dark:text-blue-300'
            );
        }

        if (source.includes('pase')) {
            return appearance(
                'badge-check',
                'border-border hover:border-blue-200 dark:hover:border-blue-800',
                'bg-blue-100 text-blue-600 dark:bg-blue-950/45 dark:text-blue-300'
            );
        }

        if (
            source.includes('estado')
            || source.includes('seguimiento')
            || source.includes('gestiones')
            || source.includes('historial')
        ) {
            return appearance(
                'history',
                'border-border hover:border-amber-200 dark:hover:border-amber-800',
                'bg-amber-100 text-amber-700 dark:bg-amber-950/45 dark:text-amber-300'
            );
        }

        /*
        |--------------------------------------------------------------
        | Categorías tecnológicas
        |--------------------------------------------------------------
        */

        if (
            source.includes('computadora')
            || source.includes('equipo')
            || source.includes('laptop')
            || source.includes('monitor')
        ) {
            return appearance(
                'monitor',
                'border-border hover:border-sky-200 dark:hover:border-sky-800',
                'bg-sky-100 text-sky-600 dark:bg-sky-950/45 dark:text-sky-300'
            );
        }

        if (
            source.includes('programa')
            || source.includes('software')
            || source.includes('instalar')
            || source.includes('aplicacion')
            || source.includes('sistema')
        ) {
            return appearance(
                'package-plus',
                'border-border hover:border-cyan-200 dark:hover:border-cyan-800',
                'bg-cyan-100 text-cyan-700 dark:bg-cyan-950/45 dark:text-cyan-300'
            );
        }

        if (
            source.includes('acceso')
            || source.includes('permiso')
            || source.includes('credencial')
        ) {
            return appearance(
                'key-round',
                'border-border hover:border-indigo-200 dark:hover:border-indigo-800',
                'bg-indigo-100 text-indigo-600 dark:bg-indigo-950/45 dark:text-indigo-300'
            );
        }

        if (
            source.includes('vpn')
            || source.includes('internet')
            || source.includes('wifi')
            || source.includes('red')
            || source.includes('conexion')
        ) {
            return appearance(
                'wifi',
                'border-border hover:border-sky-200 dark:hover:border-sky-800',
                'bg-sky-100 text-sky-600 dark:bg-sky-950/45 dark:text-sky-300'
            );
        }

        if (
            source.includes('impresora')
            || source.includes('imprimir')
        ) {
            return appearance(
                'printer',
                'border-border hover:border-orange-200 dark:hover:border-orange-800',
                'bg-orange-100 text-orange-600 dark:bg-orange-950/45 dark:text-orange-300'
            );
        }

        if (
            source.includes('correo')
            || source.includes('email')
            || source.includes('outlook')
        ) {
            return appearance(
                'mail',
                'border-border hover:border-blue-200 dark:hover:border-blue-800',
                'bg-blue-100 text-blue-600 dark:bg-blue-950/45 dark:text-blue-300'
            );
        }

        if (
            source.includes('cuenta')
            || source.includes('contrasena')
            || source.includes('usuario')
        ) {
            return appearance(
                'circle-user-round',
                'border-border hover:border-violet-200 dark:hover:border-violet-800',
                'bg-violet-100 text-violet-600 dark:bg-violet-950/45 dark:text-violet-300'
            );
        }

        if (
            source.includes('configuracion')
            || source.includes('cambio')
            || source.includes('ajuste')
        ) {
            return appearance(
                'settings-2',
                'border-border hover:border-slate-300 dark:hover:border-slate-600',
                'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300'
            );
        }


        /*
        |--------------------------------------------------------------
        | Consulta abierta
        |--------------------------------------------------------------
        */

        if (
            action?.variant === 'ai'
            || source.includes('pregunta')
            || source.includes('consulta')
            || source.includes('asistente')
        ) {
            return appearance(
                'message-square-text',
                'border-border hover:border-violet-200 dark:hover:border-violet-800',
                'bg-violet-100 text-violet-600 dark:bg-violet-950/45 dark:text-violet-300'
            );
        }

        /*
        |--------------------------------------------------------------
        | Opción genérica
        |--------------------------------------------------------------
        */

        return appearance(
            'mouse-pointer-click',
            'border-border hover:border-primary/30',
            'bg-primary/10 text-primary'
        );
    };

    window.chatbotResolvedActionIcon = function (action) {
        /*
         * Usa primero el icono definido por el flujo. Si una acción antigua
         * no lo incluye, conserva el mapeo semántico como respaldo.
         */
        const explicitIcon = String(
            action?.icon ?? ''
        ).trim();

        return explicitIcon
            || window.chatbotActionAppearance(action).icon;
    };
</script>


@once
    <script
        src="{{ asset('js/chatbot.js') }}?v={{ filemtime(public_path('js/chatbot.js')) }}"
    ></script>
@endonce