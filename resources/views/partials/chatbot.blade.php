{{-- ==========================================================
    CHATBOT INTERACTIVO DEL PORTAL TI
========================================================== --}}

<section>

    <div
        class="bg-card rounded-2xl border border-border overflow-hidden"
        x-data="chatbotWidget()"
        x-init="init()"
    >

        {{-- HEADER --}}
        <div
            class="px-6 py-4 border-b border-border
                   flex items-center justify-between gap-3"
        >

            <div class="flex items-center gap-3">

                <div
                    class="w-9 h-9 rounded-xl bg-primary/10
                           flex items-center justify-center"
                >
                    <i
                        data-lucide="bot"
                        class="w-[18px] h-[18px] text-primary"
                    ></i>
                </div>

                <div>

                    <p class="text-sm font-semibold text-foreground">
                        Asistente TI
                    </p>

                    <div class="flex items-center gap-1.5 mt-0.5">

                        <span
                            class="w-1.5 h-1.5 bg-emerald-400 rounded-full"
                        ></span>

                        <span class="text-xs text-muted-foreground">
                            En línea
                        </span>

                    </div>

                </div>

            </div>


            {{-- MODO ACTIVO --}}
            <div
                class="inline-flex items-center gap-1.5
                       rounded-full px-2.5 py-1 text-[11px] font-medium"
                :class="
                    aiMode
                        ? 'bg-violet-100 text-violet-700'
                        : 'bg-blue-50 text-blue-700'
                "
            >

                <i
                    :data-lucide="
                        aiMode
                            ? 'sparkles'
                            : 'list-tree'
                    "
                    class="w-3 h-3"
                ></i>

                <span
                    x-text="
                        aiMode
                            ? 'Consulta con IA'
                            : 'Asistente guiado'
                    "
                ></span>

            </div>

        </div>


        {{-- CONTENEDOR DE MENSAJES --}}
        <div
            id="chatbot-messages"
            x-ref="messages"
            class="px-6 py-5 min-h-[180px] max-h-96
                   overflow-y-auto space-y-4"
        >

            {{-- MENSAJE INICIAL --}}
            <div class="flex gap-3">

                <div
                    class="w-8 h-8 rounded-full bg-primary/10
                           flex items-center justify-center flex-shrink-0"
                >
                    <i
                        data-lucide="bot"
                        class="w-[15px] h-[15px] text-primary"
                    ></i>
                </div>

                <div
                    class="bg-muted rounded-2xl rounded-tl-sm
                           px-4 py-3 max-w-lg"
                >

                    <p class="text-sm text-foreground leading-relaxed">
                        Hola{{ auth()->check() ? ', '.explode(' ', auth()->user()->nombre)[0] : '' }} 👋
                    </p>

                    <p class="text-sm text-foreground leading-relaxed mt-2">
                        ¿En qué puedo ayudarte? Selecciona una opción
                        para comenzar.
                    </p>


                    {{-- OPCIONES PRINCIPALES --}}
                    <div class="flex flex-wrap gap-2 mt-3">

                        <button
                            type="button"
                            @click="
                                sendAction(
                                    'problema.menu',
                                    'Tengo un problema'
                                )
                            "
                            :disabled="loading"
                            class="inline-flex items-center gap-1.5
                                   rounded-full border border-blue-200
                                   bg-blue-50 px-3 py-1.5
                                   text-xs font-medium text-blue-700
                                   hover:border-blue-500
                                   hover:bg-blue-500 hover:text-white
                                   disabled:opacity-50
                                   disabled:cursor-not-allowed
                                   transition-colors"
                        >
                            <i
                                data-lucide="circle-alert"
                                class="w-3 h-3"
                            ></i>

                            Tengo un problema
                        </button>

                        <button
                            type="button"
                            @click="
                                sendAction(
                                    'solicitud.menu',
                                    'Necesito un servicio'
                                )
                            "
                            :disabled="loading"
                            class="inline-flex items-center gap-1.5
                                   rounded-full border border-blue-200
                                   bg-blue-50 px-3 py-1.5
                                   text-xs font-medium text-blue-700
                                   hover:border-blue-500
                                   hover:bg-blue-500 hover:text-white
                                   disabled:opacity-50
                                   disabled:cursor-not-allowed
                                   transition-colors"
                        >
                            <i
                                data-lucide="wrench"
                                class="w-3 h-3"
                            ></i>

                            Necesito un servicio
                        </button>

                        <button
                            type="button"
                            @click="
                                sendAction(
                                    'pase.menu',
                                    'Necesito un pase'
                                )
                            "
                            :disabled="loading"
                            class="inline-flex items-center gap-1.5
                                   rounded-full border border-blue-200
                                   bg-blue-50 px-3 py-1.5
                                   text-xs font-medium text-blue-700
                                   hover:border-blue-500
                                   hover:bg-blue-500 hover:text-white
                                   disabled:opacity-50
                                   disabled:cursor-not-allowed
                                   transition-colors"
                        >
                            <i
                                data-lucide="contact"
                                class="w-3 h-3"
                            ></i>

                            Necesito un pase
                        </button>

                        <button
                            type="button"
                            @click="
                                sendAction(
                                    'gestion.estado',
                                    'Consultar gestiones'
                                )
                            "
                            :disabled="loading"
                            class="inline-flex items-center gap-1.5
                                   rounded-full border border-blue-200
                                   bg-blue-50 px-3 py-1.5
                                   text-xs font-medium text-blue-700
                                   hover:border-blue-500
                                   hover:bg-blue-500 hover:text-white
                                   disabled:opacity-50
                                   disabled:cursor-not-allowed
                                   transition-colors"
                        >
                            <i
                                data-lucide="search"
                                class="w-3 h-3"
                            ></i>

                            Consultar gestiones
                        </button>

                        <button
                            type="button"
                            @click="
                                sendAction(
                                    'ai.enable',
                                    'Hacer una pregunta'
                                )
                            "
                            :disabled="loading"
                            class="inline-flex items-center gap-1.5
                                   rounded-full border border-violet-200
                                   bg-violet-50 px-3 py-1.5
                                   text-xs font-medium text-violet-700
                                   hover:border-violet-500
                                   hover:bg-violet-500 hover:text-white
                                   disabled:opacity-50
                                   disabled:cursor-not-allowed
                                   transition-colors"
                        >
                            <i
                                data-lucide="sparkles"
                                class="w-3 h-3"
                            ></i>

                            Hacer una pregunta
                        </button>

                    </div>

                </div>

            </div>


            {{-- MENSAJES DINÁMICOS --}}
            <template
                x-for="(msg, index) in messages"
                :key="msg.id ?? index"
            >

                <div
                    class="flex gap-3"
                    :class="
                        msg.from === 'user'
                            ? 'flex-row-reverse'
                            : ''
                    "
                >

                    {{-- ICONO --}}
                    <div
                        class="w-8 h-8 rounded-full
                               flex items-center justify-center flex-shrink-0"
                        :class="
                            msg.from === 'user'
                                ? 'bg-primary'
                                : 'bg-primary/10'
                        "
                    >

                        <i
                            :data-lucide="
                                msg.from === 'user'
                                    ? 'user'
                                    : 'bot'
                            "
                            class="w-[15px] h-[15px]"
                            :class="
                                msg.from === 'user'
                                    ? 'text-white'
                                    : 'text-primary'
                            "
                        ></i>

                    </div>


                    {{-- BURBUJA --}}
                    <div
                        class="rounded-2xl px-4 py-3 max-w-lg"
                        :class="
                            msg.from === 'user'
                                ? 'bg-primary text-white rounded-tr-sm'
                                : 'bg-muted text-foreground rounded-tl-sm'
                        "
                    >

                        {{-- TEXTO --}}
                        <p
                            x-show="msg.text"
                            x-transition:enter="
                                transition ease-out duration-200
                            "
                            x-transition:enter-start="
                                opacity-0 translate-y-1
                            "
                            x-transition:enter-end="
                                opacity-100 translate-y-0
                            "
                            class="text-sm leading-relaxed whitespace-pre-line"
                            x-text="msg.text"
                        ></p>


                        {{-- PROCESANDO --}}
                        <div
                            x-show="
                                msg.from === 'bot'
                                && msg.streaming
                                && !msg.text
                            "
                            class="flex items-center gap-1 py-1"
                            aria-label="El asistente está procesando"
                        >

                            <span
                                class="w-1.5 h-1.5 rounded-full
                                       bg-current opacity-50 animate-bounce"
                            ></span>

                            <span
                                class="w-1.5 h-1.5 rounded-full
                                       bg-current opacity-50 animate-bounce"
                                style="animation-delay: 120ms"
                            ></span>

                            <span
                                class="w-1.5 h-1.5 rounded-full
                                       bg-current opacity-50 animate-bounce"
                                style="animation-delay: 240ms"
                            ></span>

                        </div>


                        {{-- GESTIONES --}}
                        <template
                            x-if="
                                !msg.streaming
                                && msg.items
                                && msg.items.length
                            "
                        >

                            <div class="mt-3 space-y-2">

                                <template
                                    x-for="(item, i) in msg.items"
                                    :key="item.id ?? i"
                                >

                                    <a
                                        :href="item.url"
                                        class="block rounded-lg border
                                               border-border bg-card
                                               px-3 py-2
                                               hover:border-primary
                                               transition-colors"
                                    >

                                        <div
                                            class="flex items-center
                                                   justify-between gap-2"
                                        >

                                            <span
                                                class="text-xs font-medium"
                                                x-text="
                                                    item.tipo
                                                    ?? item.type
                                                    ?? 'Gestión'
                                                "
                                            ></span>

                                            <span
                                                class="text-[11px] rounded-full
                                                       bg-primary/10 text-primary
                                                       px-2 py-0.5"
                                                x-text="
                                                    item.status
                                                    ?? item.estado
                                                    ?? ''
                                                "
                                            ></span>

                                        </div>

                                        <p
                                            class="text-xs
                                                   text-muted-foreground mt-1"
                                            x-text="
                                                item.title
                                                ?? item.titulo
                                                ?? item.codigo
                                                ?? ''
                                            "
                                        ></p>

                                    </a>

                                </template>

                            </div>

                        </template>


                        {{-- REDIRECCIÓN --}}
                        <template
                            x-if="
                                !msg.streaming
                                && msg.redirect
                            "
                        >

                            <a
                                :href="msg.redirect.url"
                                class="inline-flex items-center gap-2
                                       mt-3 px-3 py-1.5 rounded-full
                                       text-xs font-medium
                                       bg-primary text-white
                                       hover:bg-primary/90
                                       transition-colors"
                            >

                                <i
                                    data-lucide="external-link"
                                    class="w-3 h-3"
                                ></i>

                                <span
                                    x-text="msg.redirect.label"
                                ></span>

                            </a>

                        </template>


                        {{-- ACCIONES RÁPIDAS --}}
                        <template
                            x-if="
                                !msg.streaming
                                && msg.quick_actions
                                && msg.quick_actions.length
                            "
                        >

                            <div class="flex flex-wrap gap-2 mt-3">

                                <template
                                    x-for="(action, i) in msg.quick_actions"
                                    :key="
                                        action.value
                                        ?? action.url
                                        ?? action.label
                                        ?? i
                                    "
                                >

                                    <button
    type="button"

    @click="
        executeAction(
            action,
            msg
        )
    "

    :disabled="loading"

    class="inline-flex items-center gap-1.5
           rounded-full border
           px-3 py-1.5
           text-xs font-medium
           disabled:opacity-50
           disabled:cursor-not-allowed
           transition-colors"

    :class="
        action.variant === 'ai'
            ? 'border-violet-200 bg-violet-50 text-violet-700 hover:border-violet-500 hover:bg-violet-500 hover:text-white'
            : 'border-blue-200 bg-blue-50 text-blue-700 hover:border-blue-500 hover:bg-blue-500 hover:text-white'
    "
