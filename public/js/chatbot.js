/*
|--------------------------------------------------------------------------
| Chatbot interactivo del Portal TI
|--------------------------------------------------------------------------
|
| Modos:
| - flow: navegación mediante botones
| - ai: texto libre enviado a Ollama
|
*/

window.chatbotWidget = function (options = {}) {
    return {

        /*
        |--------------------------------------------------------------------------
        | Estado
        |--------------------------------------------------------------------------
        */

        draft: '',

        loading: false,

        aiMode: false,

        streamStarted: false,

        messages: [],

        historyStorageKey: String(
            options.storageKey
            ?? 'portal-it-chatbot-history'
        ),

        historyMaxMessages: 80,

        requestController: null,

        requestTimeout: null,

        messageCounter: 0,

        scrollFrame: null,


        /*
        |--------------------------------------------------------------------------
        | Inicialización
        |--------------------------------------------------------------------------
        */

        init() {
            this.restoreChatHistory();

            this.$nextTick(() => {
                this.renderIcons();

                if (this.messages.length) {
                    this.scrollBottom();
                }
            });

            /*
             * Precargar Ollama silenciosamente.
             */
            window.setTimeout(
                () => {
                    this.warmUpModel();
                },
                500
            );
        },


        /*
        |--------------------------------------------------------------------------
        | Precargar Ollama
        |--------------------------------------------------------------------------
        */

        async warmUpModel() {
            const endpoint = document
                .querySelector(
                    'meta[name="chatbot-warmup-endpoint"]'
                )
                ?.getAttribute('content')
                ?.trim();

            if (!endpoint) {
                return;
            }

            const storageKey =
                'portal-it-chatbot-warmed-at';

            const warmedAt = Number(
                window.localStorage.getItem(
                    storageKey
                )
                    ?? 0
            );

            const twentyMinutes =
                20 * 60 * 1000;

            if (
                warmedAt
                && Date.now() - warmedAt < twentyMinutes
            ) {
                return;
            }

            try {
                const response = await fetch(
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
                    }
                );

                if (response.ok) {
                    window.localStorage.setItem(
                    storageKey,
                    String(Date.now())
                );
                }

            } catch (error) {
                /*
                 * La precarga es opcional.
                 */
                console.debug(
                    'No fue posible precargar Ollama.'
                );
            }
        },


        /*
        |--------------------------------------------------------------------------
        | Enviar texto escrito
        |--------------------------------------------------------------------------
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
                },

                normalizedMessage
            );
        },


        /*
        |--------------------------------------------------------------------------
        | Enviar acción de botón
        |--------------------------------------------------------------------------
        */

        async sendAction(
            actionValue,
            label = null
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
                this.showMainMenu(
                    label
                    ?? 'Mostrar menú'
                );

                return;
            }

            const visibleLabel = String(
                label
                ?? 'Continuar'
            ).trim();

            await this.performRequest(
                {
                    action,
                },

                visibleLabel
            );
        },


        /*
        |--------------------------------------------------------------------------
        | Mostrar menú principal
        |--------------------------------------------------------------------------
        */

        showMainMenu(label = 'Mostrar menú') {
            this.cancelCurrentRequest();

            this.draft = '';
            this.loading = false;
            this.aiMode = false;
            this.streamStarted = false;

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
                text: '¿En qué puedo ayudarte? Selecciona una opción para continuar.',
                quick_actions: [
                    {
                        label: 'Tengo un problema',
                        description: 'Algo no funciona correctamente',
                        icon: 'triangle-alert',
                        action: 'flow',
                        value: 'problema.menu',
                    },
                    {
                        label: 'Necesito un servicio',
                        description: 'Equipos, programas o accesos',
                        icon: 'clipboard-list',
                        action: 'flow',
                        value: 'solicitud.menu',
                    },
                    {
                        label: 'Necesito un pase',
                        description: 'Pase menor o mayor a 24 horas',
                        icon: 'badge-check',
                        action: 'flow',
                        value: 'pase.menu',
                    },
                    {
                        label: 'Consultar gestiones',
                        description: 'Revisa estados y seguimientos',
                        icon: 'history',
                        action: 'status',
                        value: 'gestion.estado',
                    },
                    {
                        label: 'Hacer una pregunta',
                        description: 'Describe lo que necesitas con tus propias palabras',
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
        */

        startNewConversation() {
            this.cancelCurrentRequest();

            this.draft = '';
            this.loading = false;
            this.aiMode = false;
            this.streamStarted = false;
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

            this.afterStreamComplete();
        },


        /*
        |--------------------------------------------------------------------------
        | Agregar mensaje
        |--------------------------------------------------------------------------
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
                    action.label
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
        */

        resetRequestTimeout() {
            this.clearRequestTimeout();

            this.requestTimeout =
                window.setTimeout(
                    () => {
                        this.requestController
                            ?.abort();
                    },
                    60000
                );
        },

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
                        version: 1,
                        saved_at: Date.now(),
                        ai_mode: Boolean(this.aiMode),
                        messages,
                    })
                );

            } catch (error) {
                console.debug(
                    'No fue posible guardar el historial temporal del chatbot.'
                );
            }
        },


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
                    history?.version !== 1
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
                    }));

                this.aiMode = Boolean(
                    history.ai_mode
                );

            } catch (error) {
                this.clearChatHistory();
            }
        },


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
        | Identificador
        |--------------------------------------------------------------------------
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
        */

        renderIcons() {
            if (!window.lucide) {
                return;
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