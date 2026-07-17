<?php

// config/chatbot.php
//
// Configuración central del Asistente Virtual TI.
// Aquí vive todo lo "editable sin tocar código": palabras clave por intención,
// textos de respuesta y el mapeo hacia las rutas reales de cada módulo del portal.
//
// Cuando en el futuro se integre un motor de IA / base de conocimiento, este
// archivo seguirá siendo útil (los quick_actions y las rutas de módulos no
// cambian), solo se reemplazará el "reconocedor" de intención por uno basado
// en IA (ver App\Services\Chatbot\IntentRecognizerInterface).

return [

    // Umbral mínimo de coincidencias para aceptar una intención por palabras clave.
    // Si ninguna intención supera este puntaje, se responde con el menú de aclaración.
    'min_score' => 1,

    // Nombre del usuario que se usa si no hay uno autenticado (no debería pasar,
    // el chatbot solo debe cargarse dentro de rutas con middleware auth).
    'fallback_name' => 'usuario',

    // Mapeo intención => rutas nombradas reales del portal.
    // AJUSTA estos nombres de ruta a los que ya existan en tu routes/web.php.
    'modules' => [
        'incidencia' => [
            'label'  => 'Reportar incidencia',
            'create' => 'incidencias.create',
            'index'  => 'incidencias.index',
        ],
        'solicitud' => [
            'label'  => 'Solicitud de servicio',
            'create' => 'solicitudes.create',
            'index'  => 'solicitudes.index',
        ],
        'pase_menor_24h' => [
            'label'  => 'Pase de acceso (menor a 24h)',
            'create' => 'pases.create',
            'index'  => 'pases.index',
        ],
        'autorizacion_memorando' => [
            'label'  => 'Autorización por memorando (mayor a 24h)',
            'create' => 'autorizaciones.create',
            'index'  => 'autorizaciones.index',
        ],
    ],

    // Palabras clave por intención. Todo en minúsculas y sin tildes
    // (el recognizer normaliza el texto de entrada antes de comparar).
    'keywords' => [

        'incidencia' => [
            'incidencia', 'falla', 'fallo', 'error', 'no funciona', 'no sirve',
            'no enciende', 'no prende', 'esta danado', 'dañado', 'danado',
            'lento', 'se cayo', 'se cayó', 'no carga', 'no imprime', 'virus',
            'pantalla azul', 'no conecta', 'sin internet', 'reportar', 'problema',
            'se dano', 'se daño',
        ],

        'solicitud' => [
            'solicitud', 'solicitar', 'necesito', 'requiero', 'quiero pedir',
            'nuevo equipo', 'instalar', 'instalacion', 'instalación',
            'requerimiento', 'dar de alta', 'crear usuario', 'nueva cuenta',
            'licencia', 'software nuevo', 'equipo nuevo', 'cambio de equipo',
        ],

        'pase_menor_24h' => [
            'pase', 'acceso temporal', 'permiso de acceso', 'entrar hoy',
            'visita', 'proveedor', 'menor a 24 horas', 'menos de 24 horas',
            'acceso por un dia', 'acceso por un día', 'ingreso temporal',
            'hoy necesito entrar', 'acceso puntual',
        ],

        'autorizacion_memorando' => [
            'memorando', 'autorizacion', 'autorización', 'autorizar acceso',
            'acceso permanente', 'mas de 24 horas', 'más de 24 horas',
            'acceso prolongado', 'autorizar ingreso', 'memo de autorizacion',
            'memo de autorización', 'acceso por varios dias', 'acceso por varios días',
        ],

        'consultar_estado' => [
            'estado', 'consultar', 'seguimiento', 'como va', 'cómo va',
            'mi solicitud', 'mi incidencia', 'mi pase', 'mi memorando',
            'mis gestiones', 'que paso con', 'qué pasó con', 'ya resolvieron',
            'ha sido aprobado', 'fue aprobado',
        ],

        'saludo' => [
            'hola', 'buenos dias', 'buenos días', 'buenas tardes', 'buenas noches',
            'que tal', 'qué tal', 'ayuda', 'menu', 'menú',
        ],
    ],
];