>
    <template x-if="action.icon">

        <i
            :data-lucide="action.icon"
            class="w-3 h-3"
        ></i>

    </template>

    <span
        x-text="action.label"
    ></span>
</button>

                                </template>

                            </div>

                        </template>


                        {{-- INFORMACIÓN DE IA --}}
                        <template
                            x-if="
                                msg.ai
                                && !msg.streaming
                            "
                        >

                            <div
                                class="mt-3 pt-2 border-t border-border/60
                                       flex items-center gap-1.5
                                       text-[11px] opacity-70"
                            >

                                <i
                                    data-lucide="sparkles"
                                    class="w-3 h-3"
                                ></i>

                                <span>
                                    Respuesta asistida por IA
                                </span>

                            </div>

                        </template>


                        {{-- DEBUG LOCAL --}}
                        @if(app()->environment('local'))

                            <template
                                x-if="
                                    msg.intent
                                    && !msg.streaming
                                "
                            >

                                <div
                                    class="mt-3 text-[10px]
                                           text-muted-foreground"
                                >
                                    Intent:

                                    <span
                                        x-text="
                                            msg.intent.name
                                            ?? 'N/A'
                                        "
                                    ></span>

                                    <template
                                        x-if="
                                            msg.intent.action
                                        "
                                    >
                                        <span>
                                            · Acción:

                                            <span
                                                x-text="
                                                    msg.intent.action
                                                "
                                            ></span>
                                        </span>
                                    </template>

                                </div>

                            </template>

                        @endif

                    </div>

                </div>

            </template>

        </div>


        {{-- INPUT --}}
        <div class="px-6 py-4 border-t border-border">

            {{-- ESTADO DEL INPUT --}}
            <div
                class="flex items-center justify-between gap-3 mb-2"
            >

                <p
                    class="text-xs"
                    :class="
                        aiMode
                            ? 'text-violet-600'
                            : 'text-muted-foreground'
                    "
                >
                    <span
                        x-text="
    aiMode
        ? 'Describe tu consulta para que la IA pueda ayudarte.'
        : 'Selecciona una de las opciones del asistente para continuar.'
