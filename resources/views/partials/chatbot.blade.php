{{-- Chatbot: Asistente Virtual TI --}}
<section>

    <div class="bg-card rounded-2xl border border-border overflow-hidden" x-data="chatbotWidget()" x-init="init()">

        <div class="px-6 py-4 border-b border-border flex items-center gap-3">

            <div class="w-9 h-9 rounded-xl bg-primary/10 flex items-center justify-center">
                <i data-lucide="bot" class="w-[18px] h-[18px] text-primary"></i>
            </div>

            <div>
                <p class="text-sm font-semibold text-foreground">Asistente TI</p>
                <div class="flex items-center gap-1.5 mt-0.5">
                    <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full"></span>
                    <span class="text-xs text-muted-foreground">En línea</span>
                </div>
            </div>

        </div>

        {{-- Historial de mensajes --}}
        <div class="px-6 py-5 min-h-[140px] max-h-72 overflow-y-auto space-y-3" id="chatbot-messages" x-ref="messages">

            {{-- Mensaje inicial estático (se conserva tal cual lo tenías) --}}
            <div class="flex gap-3">
                <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <i data-lucide="bot" class="w-[15px] h-[15px] text-primary"></i>
                </div>
                <div class="bg-muted rounded-2xl rounded-tl-sm px-4 py-3 max-w-lg">
                   <p class="text-sm text-foreground leading-relaxed">
                    ¡Hola{{ auth()->check() ? ', '.explode(' ', auth()->user()->nombre)[0] : '' }}! 👋 
                    Soy el asistente del Portal TI. Puedo ayudarte a reportar incidencias, crear solicitudes y consultar información relacionada con los servicios tecnológicos.
                    </p>
                    <p class="text-sm text-foreground leading-relaxed mt-2">¿En qué te puedo ayudar hoy?</p>
                </div>
            </div>

            {{-- Mensajes dinámicos (usuario y bot) --}}
            <template x-for="(msg, index) in messages" :key="index">
                <div class="flex gap-3" :class="msg.from === 'user' ? 'flex-row-reverse' : ''">

                    <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"
                         :class="msg.from === 'user' ? 'bg-primary' : 'bg-primary/10'">
                        <i :data-lucide="msg.from === 'user' ? 'user' : 'bot'"
                           class="w-[15px] h-[15px]"
                           :class="msg.from === 'user' ? 'text-white' : 'text-primary'"></i>
                    </div>

                    <div class="rounded-2xl px-4 py-3 max-w-lg"
                         :class="msg.from === 'user' ? 'bg-primary text-white rounded-tr-sm' : 'bg-muted text-foreground rounded-tl-sm'">

                        <p class="text-sm leading-relaxed whitespace-pre-line" x-text="msg.text"></p>

                        {{-- Lista de gestiones (para respuestas de "consultar estado") --}}
                        <template x-if="msg.items && msg.items.length">
                            <div class="mt-3 space-y-2">
                                <template x-for="(item, i) in msg.items" :key="i">
                                    <a :href="item.url"
                                       class="block rounded-lg border border-border bg-card px-3 py-2 hover:border-primary transition-colors">
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="text-xs font-medium text-foreground" x-text="item.tipo"></span>
                                            <span class="text-[11px] px-2 py-0.5 rounded-full bg-primary/10 text-primary" x-text="item.status"></span>
                                        </div>
                                        <p class="text-xs text-muted-foreground mt-1" x-text="item.title"></p>
                                    </a>
                                </template>
                            </div>
                        </template>

                        {{-- Botón de redirección directa al módulo --}}
                        <template x-if="msg.redirect">
                            <a :href="msg.redirect.url"
                               class="inline-flex items-center gap-1.5 mt-3 text-xs font-medium px-3 py-1.5 rounded-full bg-primary text-white hover:bg-blue-700 transition-colors">
                                <span x-text="msg.redirect.label"></span>
                            </a>
                        </template>

                        {{-- Acciones rápidas propuestas por el bot para este mensaje --}}
                        <template x-if="msg.quick_actions && msg.quick_actions.length">
                            <div class="flex flex-wrap gap-2 mt-3">
                                <template x-for="(qa, i) in msg.quick_actions" :key="i">
                                    <button
                                        @click="qa.action === 'redirect' ? (window.location.href = msg.redirect?.url) : send(qa.value)"
                                        class="px-3 py-1 rounded-full text-xs font-medium bg-card border border-border text-foreground hover:bg-blue-600 hover:text-white hover:border-blue-600 transition-colors"
                                        x-text="qa.label">
                                    </button>
                                </template>
                            </div>
                        </template>

                    </div>
                </div>
            </template>

            {{-- Indicador "escribiendo…" --}}
            <div class="flex gap-3" x-show="loading" x-cloak>
                <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <i data-lucide="bot" class="w-[15px] h-[15px] text-primary"></i>
                </div>
                <div class="bg-muted rounded-2xl rounded-tl-sm px-4 py-3">
                    <p class="text-sm text-muted-foreground">Escribiendo…</p>
                </div>
            </div>

        </div>

        {{-- Input --}}
        <div class="px-6 py-4 border-t border-border">

            <form @submit.prevent="send()" class="flex items-center gap-3 rounded-xl border px-4 py-3 border-border bg-muted/50">

                <i data-lucide="search" class="w-4 h-4 text-muted-foreground flex-shrink-0"></i>

                <input
                    type="text"
                    x-model="draft"
                    placeholder="Escribe tu consulta o solicitud…"
                    class="flex-1 bg-transparent text-sm text-foreground placeholder:text-muted-foreground outline-none border-none focus:outline-none focus:ring-0">

                <button
                    type="submit"
                    :disabled="loading || !draft.trim()"
                    class="w-8 h-8 rounded-lg bg-primary flex items-center justify-center flex-shrink-0 hover:bg-blue-700 transition-colors disabled:opacity-50"
                >
                    <i data-lucide="send" class="w-3.5 h-3.5 text-white"></i>
                </button>

            </form>

            <p class="text-xs text-muted-foreground mt-2 px-1">
                También puedes usar los accesos directos de abajo para solicitudes frecuentes.
            </p>

            <div class="flex flex-wrap gap-2 mt-3 px-1">
                <button @click="send('quiero reportar una incidencia')"
                    class="px-3 py-1 rounded-full text-xs font-medium bg-muted text-foreground hover:bg-blue-600 hover:text-white transition-colors">
                    Reportar incidencia
                </button>
                <button @click="send('consultar estado de mis gestiones')"
                    class="px-3 py-1 rounded-full text-xs font-medium bg-muted text-foreground hover:bg-blue-600 hover:text-white transition-colors">
                    Consultar solicitud
                </button>
                <button @click="send('necesito soporte tecnico')"
                    class="px-3 py-1 rounded-full text-xs font-medium bg-muted text-foreground hover:bg-blue-600 hover:text-white transition-colors">
                    Solicitar soporte
                </button>
            </div>

        </div>

    </div>

