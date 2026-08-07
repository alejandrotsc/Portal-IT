/*
|--------------------------------------------------------------------------
| Chatbot interactivo del Portal TI
|--------------------------------------------------------------------------
|
| Define el componente principal del asistente y coordina navegación por
| flujo, consultas libres con IA, streaming, historial temporal y acciones
| de interfaz.
|
| Modos:
| - flow: navegación mediante botones.
| - ai: texto libre enviado al proveedor de IA.
|
*/

window.chatbotWidget = function (options = {}) {
    return {

        /*
        |--------------------------------------------------------------------------
        | Estado
        |--------------------------------------------------------------------------
        |
        | Centraliza las propiedades reactivas utilizadas por el widget, incluyendo mensajes, modo IA, contexto, control de solicitudes, historial y renderizado.
        |
        */

        draft: '',

        loading: false,

        aiMode: false,

        streamStarted: false,

        messages: [],

        /*
         * Contexto acumulado del flujo interactivo.
         */
        flowContext: {},

        historyStorageKey: String(
            options.storageKey
            ?? 'portal-it-chatbot-history'
        ),

        historyMaxMessages: 80,

        requestController: null,

        requestTimeout: null,

        /*
         * Promesa compartida para impedir que el mismo widget
         * ejecute más de una precarga simultánea.
         */
        warmUpPromise: null,

        messageCounter: 0,

        scrollFrame: null,

        iconRetryTimeout: null,


        /*
        |--------------------------------------------------------------------------
        | Inicialización
        |--------------------------------------------------------------------------
        |
        | Restaura el historial temporal, actualiza iconos y posiciona la conversación en el último mensaje disponible.
        |
        */

        /*
        |--------------------------------------------------------------------------
        | Inicializar widget
        |--------------------------------------------------------------------------
        |
        | Restaura el historial disponible y actualiza la interfaz una vez que
        | Alpine termina de montar el componente.
        |
        */

        init() {
            this.restoreChatHistory();

            this.$nextTick(() => {
                this.renderIcons();

                if (this.messages.length) {
                    this.scrollBottom();
                }
            });

        },


        /*
        |--------------------------------------------------------------------------
        | Precargar Ollama
        |--------------------------------------------------------------------------
        |
        | Solicita la activación anticipada del modelo cuando corresponde, evita precargas duplicadas y utiliza almacenamiento local para coordinar múltiples pestañas.
        |
        */

        /*
        |--------------------------------------------------------------------------
        | Precargar modelo de IA
        |--------------------------------------------------------------------------
        |
        | Solicita una precarga controlada del modelo, evita ejecuciones
        | simultáneas y coordina el estado entre pestañas mediante localStorage.
        |
        */

        async warmUpModel() {
            /*
             * Si este mismo widget ya inició una precarga,
             * reutilizar la promesa existente.
             */
            if (this.warmUpPromise) {
                return this.warmUpPromise;
            }

            const endpoint = document
                .querySelector(
                    'meta[name="chatbot-warmup-endpoint"]'
                )
                ?.getAttribute('content')
                ?.trim();

            if (!endpoint) {
                return false;
            }

            const warmedStorageKey =
                'portal-it-chatbot-warmed-at';

            const warmingStorageKey =
                'portal-it-chatbot-warming-at';

            const now = Date.now();

            let warmedAt = 0;
            let warmingAt = 0;

            try {
                warmedAt = Number(
                    window.localStorage.getItem(
                        warmedStorageKey
                    )
                    ?? 0
                );

                warmingAt = Number(
                    window.localStorage.getItem(
                        warmingStorageKey
                    )
                    ?? 0
                );

            } catch (error) {
                console.debug(
                    'No fue posible consultar el estado de precarga.'
                );
            }

            /*
             * El backend mantiene el modelo activo durante 30 minutos.
             * Se usa una ventana menor para evitar confiar en una marca
             * que esté cerca de vencer.
             */
            const warmValidity =
                20 * 60 * 1000;

            /*
             * Evita que dos pestañas o dos inicializaciones intenten
             * cargar el modelo al mismo tiempo.
             */
            const warmingValidity =
                5 * 60 * 1000;

            if (
                warmedAt
                && now - warmedAt < warmValidity
            ) {
                return true;
            }

            if (
                warmingAt
                && now - warmingAt < warmingValidity
            ) {
                return true;
            }

            try {
                window.localStorage.setItem(
                    warmingStorageKey,
                    String(now)
                );
            } catch (error) {
                console.debug(
                    'No fue posible guardar el estado de precarga.'
                );
            }

            const warmUpController =
                new AbortController();

            const warmUpTimeout =
                window.setTimeout(
                    () => {
                        warmUpController.abort();
                    },
                    30000
                );

            this.warmUpPromise = fetch(
                endpoint,
                {
                    method: 'POST',

                    headers: {
                        'Accept':
                            'application/json',

                        'X-Requested-With':
                            'XMLHttpRequest',

                        'X-CSRF-TOKEN':
                            this.getCsrfToken(),
                    },

                    credentials:
                        'same-origin',

                    signal:
                        warmUpController.signal,
                }
            )
                .then(async (response) => {
                    if (!response.ok) {
                        return false;
                    }

                    const data =
                        await response.json();

                    if (data?.ok !== true) {
                        return false;
                    }

                    try {
                        window.localStorage.setItem(
                            warmedStorageKey,
                            String(Date.now())
                        );
                    } catch (error) {
                        console.debug(
                            'No fue posible guardar la precarga completada.'
                        );
                    }

                    return true;
                })
                .catch(() => {
                    console.debug(
                        'No fue posible precargar Ollama.'
                    );

                    return false;
                })
                .finally(() => {
                    window.clearTimeout(
                        warmUpTimeout
                    );

                    try {
                        window.localStorage.removeItem(
                            warmingStorageKey
                        );
                    } catch (error) {
                        console.debug(
                            'No fue posible limpiar el estado de precarga.'
                        );
                    }

                    this.warmUpPromise = null;
                });

            return this.warmUpPromise;
        },


        /*
        |--------------------------------------------------------------------------
        | Enviar texto escrito
        |--------------------------------------------------------------------------
        |
        | Normaliza el texto introducido por el usuario y lo envía al backend junto con el contexto actual del flujo.
        |
        */

        /*
        |--------------------------------------------------------------------------
        | Enviar consulta escrita
        |--------------------------------------------------------------------------
        |
        | Normaliza y limita el mensaje visible antes de enviarlo junto con el
        | estado actual del modo IA y el contexto acumulado.
        |
        */

        async send(text = null) {
            const message = String(
                text ?? this.draft
            ).trim();

            if (
                !message
                || this.loading
            ) {
                return;
            }

            const normalizedMessage =
                message.slice(0, 500);

            this.draft = '';

            await this.performRequest(
                {
                    message:
                        normalizedMessage,

                    force_ai:
                        this.aiMode,

                    flow_context:
                        this.flowContext,
                },

                normalizedMessage
            );
        },


        /*
        |--------------------------------------------------------------------------
        | Enviar acción de botón
        |--------------------------------------------------------------------------
        |
        | Procesa acciones seleccionadas desde botones interactivos, actualiza el contexto y decide cuándo activar la precarga del modelo.
        |
        */

        /*
        |--------------------------------------------------------------------------
        | Enviar acción interactiva
        |--------------------------------------------------------------------------
        |
        | Procesa botones del flow, actualiza el contexto y delega la acción al
        | mismo flujo de solicitudes utilizado por los mensajes escritos.
        |
        */

        async sendAction(
            actionValue,
            label = null,
            actionContext = null
        ) {
            const action = String(
                actionValue
                ?? ''
            ).trim();

            if (
                !action
                || this.loading
            ) {
                return;
            }

            /*
            |------------------------------------------------------------------
            | Restaurar el menú principal original
            |------------------------------------------------------------------
            |
            | No se consulta nuevamente al backend porque su respuesta
            | convertiría el menú en quick_actions con una apariencia distinta.
            |
            | Al limpiar la conversación, el Blade vuelve a mostrar exactamente
            | las tarjetas iniciales definidas en el mensaje de bienvenida.
            |
            */

            if (action === 'menu.principal') {
                this.flowContext = {};

                this.showMainMenu(
                    label
                    ?? 'Mostrar menú'
                );

                return;
            }

            /*
             * Ollama solo se precarga cuando el usuario decide
             * utilizar la consulta libre con IA.
             *
             * No se usa await para que la navegación del flow
             * continúe sin esperar a que el modelo termine de cargar.
             */
            if (action === 'ai.enable') {
                void this.warmUpModel();
            }

            const visibleLabel = String(
                label
                ?? 'Continuar'
            ).trim();

            this.flowContext = this.mergeFlowContext(
                this.flowContext,
                actionContext
            );

            await this.performRequest(
                {
                    action,

                    flow_context:
                        this.flowContext,
                },

                visibleLabel
            );
        },


        /*
        |--------------------------------------------------------------------------
        | Mostrar menú principal
        |--------------------------------------------------------------------------
        |
        | Restablece el estado conversacional del flow y reconstruye el menú principal con sus acciones iniciales.
        |
        */

        /*
        |--------------------------------------------------------------------------
        | Restaurar menú principal
        |--------------------------------------------------------------------------
        |
        | Cancela solicitudes activas, limpia contexto y modo IA y reconstruye
        | las opciones iniciales sin consultar nuevamente al backend.
        |
        */

        showMainMenu(label = 'Mostrar menú') {
            this.cancelCurrentRequest();

            this.draft = '';
            this.loading = false;
            this.aiMode = false;
            this.streamStarted = false;
            this.flowContext = {};

            const lastMessage =
                this.messages[
                    this.messages.length - 1
                ];

            /*
             * Evitar duplicar el menú si ya es el último mensaje.
             */
            if (lastMessage?.kind === 'main_menu') {
                this.scrollBottom();

                return;
            }

            this.addMessage({
                from: 'user',
                text: String(label).trim()
                    || 'Mostrar menú',
            });

            this.addMessage({
                from: 'bot',
                kind: 'main_menu',
                text: '¿Qué necesitas gestionar hoy?',
                quick_actions: [
                    {
                        label: 'Reportar un problema',
                        description:
                            'Algo no funciona correctamente',
                        icon: 'triangle-alert',
                        action: 'flow',
                        value: 'problema.menu',
                    },

                    {
                        label: 'Solicitar un servicio',
                        description:
                            'Equipos, programas o accesos',
                        icon: 'clipboard-list',
                        action: 'flow',
                        value: 'solicitud.menu',
                    },

                    {
                        label: 'Gestionar un pase',
                        description:
                            'Pase menor o mayor a 24 horas',
                        icon: 'badge-check',
                        action: 'flow',
                        value: 'pase.menu',
                    },

                    {
                        label: 'Consultar mis gestiones',
                        description:
                            'Revisa estados y seguimientos',
                        icon: 'history',
                        action: 'status',
                        value: 'gestion.estado',
                    },

                    {
                        label: 'Escribir otra consulta',
                        description:
                            'Describe con tus propias palabras lo que necesitas',
                        icon: 'message-square-text',
                        variant: 'ai',
                        action: 'flow',
                        value: 'ai.enable',
                    },
                ],
            });

            this.saveChatHistory();

            this.$nextTick(() => {
                this.renderIcons();
                this.scrollBottom();
            });
        },


        /*
        |--------------------------------------------------------------------------
        | Iniciar una conversación nueva
        |--------------------------------------------------------------------------
        |
        | Cancela solicitudes activas, limpia mensajes, contexto e historial temporal para comenzar una conversación desde cero.
        |
        */

        /*
        |--------------------------------------------------------------------------
        | Reiniciar conversación
        |--------------------------------------------------------------------------
        |
        | Limpia estado, mensajes, historial y contexto para comenzar una sesión
        | conversacional nueva dentro de la misma pestaña.
        |
        */

        startNewConversation() {
            this.cancelCurrentRequest();

            this.draft = '';
            this.loading = false;
            this.aiMode = false;
            this.streamStarted = false;
            this.flowContext = {};
            this.messages = [];

            this.clearChatHistory();

            this.$nextTick(() => {
                this.renderIcons();

                const container =
                    this.$refs.messages;

                if (container) {
                    container.scrollTop = 0;
                }
            });
        },


        /*
        |--------------------------------------------------------------------------
        | Realizar solicitud
        |--------------------------------------------------------------------------
        |
        | Envía la petición al endpoint de streaming, crea la burbuja de respuesta, controla cancelación, timeout y manejo de errores.
        |
        */

        /*
        |--------------------------------------------------------------------------
        | Ejecutar solicitud al chatbot
        |--------------------------------------------------------------------------
        |
        | Crea la burbuja del usuario y del bot, abre la conexión de streaming y
        | controla errores, cancelación, timeout y finalización.
        |
        */

        async performRequest(
            payload,
            userVisibleText
        ) {
            if (this.loading) {
                return;
            }

            this.cancelCurrentRequest();

            /*
             * Mostrar lo que seleccionó o escribió el usuario.
             */
            this.addMessage({
                from: 'user',

                text:
                    userVisibleText,
            });

            this.loading = true;
            this.streamStarted = false;

            /*
             * Burbuja que mostrará puntos mientras procesa.
             */
            const botMessage = this.addMessage({
                from: 'bot',

                text: '',

                streaming: true,

                quick_actions: [],

                redirect: null,

                items: [],

                intent: null,

                ai: null,

                conversation_id: null,

                flow_context:
                    this.flowContext,
            });

            this.requestController =
                new AbortController();

            this.resetRequestTimeout();

            try {
                const response = await fetch(
                    this.getStreamEndpoint(),
                    {
                        method: 'POST',

                        headers: {
                            'Content-Type':
                                'application/json',

                            'Accept':
                                'application/x-ndjson',

                            'X-Requested-With':
                                'XMLHttpRequest',

                            'X-CSRF-TOKEN':
                                this.getCsrfToken(),
                        },

                        credentials:
                            'same-origin',

                        signal:
                            this.requestController.signal,

                        body:
                            JSON.stringify(
                                payload
                            ),
                    }
                );

                if (!response.ok) {
                    const errorData =
                        await this.parseErrorResponse(
                            response
                        );

                    throw new Error(
                        errorData?.message
                        ?? `Error del servidor (${response.status})`
                    );
                }

                if (!response.body) {
                    throw new Error(
                        'El navegador no pudo leer la respuesta.'
                    );
                }

                await this.consumeStream(
                    response,
                    botMessage
                );

            } catch (error) {
                console.error(
                    'Chatbot error:',
                    error
                );

                botMessage.text =
                    this.getErrorMessage(
                        error
                    );

                botMessage.quick_actions = [
                    {
                        label:
                            'Intentar nuevamente',

                        action:
                            'retry',

                        payload:
                            payload,

                        display:
                            userVisibleText,
                    },

                    {
                        label:
                            'Volver al menú',

                        action:
                            'flow',

                        value:
                            'menu.principal',
                    },
                ];

                botMessage.streaming = false;

            } finally {
                this.clearRequestTimeout();

                this.requestController = null;
                this.loading = false;
                this.streamStarted = false;

                botMessage.streaming = false;

                this.afterStreamComplete();
            }
        },


        /*
        |--------------------------------------------------------------------------
        | Consumir NDJSON
        |--------------------------------------------------------------------------
        |
        | Lee progresivamente el cuerpo de respuesta, separa eventos por línea y los entrega al procesador de eventos del stream.
        |
        */

        /*
        |--------------------------------------------------------------------------
        | Consumir stream NDJSON
        |--------------------------------------------------------------------------
        |
        | Lee fragmentos del cuerpo de respuesta, reconstruye líneas completas y
        | procesa cada evento enviado progresivamente por el backend.
        |
        */

        async consumeStream(
            response,
            botMessage
        ) {
            const reader =
                response.body.getReader();

            const decoder =
                new TextDecoder('utf-8');

            let buffer = '';
            let completed = false;

            while (true) {
                const {
                    value,
                    done,
                } = await reader.read();

                if (done) {
                    break;
                }

                /*
                 * Reiniciar el timeout mientras Laravel u Ollama
                 * continúen enviando información.
                 */
                this.resetRequestTimeout();

                buffer += decoder.decode(
                    value,
                    {
                        stream: true,
                    }
                );

                let position;

                while (
                    (
                        position =
                            buffer.indexOf('\n')
                    ) !== -1
                ) {
                    const line = buffer
                        .slice(
                            0,
                            position
                        )
                        .trim();

                    buffer = buffer.slice(
                        position + 1
                    );

                    /*
                     * Ignorar el relleno de espacios enviado
                     * por el controlador.
                     */
                    if (!line) {
                        continue;
                    }

                    const event =
                        this.parseStreamEvent(
                            line
                        );

                    if (!event) {
                        continue;
                    }

                    if (
                        this.handleStreamEvent(
                            event,
                            botMessage
                        )
                    ) {
                        completed = true;
                    }
                }
            }

            buffer += decoder.decode();

            if (buffer.trim()) {
                const event =
                    this.parseStreamEvent(
                        buffer.trim()
                    );

                if (
                    event
                    && this.handleStreamEvent(
                        event,
                        botMessage
                    )
                ) {
                    completed = true;
                }
            }

            if (!completed) {
                throw new Error(
                    'La conexión terminó antes de completar la respuesta.'
                );
            }
        },


        /*
        |--------------------------------------------------------------------------
        | Interpretar línea NDJSON
        |--------------------------------------------------------------------------
        |
        | Convierte una línea del stream en un evento válido y descarta contenido malformado sin detener la conversación.
        |
        */

        /*
        |--------------------------------------------------------------------------
        | Parsear evento NDJSON
        |--------------------------------------------------------------------------
        |
        | Convierte una línea JSON en un evento válido y descarta datos
        | malformados sin interrumpir el resto del stream.
        |
        */

        parseStreamEvent(line) {
            try {
                const event =
                    JSON.parse(line);

                if (
                    !event
                    || typeof event.type !== 'string'
                ) {
                    return null;
                }

                return event;

            } catch (error) {
                console.warn(
                    'Evento NDJSON inválido:',
                    line
                );

                return null;
            }
        },


        /*
        |--------------------------------------------------------------------------
        | Procesar evento
        |--------------------------------------------------------------------------
        |
        | Interpreta eventos start, chunk, complete y error recibidos desde el backend durante el streaming.
        |
        */

        /*
        |--------------------------------------------------------------------------
        | Manejar evento del stream
        |--------------------------------------------------------------------------
        |
        | Interpreta eventos de inicio, mantenimiento de conexión, respuesta
        | completa y error recibidos desde el backend.
        |
        */

        handleStreamEvent(
            event,
            botMessage
        ) {
            switch (event.type) {
                case 'start':
                    this.streamStarted = true;

                    return false;

                case 'chunk':
                    /*
                     * El stream mantiene la conexión activa, pero no
                     * pintamos cada token para evitar lag.
                     */
                    this.streamStarted = true;

                    return false;

                case 'complete':
                    this.applyCompleteResponse(
                        botMessage,
                        event.data
                    );

                    return true;

                case 'error':
                    throw new Error(
                        event.data?.message
                        ?? 'No pude procesar tu solicitud.'
                    );

                default:
                    return false;
            }
        },


        /*
        |--------------------------------------------------------------------------
        | Aplicar respuesta final
        |--------------------------------------------------------------------------
        |
        | Actualiza la burbuja del bot, acciones rápidas, redirecciones, metadatos, contexto del flow y modo IA con la respuesta completa.
        |
        */

        /*
        |--------------------------------------------------------------------------
        | Aplicar respuesta completa
        |--------------------------------------------------------------------------
        |
        | Transfiere la respuesta final al mensaje reactivo y sincroniza acciones,
        | redirección, contexto, metadatos y estado del modo IA.
        |
        */

        applyCompleteResponse(
            botMessage,
            data
        ) {
            if (
                !data
                || typeof data !== 'object'
            ) {
                throw new Error(
                    'El servidor devolvió una respuesta inválida.'
                );
            }

            botMessage.text =
                typeof data.message === 'string'
                && data.message.trim()
                    ? data.message
                    : 'No recibí una respuesta válida.';

            botMessage.quick_actions =
                Array.isArray(
                    data.quick_actions
                )
                    ? data.quick_actions
                    : [];

            botMessage.redirect =
                this.normalizeRedirect(
                    data.redirect
                );

            botMessage.items =
                Array.isArray(data.items)
                    ? data.items
                    : [];

            botMessage.intent =
                data.intent
                ?? null;

            botMessage.ai =
                data.ai
                ?? null;

            botMessage.conversation_id =
                data.conversation_id
                ?? null;

            this.flowContext = this.prepareFlowContext(
                data.flow_context
                ?? this.flowContext
            );

            botMessage.flow_context =
                this.flowContext;

            botMessage.streaming = false;

            /*
             * Activar o desactivar el envío directo a Ollama.
             */
            this.aiMode =
                data.mode === 'ai';

            /*
             * Si se activó IA, enfocar el campo para que
             * el usuario describa su consulta.
             */
            if (this.aiMode) {
                this.$nextTick(() => {
                    this.$refs.input
                        ?.focus();
                });
            }
        },


        /*
        |--------------------------------------------------------------------------
        | Agregar mensaje
        |--------------------------------------------------------------------------
        |
        | Normaliza la estructura de un mensaje antes de incorporarlo al arreglo reactivo de la conversación.
        |
        */

        /*
        |--------------------------------------------------------------------------
        | Registrar mensaje
        |--------------------------------------------------------------------------
        |
        | Construye una estructura consistente antes de agregar el mensaje al
        | historial reactivo de la conversación.
        |
        */

        addMessage(message) {
            const newMessage = {
                id:
                    this.generateMessageId(),

                from:
                    message.from
                    ?? 'bot',

                kind:
                    message.kind
                    ?? null,

                text:
                    message.text
                    ?? '',

                streaming:
                    Boolean(
                        message.streaming
                    ),

                quick_actions:
                    Array.isArray(
                        message.quick_actions
                    )
                        ? message.quick_actions
                        : [],

                redirect:
                    message.redirect
                    ?? null,

                items:
                    Array.isArray(
                        message.items
                    )
                        ? message.items
                        : [],

                intent:
                    message.intent
                    ?? null,

                ai:
                    message.ai
                    ?? null,

                conversation_id:
                    message.conversation_id
                    ?? null,

                flow_context:
                    this.prepareFlowContext(
                        message.flow_context
                        ?? {}
                    ),
            };

            this.messages.push(
                newMessage
            );

            const reactiveMessage =
                this.messages[
                    this.messages.length - 1
                ];

            this.afterMessageAdded();

            return reactiveMessage;
        },


        /*
        |--------------------------------------------------------------------------
        | Ejecutar botón
        |--------------------------------------------------------------------------
        |
        | Despacha acciones interactivas hacia flow, reintentos, mensajes, Helpdesk, redirecciones internas o enlaces externos.
        |
        */

        /*
        |--------------------------------------------------------------------------
        | Ejecutar acción del mensaje
        |--------------------------------------------------------------------------
        |
        | Resuelve acciones de flow, reintento, envío, Helpdesk, redirecciones
        | internas, menús y enlaces externos.
        |
        */

        executeAction(
            action,
            message = null
        ) {
            if (
                !action
                || this.loading
            ) {
                return;
            }

            const actionType = String(
                action.action
                ?? action.type
                ?? 'send'
            )
                .trim()
                .toLowerCase();

            /*
             * Acción interactiva exacta.
             */
            if (
                actionType === 'flow'
                || actionType === 'status'
            ) {
                this.sendAction(
                    action.value,
                    action.label,
                    action.context
                    ?? message?.flow_context
                    ?? this.flowContext
                );

                return;
            }

            /*
             * Reintentar solicitud anterior.
             */
            if (actionType === 'retry') {
                this.performRequest(
                    action.payload
                    ?? {},

                    action.display
                    ?? 'Intentar nuevamente'
                );

                return;
            }

            /*
             * Compatibilidad con respuestas anteriores.
             */
            if (actionType === 'send') {
                const value = String(
                    action.value
                    ?? action.message
                    ?? action.label
                    ?? ''
                ).trim();

                if (value) {
                    this.send(value);
                }

                return;
            }

            /*
             * Contactar a Helpdesk.
             *
             * Outlook Web se abre en una pestaña nueva con
             * destinatario, asunto y cuerpo ya preparados.
             * Si el navegador bloquea la pestaña o la URL no
             * es válida, se utiliza mailto: como respaldo.
             */
            if (actionType === 'helpdesk') {
                const outlookUrl = String(
                    action.url
                    ?? action.href
                    ?? ''
                ).trim();

                const fallbackUrl = String(
                    action.fallback_url
                    ?? ''
                ).trim();

                let opened = null;

                if (
                    outlookUrl.startsWith(
                        'https://outlook.office.com/'
                    )
                ) {
                    opened = window.open(
                        outlookUrl,
                        '_blank',
                        'noopener,noreferrer'
                    );
                }

                if (
                    !opened
                    && fallbackUrl
                        .toLowerCase()
                        .startsWith('mailto:')
                ) {
                    window.location.href =
                        fallbackUrl;
                }

                return;
            }

            /*
             * Redirección interna.
             */
            if (
                actionType === 'redirect'
                || actionType === 'link'
                || actionType === 'url'
            ) {
                const url =
                    action.url
                    ?? action.href
                    ?? message?.redirect?.url
                    ?? null;

                if (url) {
                    window.location.assign(
                        url
                    );
                }

                return;
            }

            /*
             * Compatibilidad con menú anterior.
             */
            if (
                actionType === 'menu'
                || actionType === 'show_menu'
            ) {
                this.sendAction(
                    'menu.principal',
                    'Mostrar menú'
                );

                return;
            }

            /*
             * Enlace externo.
             */
            if (actionType === 'external') {
                const url =
                    action.url
                    ?? action.href
                    ?? null;

                if (url) {
                    window.open(
                        url,
                        '_blank',
                        'noopener,noreferrer'
                    );
                }
            }
        },


        /*
        |--------------------------------------------------------------------------
        | Endpoint
        |--------------------------------------------------------------------------
        |
        | Obtiene desde el documento la URL configurada para el endpoint de streaming del chatbot.
        |
        */

        /*
        |--------------------------------------------------------------------------
        | Obtener endpoint de streaming
        |--------------------------------------------------------------------------
        |
        | Recupera la URL configurada en el meta tag y utiliza la ruta estándar
        | del chatbot como respaldo.
        |
        */

        getStreamEndpoint() {
            return document
                .querySelector(
                    'meta[name="chatbot-stream-endpoint"]'
                )
                ?.getAttribute('content')
                ?.trim()
                || '/chatbot/stream';
        },


        /*
        |--------------------------------------------------------------------------
        | CSRF
        |--------------------------------------------------------------------------
        |
        | Recupera el token CSRF utilizado para autenticar las solicitudes AJAX hacia Laravel.
        |
        */

        /*
        |--------------------------------------------------------------------------
        | Obtener token CSRF
        |--------------------------------------------------------------------------
        |
        | Recupera el token de seguridad requerido por Laravel para solicitudes
        | POST realizadas desde el widget.
        |
        */

        getCsrfToken() {
            return document
                .querySelector(
                    'meta[name="csrf-token"]'
                )
                ?.getAttribute('content')
                || '';
        },


        /*
        |--------------------------------------------------------------------------
        | Interpretar error HTTP
        |--------------------------------------------------------------------------
        |
        | Extrae mensajes de validación o errores JSON y proporciona un mensaje genérico cuando la respuesta no es válida.
        |
        */

        /*
        |--------------------------------------------------------------------------
        | Interpretar respuesta de error
        |--------------------------------------------------------------------------
        |
        | Extrae mensajes de validación o de servidor cuando la respuesta HTTP
        | no fue satisfactoria.
        |
        */

        async parseErrorResponse(
            response
        ) {
            const contentType =
                response.headers.get(
                    'content-type'
                )
                ?? '';

            if (
                contentType.includes(
                    'application/json'
                )
            ) {
                try {
                    const data =
                        await response.json();

                    const validationMessage =
                        data?.errors?.message?.[0]
                        ?? data?.errors?.action?.[0];

                    return {
                        message:
                            validationMessage
                            ?? data?.message
                            ?? null,
                    };

                } catch (error) {
                    return {
                        message: null,
                    };
                }
            }

            return {
                message:
                    'El servidor devolvió una respuesta inválida.',
            };
        },


        /*
        |--------------------------------------------------------------------------
        | Normalizar redirección
        |--------------------------------------------------------------------------
        |
        | Convierte distintos formatos de redirección a una estructura uniforme utilizada por el frontend.
        |
        */

        /*
        |--------------------------------------------------------------------------
        | Normalizar redirección
        |--------------------------------------------------------------------------
        |
        | Convierte una URL simple o un objeto de redirección a una estructura
        | uniforme consumida por la interfaz.
        |
        */

        normalizeRedirect(redirect) {
            if (!redirect) {
                return null;
            }

            if (
                typeof redirect === 'string'
            ) {
                return {
                    url:
                        redirect,

                    label:
                        'Abrir módulo',
                };
            }

            if (
                typeof redirect === 'object'
                && redirect.url
            ) {
                return {
                    url:
                        redirect.url,

                    label:
                        redirect.label
                        ?? 'Abrir módulo',
                };
            }

            return null;
        },


        /*
        |--------------------------------------------------------------------------
        | Mensajes de error
        |--------------------------------------------------------------------------
        |
        | Transforma errores de cancelación, conectividad o servidor en mensajes comprensibles para el usuario.
        |
        */

        /*
        |--------------------------------------------------------------------------
        | Obtener mensaje de error
        |--------------------------------------------------------------------------
        |
        | Convierte errores técnicos de cancelación, red o servidor en mensajes
        | comprensibles para el usuario final.
        |
        */

        getErrorMessage(error) {
            if (
                error?.name === 'AbortError'
            ) {
                return 'La respuesta tardó demasiado o fue cancelada.';
            }

            if (
                error instanceof TypeError
                && String(
                    error.message
                )
                    .toLowerCase()
                    .includes('fetch')
            ) {
                return 'No pude conectarme con el servidor. Revisa tu conexión.';
            }

            if (
                typeof error?.message === 'string'
                && error.message.trim()
            ) {
                return error.message;
            }

            return 'No pude procesar tu solicitud en este momento.';
        },


        /*
        |--------------------------------------------------------------------------
        | Timeout
        |--------------------------------------------------------------------------
        |
        | Administra el límite máximo de espera de las solicitudes y reinicia el contador mientras el stream continúa activo.
        |
        */

        /*
        |--------------------------------------------------------------------------
        | Reiniciar timeout de solicitud
        |--------------------------------------------------------------------------
        |
        | Restablece el límite de espera cada vez que el backend continúa
        | enviando actividad por el stream.
        |
        */

        resetRequestTimeout() {
            this.clearRequestTimeout();

            this.requestTimeout =
                window.setTimeout(
                    () => {
                        this.requestController
                            ?.abort();
                    },
                    150000
                );
        },

        /*
        |--------------------------------------------------------------------------
        | Limpiar timeout
        |--------------------------------------------------------------------------
        |
        | Cancela el temporizador activo asociado a la solicitud actual.
        |
        */

        clearRequestTimeout() {
            if (
                this.requestTimeout !== null
            ) {
                window.clearTimeout(
                    this.requestTimeout
                );

                this.requestTimeout = null;
            }
        },


        /*
        |--------------------------------------------------------------------------
        | Cancelar solicitud
        |--------------------------------------------------------------------------
        |
        | Interrumpe la petición actualmente activa y limpia su timeout asociado.
        |
        */

        /*
        |--------------------------------------------------------------------------
        | Cancelar solicitud actual
        |--------------------------------------------------------------------------
        |
        | Aborta la petición activa y elimina cualquier timeout pendiente antes
        | de iniciar una nueva operación.
        |
        */

        cancelCurrentRequest() {
            if (this.requestController) {
                this.requestController.abort();

                this.requestController = null;
            }

            this.clearRequestTimeout();
        },


        /*
        |--------------------------------------------------------------------------
        | Historial temporal de la pestaña
        |--------------------------------------------------------------------------
        |
        | sessionStorage conserva la conversación al navegar por el Portal TI,
        | pero el navegador la elimina al cerrar la pestaña. La clave incluye
        | al usuario autenticado desde el Blade para no mezclar conversaciones.
        |
        */

        /*
        |--------------------------------------------------------------------------
        | Guardar historial temporal
        |--------------------------------------------------------------------------
        |
        | Persiste mensajes válidos, modo IA y contexto en sessionStorage,
        | limitando la cantidad máxima de elementos conservados.
        |
        */

        saveChatHistory() {
            try {
                const messages = this.messages
                    .filter((message) => {
                        return !message.streaming
                            && (
                                String(message.text ?? '').trim()
                                || message.kind === 'main_menu'
                            );
                    })
                    .slice(-this.historyMaxMessages)
                    .map((message) => ({
                        ...message,
                        streaming: false,
                    }));

                window.sessionStorage.setItem(
                    this.historyStorageKey,
                    JSON.stringify({
                        version: 2,
                        saved_at: Date.now(),
                        ai_mode: Boolean(this.aiMode),

                        flow_context:
                            this.flowContext,

                        messages,
                    })
                );

            } catch (error) {
                console.debug(
                    'No fue posible guardar el historial temporal del chatbot.'
                );
            }
        },


        /*
        |--------------------------------------------------------------------------
        | Restaurar historial temporal
        |--------------------------------------------------------------------------
        |
        | Recupera y valida la conversación almacenada en la pestaña antes de
        | reconstruir mensajes, contexto y modo IA.
        |
        */

        restoreChatHistory() {
            try {
                const stored =
                    window.sessionStorage.getItem(
                        this.historyStorageKey
                    );

                if (!stored) {
                    return;
                }

                const history = JSON.parse(stored);

                if (
                    ![1, 2].includes(history?.version)
                    || !Array.isArray(history.messages)
                ) {
                    this.clearChatHistory();

                    return;
                }

                this.messages = history.messages
                    .slice(-this.historyMaxMessages)
                    .filter((message) => {
                        return message
                            && typeof message === 'object'
                            && ['user', 'bot'].includes(message.from);
                    })
                    .map((message) => ({
                        id: message.id
                            ?? this.generateMessageId(),
                        from: message.from,
                        kind: message.kind
                            ?? null,
                        text: String(message.text ?? ''),
                        streaming: false,
                        quick_actions: Array.isArray(message.quick_actions)
                            ? message.quick_actions
                            : [],
                        redirect: message.redirect
                            ?? null,
                        items: Array.isArray(message.items)
                            ? message.items
                            : [],
                        intent: message.intent
                            ?? null,
                        ai: message.ai
                            ?? null,
                        conversation_id: message.conversation_id
                            ?? null,

                        flow_context:
                            this.prepareFlowContext(
                                message.flow_context
                                ?? {}
                            ),
                    }));

                this.aiMode = Boolean(
                    history.ai_mode
                );

                this.flowContext =
                    history.version === 2
                        ? this.prepareFlowContext(
                            history.flow_context
                            ?? {}
                        )
                        : {};

            } catch (error) {
                this.clearChatHistory();
            }
        },


        /*
        |--------------------------------------------------------------------------
        | Limpiar historial temporal
        |--------------------------------------------------------------------------
        |
        | Elimina de sessionStorage la conversación asociada al widget actual.
        |
        */

        clearChatHistory() {
            try {
                window.sessionStorage.removeItem(
                    this.historyStorageKey
                );

            } catch (error) {
                console.debug(
                    'No fue posible limpiar el historial temporal del chatbot.'
                );
            }
        },


        /*
        |--------------------------------------------------------------------------
        | Preparar contexto del flujo
        |--------------------------------------------------------------------------
        |
        | Filtra, normaliza y limita las claves permitidas del contexto antes de reutilizarlas en nuevas acciones o solicitudes.
        |
        */

        /*
        |--------------------------------------------------------------------------
        | Preparar contexto del flow
        |--------------------------------------------------------------------------
        |
        | Filtra claves permitidas, normaliza valores y aplica límites de
        | longitud antes de reutilizar el contexto en nuevas solicitudes.
        |
        */

        prepareFlowContext(context) {
            if (
                !context
                || typeof context !== 'object'
                || Array.isArray(context)
            ) {
                return {};
            }

            const allowedKeys = [
                'titulo',
                'descripcion',
                'tiempo_problema',
                'afectacion',
                'equipo',
                'ubicacion',
                'categoria',
                'asunto',
                'tipo_equipo',
                'accesorio',
                'programa',
                'sistema',
                'tipo_acceso',
                'justificacion',
                'usuario_afectado',
                'equipo_actual',
                'motivo_cambio',

                /*
                 * Texto interno utilizado por la IA para extraer los datos
                 * del formulario. No se envía como parámetro al formulario.
                 */
                'prefill_source',
            ];

            const prepared = {};

            allowedKeys.forEach((key) => {
                const value = context[key];

                if (
                    value === null
                    || value === undefined
                ) {
                    return;
                }

                /*
                 * La descripción original puede necesitar más espacio para que
                 * el extractor identifique correctamente todos los campos.
                 * Los demás valores continúan limitados a 1000 caracteres.
                 */
                const maxLength =
                    key === 'prefill_source'
                        ? 3000
                        : 1000;

                const normalized = String(
                    value
                )
                    .trim()
                    .slice(0, maxLength);

                if (normalized) {
                    prepared[key] = normalized;
                }
            });

            return prepared;
        },


        /*
        |--------------------------------------------------------------------------
        | Combinar contexto
        |--------------------------------------------------------------------------
        |
        | Fusiona de forma segura el contexto existente con los nuevos datos recibidos desde una acción.
        |
        */

        /*
        |--------------------------------------------------------------------------
        | Combinar contexto del flow
        |--------------------------------------------------------------------------
        |
        | Fusiona de forma segura el contexto anterior con la nueva información
        | recibida desde una acción interactiva.
        |
        */

        mergeFlowContext(
            currentContext,
            newContext
        ) {
            return {
                ...this.prepareFlowContext(
                    currentContext
                ),

                ...this.prepareFlowContext(
                    newContext
                ),
            };
        },


        /*
        |--------------------------------------------------------------------------
        | Identificador
        |--------------------------------------------------------------------------
        |
        | Genera identificadores únicos para los mensajes utilizando tiempo actual y contador interno.
        |
        */

        /*
        |--------------------------------------------------------------------------
        | Generar identificador de mensaje
        |--------------------------------------------------------------------------
        |
        | Construye un identificador único utilizando timestamp y contador
        | interno del widget.
        |
        */

        generateMessageId() {
            this.messageCounter++;

            return [
                'chatbot',
                Date.now(),
                this.messageCounter,
            ].join('-');
        },


        /*
        |--------------------------------------------------------------------------
        | Después de agregar mensaje
        |--------------------------------------------------------------------------
        |
        | Persiste el historial y actualiza iconos y desplazamiento después de incorporar un mensaje.
        |
        */

        /*
        |--------------------------------------------------------------------------
        | Procesar mensaje agregado
        |--------------------------------------------------------------------------
        |
        | Guarda el historial y actualiza iconos y scroll después de insertar un
        | nuevo mensaje.
        |
        */

        afterMessageAdded() {
            this.saveChatHistory();

            this.$nextTick(() => {
                this.renderIcons();
                this.scrollBottom();
            });
        },


        /*
        |--------------------------------------------------------------------------
        | Después de respuesta
        |--------------------------------------------------------------------------
        |
        | Persiste el estado final de la conversación y actualiza la interfaz después de completar un stream.
        |
        */

        /*
        |--------------------------------------------------------------------------
        | Procesar stream completado
        |--------------------------------------------------------------------------
        |
        | Persiste el resultado final y sincroniza iconos y desplazamiento al
        | terminar una respuesta.
        |
        */

        afterStreamComplete() {
            this.saveChatHistory();

            this.$nextTick(() => {
                this.renderIcons();
                this.scrollBottom();
            });
        },


        /*
        |--------------------------------------------------------------------------
        | Lucide
        |--------------------------------------------------------------------------
        |
        | Renderiza iconos de forma segura y realiza un reintento breve cuando la librería todavía no está disponible.
        |
        */

        /*
        |--------------------------------------------------------------------------
        | Renderizar iconos
        |--------------------------------------------------------------------------
        |
        | Inicializa Lucide de forma segura y realiza un único reintento breve
        | cuando la librería todavía no está disponible.
        |
        */

        renderIcons(
            allowRetry = true
        ) {
            if (
                !window.lucide
                || typeof window.lucide.createIcons
                    !== 'function'
            ) {
                if (
                    allowRetry
                    && this.iconRetryTimeout === null
                ) {
                    this.iconRetryTimeout =
                        window.setTimeout(
                            () => {
                                this.iconRetryTimeout = null;

                                this.renderIcons(
                                    false
                                );
                            },
                            150
                        );
                }

                return;
            }

            if (
                this.iconRetryTimeout !== null
            ) {
                window.clearTimeout(
                    this.iconRetryTimeout
                );

                this.iconRetryTimeout = null;
            }

            try {
                window.lucide.createIcons();

            } catch (error) {
                console.warn(
                    'No fue posible actualizar los iconos.'
                );
            }
        },


        /*
        |--------------------------------------------------------------------------
        | Scroll
        |--------------------------------------------------------------------------
        |
        | Desplaza el contenedor de mensajes al final utilizando requestAnimationFrame para evitar actualizaciones redundantes.
        |
        */

        /*
        |--------------------------------------------------------------------------
        | Desplazar conversación al final
        |--------------------------------------------------------------------------
        |
        | Utiliza requestAnimationFrame para evitar múltiples actualizaciones de
        | scroll dentro del mismo ciclo de renderizado.
        |
        */

        scrollBottom() {
            if (
                this.scrollFrame !== null
            ) {
                return;
            }

            this.scrollFrame =
                window.requestAnimationFrame(
                    () => {
                        this.scrollFrame = null;

                        const container =
                            this.$refs.messages;

                        if (!container) {
                            return;
                        }

                        container.scrollTop =
                            container.scrollHeight;
                    }
                );
        },

    };
};