"
                    ></span>
                </p>

                <button
                    x-show="aiMode"
                    x-cloak
                    type="button"
                    @click="
                        sendAction(
                            'menu.principal',
                            'Volver al menú'
                        )
                    "
                    :disabled="loading"
                    class="text-xs font-medium text-primary
                           hover:underline disabled:opacity-50"
                >
                    Salir del modo IA
                </button>

            </div>


            <form
    @submit.prevent="
        if (aiMode) {
            send()
        }
    "
                class="flex items-center gap-3
                       rounded-xl border px-4 py-3
                       transition-colors"
                :class="
                    aiMode
                        ? 'border-violet-300 bg-violet-50/40 focus-within:border-violet-500'
                        : 'border-border bg-muted/50 focus-within:border-primary/50'
                "
            >

                <i
                    :data-lucide="
                        aiMode
                            ? 'sparkles'
                            : 'message-circle'
                    "
                    class="w-4 h-4"
                    :class="
                        aiMode
                            ? 'text-violet-500'
                            : 'text-muted-foreground'
                    "
                ></i>

                <input
    x-ref="input"
    x-model="draft"
    type="text"
    maxlength="500"
    autocomplete="off"

    :disabled="
        loading
        || !aiMode
    "

    :placeholder="
        aiMode
            ? 'Describe el problema o escribe tu pregunta...'
            : 'Selecciona una opción para comenzar...'
    "

    class="flex-1 bg-transparent text-sm
           outline-none text-foreground
           placeholder:text-muted-foreground
           disabled:cursor-not-allowed
           disabled:opacity-60"