</section>

{{--
    Este componente usa Alpine.js (x-data, x-for, x-model, etc).
    Si tu layout principal ya carga Alpine (muy común en stacks con Tailwind),
    no necesitas hacer nada más. Si no lo tienes, agrega esto una sola vez
    en tu layout principal, antes de </body>:

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    Y asegúrate de tener el meta CSRF en el <head>:
    <meta name="csrf-token" content="{{ csrf_token() }}">
--}}

<script>
function chatbotWidget() {
    return {
        draft: '',
        loading: false,
        messages: [],

        init() {
            // Re-renderiza los íconos lucide cuando Alpine agrega nuevos nodos al DOM.
            this.$watch('messages', () => {
                this.$nextTick(() => {
                    if (window.lucide) window.lucide.createIcons();
                    this.$refs.messages.scrollTop = this.$refs.messages.scrollHeight;
                });
            });
        },

        async send(text) {
            const message = (text ?? this.draft).trim();
            if (!message || this.loading) return;

            this.messages.push({ from: 'user', text: message });
            this.draft = '';
            this.loading = true;

            try {
                const response = await fetch("{{ route('chatbot.message') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    },
                    body: JSON.stringify({ message }),
                });

                if (!response.ok) throw new Error('Respuesta no válida del servidor');

                const data = await response.json();

                this.messages.push({
                    from: 'bot',
                    text: data.message,
                    quick_actions: data.quick_actions ?? [],
                    redirect: data.redirect ?? null,
                    items: data.items ?? null,
                });
            } catch (error) {
                console.error(error);
                this.messages.push({
                    from: 'bot',
                    text: 'Tuve un problema para procesar tu mensaje. Intenta de nuevo en unos segundos.',
                    quick_actions: [],
                    redirect: null,
                    items: null,
                });
            } finally {
                this.loading = false;
            }
        },
    };
}
</script>