>

                <button
    type="submit"

    :disabled="
        loading
        || !aiMode
        || !draft.trim()
    "

    class="w-8 h-8 rounded-lg
           flex items-center justify-center
           disabled:opacity-40
           disabled:cursor-not-allowed
           transition-colors"

    :class="
        aiMode
            ? 'bg-violet-600 hover:bg-violet-700'
            : 'bg-muted-foreground/30'
    "
>
    <i
        data-lucide="send"
        class="w-4 h-4 text-white"
    ></i>
</button>

            </form>


            {{-- ATAJOS --}}
            <div class="flex flex-wrap gap-2 mt-3">

                <button
                    type="button"
                    @click="
                        sendAction(
                            'menu.principal',
                            'Mostrar menú'
                        )
                    "
                    :disabled="loading"
                    class="inline-flex items-center gap-1.5
                           rounded-full border border-border
                           bg-card px-3 py-1.5
                           text-xs font-medium text-foreground
                           hover:border-primary hover:text-primary
                           disabled:opacity-50
                           transition-colors"
                >
                    <i
                        data-lucide="menu"
                        class="w-3 h-3"
                    ></i>

                    Menú
                </button>

                <button
                    type="button"
                    @click="
                        sendAction(
                            'problema.menu',
                            'Tengo un problema'
                        )
                    "
                    :disabled="loading"
                    class="inline-flex items-center gap-1.5
                           rounded-full border border-border
                           bg-card px-3 py-1.5
                           text-xs font-medium text-foreground
                           hover:border-primary hover:text-primary
                           disabled:opacity-50
                           transition-colors"
                >
                    <i
                        data-lucide="circle-alert"
                        class="w-3 h-3"
                    ></i>

                    Problema técnico
                </button>

                <button
                    type="button"
                    @click="
                        sendAction(
                            'gestion.estado',
                            'Consultar gestiones'
                        )
                    "
                    :disabled="loading"
                    class="inline-flex items-center gap-1.5
                           rounded-full border border-border
                           bg-card px-3 py-1.5
                           text-xs font-medium text-foreground
                           hover:border-primary hover:text-primary
                           disabled:opacity-50
                           transition-colors"
                >
                    <i
                        data-lucide="search"
                        class="w-3 h-3"
                    ></i>

                    Mis gestiones
                </button>

                <button
                    type="button"
                    @click="
                        sendAction(
                            'ai.enable',
                            'Hacer una pregunta'
                        )
                    "
                    :disabled="loading"
                    class="inline-flex items-center gap-1.5
                           rounded-full border border-violet-200
                           bg-violet-50 px-3 py-1.5
                           text-xs font-medium text-violet-700
                           hover:border-violet-500
                           disabled:opacity-50
                           transition-colors"
                >
                    <i
                        data-lucide="sparkles"
                        class="w-3 h-3"
                    ></i>

                    Preguntar a IA
                </button>

            </div>

        </div>

    </div>

</section>

<script src="{{ asset('js/chatbot.js') }}"></script>