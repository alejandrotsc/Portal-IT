-- ============================================================
-- SISTEMA PORTAL TI - TVC
-- BASE DE DATOS COMPLETA
--
-- PostgreSQL
--
-- Roles:
-- Usuario
-- UsuarioTI
-- Administrador
--
-- ============================================================



-- ============================================================
-- LIMPIEZA
-- ============================================================


DROP TABLE IF EXISTS memorando_historial CASCADE;
DROP TABLE IF EXISTS aprobaciones CASCADE;
DROP TABLE IF EXISTS memorando_archivos CASCADE;
DROP TABLE IF EXISTS memorando_articulos CASCADE;
DROP TABLE IF EXISTS memorandos CASCADE;
DROP TABLE IF EXISTS memorando_tipos CASCADE;
DROP TABLE IF EXISTS folio_counters CASCADE;
DROP TABLE IF EXISTS usuarios CASCADE;
DROP TABLE IF EXISTS roles CASCADE;




-- ============================================================
-- ROLES
-- ============================================================


CREATE TABLE roles (

    id BIGSERIAL PRIMARY KEY,

    nombre VARCHAR(50)
        NOT NULL UNIQUE,

    descripcion TEXT,


    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

);





-- ============================================================
-- USUARIOS
-- ============================================================


CREATE TABLE usuarios (

    id BIGSERIAL PRIMARY KEY,


    nombre VARCHAR(200)
        NOT NULL,


    correo VARCHAR(200)
        NOT NULL
        UNIQUE,


    correo_verificado_at TIMESTAMP
        NULL,


    rol_id BIGINT
        NOT NULL,


    activo BOOLEAN
        NOT NULL
        DEFAULT TRUE,


    created_at TIMESTAMP
        NOT NULL
        DEFAULT CURRENT_TIMESTAMP,


    updated_at TIMESTAMP
        NOT NULL
        DEFAULT CURRENT_TIMESTAMP,


    CONSTRAINT usuarios_rol_id_foreign

        FOREIGN KEY (rol_id)

        REFERENCES roles(id)

);

CREATE TABLE tokens_autenticacion (

    id BIGSERIAL PRIMARY KEY,

    usuario_id BIGINT
        NOT NULL,

    correo VARCHAR(200)
        NOT NULL,

    token_hash VARCHAR(255)
        NOT NULL,

    tipo VARCHAR(30)
        NOT NULL,

    expires_at TIMESTAMP
        NOT NULL,

    used_at TIMESTAMP
        NULL,

    attempts SMALLINT
        NOT NULL DEFAULT 0,

    created_at TIMESTAMP
        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP
        NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT tokens_autenticacion_usuario_fk
        FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id)
        ON DELETE CASCADE,

    CONSTRAINT tokens_autenticacion_tipo_check
        CHECK (tipo IN ('registro', 'login')),

    CONSTRAINT tokens_autenticacion_attempts_check
        CHECK (attempts >= 0)
);

CREATE INDEX tokens_autenticacion_correo_tipo_idx
    ON tokens_autenticacion (correo, tipo);

CREATE INDEX tokens_autenticacion_usuario_idx
    ON tokens_autenticacion (usuario_id);

CREATE INDEX tokens_autenticacion_expires_at_idx
    ON tokens_autenticacion (expires_at);


CREATE TABLE incidencias (

    id BIGSERIAL PRIMARY KEY,


    codigo VARCHAR(255) NOT NULL UNIQUE,


    usuario_id BIGINT NOT NULL,


    titulo VARCHAR(255) NOT NULL,


    descripcion TEXT NOT NULL,


    tiempo_problema VARCHAR(50) NULL,


    afectacion VARCHAR(50) NULL,


    equipo VARCHAR(255) NULL,


    ubicacion VARCHAR(255) NULL,


    estado VARCHAR(30) NOT NULL DEFAULT 'Abierta'
        CHECK (estado IN (
            'Abierta',
            'En_proceso',
            'Resuelta',
            'Cerrada'
        )),


    prioridad VARCHAR(20) NOT NULL DEFAULT 'Media'
        CHECK (prioridad IN (
            'Baja',
            'Media',
            'Alta',
            'Critica'
        )),


    correo_enviado BOOLEAN DEFAULT FALSE,


    fecha_envio_correo TIMESTAMP NULL,


    created_at TIMESTAMP NULL,


    updated_at TIMESTAMP NULL,


    CONSTRAINT fk_incidencias_usuario
        FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id)

);

CREATE TABLE incidencia_archivos (

    id BIGSERIAL PRIMARY KEY,


    incidencia_id BIGINT NOT NULL,


    usuario_id BIGINT NOT NULL,


    nombre_original VARCHAR(255) NOT NULL,


    nombre_archivo VARCHAR(255) NOT NULL,


    ruta VARCHAR(255) NOT NULL,


    extension VARCHAR(50) NULL,


    tamano BIGINT NULL,


    texto_ocr TEXT NULL,


    created_at TIMESTAMP NULL,


    updated_at TIMESTAMP NULL,


    CONSTRAINT fk_incidencia_archivos_incidencia
        FOREIGN KEY (incidencia_id)
        REFERENCES incidencias(id)
        ON DELETE CASCADE,


    CONSTRAINT fk_incidencia_archivos_usuario
        FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id)

);

CREATE TABLE solicitudes (

    id BIGSERIAL PRIMARY KEY,


    usuario_id BIGINT NOT NULL,


    folio VARCHAR(20) UNIQUE,


    categoria VARCHAR(50) NOT NULL,


    asunto VARCHAR(255) NOT NULL,


    descripcion TEXT NOT NULL,


    datos_extra JSONB NULL,


    estado VARCHAR(20) NOT NULL DEFAULT 'pendiente',


    correo_enviado BOOLEAN NOT NULL DEFAULT FALSE,


    correo_enviado_at TIMESTAMP NULL,


    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,


    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,



    CONSTRAINT solicitudes_usuario_fk
        FOREIGN KEY (usuario_id)
        REFERENCES usuarios(id)
        ON DELETE CASCADE

);



CREATE INDEX solicitudes_usuario_id_index
ON solicitudes(usuario_id);



CREATE INDEX solicitudes_estado_index
ON solicitudes(estado);



CREATE INDEX solicitudes_created_at_index
ON solicitudes(created_at);


-- ============================================================
-- CHATBOT CONVERSATIONS
-- ASISTENTE VIRTUAL TI
-- ============================================================


CREATE TABLE chatbot_conversations (

    id BIGSERIAL PRIMARY KEY,


    usuario_id BIGINT NOT NULL,


    -- Mensaje enviado por el usuario
    mensaje TEXT NOT NULL,


    -- Intención detectada
    -- incidencia
    -- solicitud
    -- pase_menor
    -- pase_mayor
    -- consulta_estado
    -- desconocido
    intencion_detectada VARCHAR(100) NOT NULL,


    -- Nivel de confianza del reconocimiento
    puntuacion SMALLINT DEFAULT 0
        CHECK(
            puntuacion BETWEEN 0 AND 100
        ),


    -- Respuesta generada por el asistente
    respuesta TEXT NOT NULL,


    -- Feedback futuro del usuario
    -- true = ayudó
    -- false = no ayudó
    es_util BOOLEAN NULL,


    -- Tipo de acción sugerida
    -- incidencia_create
    -- solicitud_create
    -- pase_menor_create
    -- memorando_create
    -- consulta_gestiones
    accion VARCHAR(100) NULL,


    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,


    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,


    CONSTRAINT fk_chatbot_usuario

        FOREIGN KEY(usuario_id)

        REFERENCES usuarios(id)

        ON DELETE CASCADE

);



CREATE INDEX idx_chatbot_usuario_fecha

ON chatbot_conversations(usuario_id, created_at);



CREATE INDEX idx_chatbot_intencion

ON chatbot_conversations(intencion_detectada);

CREATE TABLE email_deliveries (

    id BIGSERIAL PRIMARY KEY,


    /*
    |--------------------------------------------------------------------------
    | Relación polimórfica
    |--------------------------------------------------------------------------
    |
    | Ejemplos de emailable_type:
    |
    | App\Models\Incidencia
    | App\Models\Solicitud
    | App\Models\Memorando
    |
    */

    emailable_type VARCHAR(255) NOT NULL,

    emailable_id BIGINT NOT NULL,


    /*
    |--------------------------------------------------------------------------
    | Destinatario
    |--------------------------------------------------------------------------
    */

    recipient_email VARCHAR(320) NOT NULL,

    recipient_name VARCHAR(255) NULL,


    /*
    |--------------------------------------------------------------------------
    | Información del correo
    |--------------------------------------------------------------------------
    */

    mail_type VARCHAR(100) NOT NULL,

    subject VARCHAR(255) NULL,

    mailable_class VARCHAR(255) NULL,


    /*
    |--------------------------------------------------------------------------
    | Estado del envío
    |--------------------------------------------------------------------------
    |
    | Estados permitidos:
    |
    | pending
    | sending
    | sent
    | failed
    |
    */

    status VARCHAR(20) NOT NULL DEFAULT 'pendiente',

    attempts SMALLINT NOT NULL DEFAULT 0,


    /*
    |--------------------------------------------------------------------------
    | Información del último error
    |--------------------------------------------------------------------------
    */

    last_error TEXT NULL,

    error_code VARCHAR(100) NULL,


    /*
    |--------------------------------------------------------------------------
    | Identificador devuelto por el servidor SMTP
    |--------------------------------------------------------------------------
    */

    provider_message_id VARCHAR(255) NULL,


    /*
    |--------------------------------------------------------------------------
    | Fechas del proceso
    |--------------------------------------------------------------------------
    */

    queued_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

    last_attempt_at TIMESTAMP NULL,

    sent_at TIMESTAMP NULL,

    failed_at TIMESTAMP NULL,

    next_retry_at TIMESTAMP NULL,


    /*
    |--------------------------------------------------------------------------
    | Información adicional
    |--------------------------------------------------------------------------
    */

    metadata JSONB NULL DEFAULT '{}'::jsonb,


    /*
    |--------------------------------------------------------------------------
    | Timestamps de Laravel
    |--------------------------------------------------------------------------
    */

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,


    /*
    |--------------------------------------------------------------------------
    | Restricciones
    |--------------------------------------------------------------------------
    */

    CONSTRAINT email_deliveries_status_check
        CHECK (
            status IN (
                'pendiente',
                'enviando',
                'enviado',
                'fallido'
            )
        ),


    CONSTRAINT email_deliveries_attempts_check
        CHECK (
            attempts >= 0
        )

);

/*
|--------------------------------------------------------------------------
| Buscar correos asociados a una gestión
|--------------------------------------------------------------------------
*/

CREATE INDEX email_deliveries_emailable_index
ON email_deliveries (
    emailable_type,
    emailable_id
);


/*
|--------------------------------------------------------------------------
| Buscar por estado
|--------------------------------------------------------------------------
*/

CREATE INDEX email_deliveries_status_index
ON email_deliveries (
    status
);


/*
|--------------------------------------------------------------------------
| Buscar correos recientes
|--------------------------------------------------------------------------
*/

CREATE INDEX email_deliveries_created_at_index
ON email_deliveries (
    created_at DESC
);


/*
|--------------------------------------------------------------------------
| Buscar pendientes para procesarlos
|--------------------------------------------------------------------------
*/

CREATE INDEX email_deliveries_pending_index
ON email_deliveries (
    next_retry_at,
    created_at
)
WHERE status = 'pendiente';


/*
|--------------------------------------------------------------------------
| Buscar fallos recientes
|--------------------------------------------------------------------------
*/

CREATE INDEX email_deliveries_failed_index
ON email_deliveries (
    failed_at DESC
)
WHERE status = 'fallido';


/*
|--------------------------------------------------------------------------
| Buscar por destinatario
|--------------------------------------------------------------------------
*/

CREATE INDEX email_deliveries_recipient_index
ON email_deliveries (
    recipient_email
);

-- ============================================================
-- TIPOS DE MEMORANDO
-- ============================================================

CREATE TABLE memorando_tipos (

    id BIGSERIAL PRIMARY KEY,

    -- Nombre visible del tipo de memorando
    nombre VARCHAR(100) NOT NULL UNIQUE,

    -- Identificador interno usado en código
    slug VARCHAR(100) NOT NULL UNIQUE,


    -- Si genera folio DIT-###-mes-año
    requiere_folio BOOLEAN NOT NULL DEFAULT FALSE,


    -- Quién puede crear este tipo
    creado_por_rol VARCHAR(50) NOT NULL DEFAULT 'Ambos'
        CHECK(creado_por_rol IN ('Usuario','UsuarioTI','Ambos')),


    -- Si necesita aprobación registrada
    requiere_aprobacion BOOLEAN NOT NULL DEFAULT FALSE,


    -- Formulario dinámico que cargará el sistema
    -- Ej:
    -- autorizacion
    -- pase_temporal
    -- solicitud_compra
    formulario VARCHAR(100) NOT NULL,


    -- Plantilla PDF utilizada
    -- Ej:
    -- memorando_autorizacion
    -- memorando_orden_compra
    plantilla VARCHAR(100) NOT NULL,


    -- Estado del catálogo
    activo BOOLEAN NOT NULL DEFAULT TRUE,


    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

);


DROP TABLE IF EXISTS memorando_destinatarios CASCADE;


CREATE TABLE memorando_destinatarios (

    id BIGSERIAL PRIMARY KEY,

    tipo_id BIGINT NOT NULL,

    para_nombre VARCHAR(200) NOT NULL,

    cc_nombre VARCHAR(300),

    activo BOOLEAN DEFAULT TRUE,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

);


CREATE TABLE solicitud_compras (

    id BIGSERIAL PRIMARY KEY,

    memorando_id BIGINT NOT NULL,

    empresa VARCHAR(200) NOT NULL,

    tipo_compra VARCHAR(50) NOT NULL,

    motivo_compra VARCHAR(100) NOT NULL,

    proveedor VARCHAR(200),

    razon_proveedor TEXT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,


    FOREIGN KEY(memorando_id)
    REFERENCES memorandos(id)
    ON DELETE CASCADE

);

ALTER TABLE solicitud_compras
ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- ============================================================
-- CONTADOR FOLIOS DIT
-- ============================================================


CREATE TABLE folio_counters (

    id BIGSERIAL PRIMARY KEY,


    prefijo VARCHAR(20)
        NOT NULL UNIQUE,


    ultimo_valor INTEGER
        DEFAULT 0,


    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,


    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

);





-- ============================================================
-- MEMORANDOS
-- ============================================================


CREATE TABLE memorandos (

    id BIGSERIAL PRIMARY KEY,


    codigo VARCHAR(50)
        UNIQUE,


    tipo_id BIGINT
        NOT NULL,


    solicitante_id BIGINT
        NOT NULL,


    estado VARCHAR(50)
        DEFAULT 'GENERADO'
        CHECK(
            estado IN
            (
                'GENERADO',
                'EN_FIRMA',
                'APROBADO',
                'RECHAZADO',
                'ARCHIVADO'
            )
        ),



    para_nombre VARCHAR(200),


    cc_nombre VARCHAR(200),


    de_nombre VARCHAR(200)
        NOT NULL,


    asunto VARCHAR(255)
        NOT NULL,


    observaciones TEXT,


    fecha_documento DATE
        NOT NULL,


    archivo_pdf VARCHAR(300),



    datos_extra JSONB
        DEFAULT '{}',



    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,


    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

);




CREATE INDEX idx_memorandos_estado
ON memorandos(estado);


CREATE INDEX idx_memorandos_tipo
ON memorandos(tipo_id);


CREATE INDEX idx_memorandos_usuario
ON memorandos(solicitante_id);





-- ============================================================
-- ARTICULOS SOLICITUD DE COMPRA
-- ============================================================


CREATE TABLE memorando_articulos (

    id BIGSERIAL PRIMARY KEY,


    memorando_id BIGINT
        NOT NULL,


    codigo VARCHAR(100),


    descripcion TEXT
        NOT NULL,


    unidad VARCHAR(50),


    cantidad INTEGER
        DEFAULT 1,


    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

);





-- ============================================================
-- ARCHIVOS RELACIONADOS
-- ============================================================


CREATE TABLE memorando_archivos (

    id BIGSERIAL PRIMARY KEY,


    memorando_id BIGINT
        NOT NULL,


    tipo_archivo VARCHAR(100)
        NOT NULL,


    nombre_archivo VARCHAR(255)
        NOT NULL,


    ruta_archivo VARCHAR(500),


    url_sharepoint VARCHAR(500),


    cargado_por BIGINT
        NOT NULL,


    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

);





-- ============================================================
-- APROBACIONES FISICAS
-- ============================================================


CREATE TABLE aprobaciones (

    id BIGSERIAL PRIMARY KEY,


    memorando_id BIGINT
        NOT NULL,


    nombre_aprobador VARCHAR(200)
        NOT NULL,


    cargo_aprobador VARCHAR(200),


    estado VARCHAR(50)
        NOT NULL,


    comentario TEXT,


    fecha_aprobacion TIMESTAMP,


    registrado_por BIGINT
        NOT NULL,


    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

);





-- ============================================================
-- HISTORIAL
-- ============================================================


CREATE TABLE memorando_historial (

    id BIGSERIAL PRIMARY KEY,


    memorando_id BIGINT
        NOT NULL,


    usuario_id BIGINT
        NOT NULL,


    estado_anterior VARCHAR(50),


    estado_nuevo VARCHAR(50),


    comentario TEXT,


    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

);







-- ============================================================
-- FOREIGN KEYS
-- ============================================================



ALTER TABLE usuarios

ADD CONSTRAINT fk_usuario_rol

FOREIGN KEY(rol_id)

REFERENCES roles(id);





ALTER TABLE memorandos

ADD CONSTRAINT fk_memorando_tipo

FOREIGN KEY(tipo_id)

REFERENCES memorando_tipos(id);





ALTER TABLE memorandos

ADD CONSTRAINT fk_memorando_usuario

FOREIGN KEY(solicitante_id)

REFERENCES usuarios(id);





ALTER TABLE memorando_articulos

ADD CONSTRAINT fk_articulos_memorando

FOREIGN KEY(memorando_id)

REFERENCES memorandos(id)

ON DELETE CASCADE;





ALTER TABLE memorando_archivos

ADD CONSTRAINT fk_archivos_memorando

FOREIGN KEY(memorando_id)

REFERENCES memorandos(id)

ON DELETE CASCADE;





ALTER TABLE memorando_archivos

ADD CONSTRAINT fk_archivos_usuario

FOREIGN KEY(cargado_por)

REFERENCES usuarios(id);





ALTER TABLE aprobaciones

ADD CONSTRAINT fk_aprobacion_memorando

FOREIGN KEY(memorando_id)

REFERENCES memorandos(id)

ON DELETE CASCADE;





ALTER TABLE aprobaciones

ADD CONSTRAINT fk_aprobacion_usuario

FOREIGN KEY(registrado_por)

REFERENCES usuarios(id);





ALTER TABLE memorando_historial

ADD CONSTRAINT fk_historial_memorando

FOREIGN KEY(memorando_id)

REFERENCES memorandos(id)

ON DELETE CASCADE;





ALTER TABLE memorando_historial

ADD CONSTRAINT fk_historial_usuario

FOREIGN KEY(usuario_id)

REFERENCES usuarios(id);






-- ============================================================
-- ROLES INICIALES
-- ============================================================


INSERT INTO roles
(nombre, descripcion)

VALUES

(
'Usuario',
'Usuario final del portal TI'
),


(
'UsuarioTI',
'Personal interno encargado de gestionar solicitudes TI'
),


(
'Administrador',
'Administrador global del sistema'
);




-- ============================================================
-- FOLIO DIT
-- ============================================================


INSERT INTO folio_counters
(
prefijo,
ultimo_valor
)

VALUES

(
'DIT',
180
);


-- ============================================================
-- Memorandos
-- ============================================================


INSERT INTO memorando_tipos
(
    nombre,
    slug,
    requiere_folio,
    creado_por_rol,
    requiere_aprobacion,
    formulario,
    plantilla
)
VALUES

(
    'Memorando de autorización',
    'autorizacion',
    FALSE,
    'usuario',
    TRUE,
    'autorizacion',
    'memorando_autorizacion'
),


(
    'Pase temporal',
    'pase_temporal',
    FALSE,
    'usuario',
    TRUE,
    'pase_temporal',
    'memorando_pase'
),


(
    'Solicitud de orden de compra',
    'orden_compra',
    TRUE,
    'usuarioTI',
    TRUE,
    'solicitud_compra',
    'memorando_orden_compra'
),


(
    'Orden de pago',
    'orden_pago',
    TRUE,
    'usuarioTI',
    TRUE,
    'orden_pago',
    'memorando_orden_pago'
),


(
    'Contratación de servicios',
    'contratacion_servicios',
    TRUE,
    'usuarioTI',
    TRUE,
    'contratacion_servicios',
    'memorando_contratacion'
),


(
    'Renovación de servicios',
    'renovacion_servicios',
    TRUE,
    'usuarioTI',
    TRUE,
    'renovacion_servicios',
    'memorando_renovacion'
);


INSERT INTO memorando_destinatarios
(
    tipo_id,
    para_nombre,
    cc_nombre
)
VALUES

(
    (
        SELECT id 
        FROM memorando_tipos 
        WHERE slug = 'autorizacion'
    ),

    'Lic. Byron Castro - Director de Seguridad',

    'Ing. Wesly López - Director Senior de Información y Tecnología'

),


(
    (
        SELECT id 
        FROM memorando_tipos 
        WHERE slug = 'pase_temporal'
    ),

    'Lic. Byron Castro - Director de Seguridad',

    'Lic. Fernando Figueroa - Coordinador de Infraestructura IT'

),


(
    (
        SELECT id 
        FROM memorando_tipos 
        WHERE slug = 'orden_compra'
    ),

    'Ing. Osman Madrid - Director Sr Operaciones',

    'Lic. Juan Carlos Dique - Director Finanzas y Administración'

);

ALTER TABLE memorando_destinatarios

ADD CONSTRAINT fk_destinatario_tipo

FOREIGN KEY(tipo_id)

REFERENCES memorando_tipos(id)

ON DELETE CASCADE;


ALTER TABLE memorando_tipos
DROP CONSTRAINT memorando_tipos_creado_por_rol_check;


ALTER TABLE memorando_tipos
ADD CONSTRAINT memorando_tipos_creado_por_rol_check
CHECK(
    creado_por_rol IN
    (
        'Usuario',
        'UsuarioTI',
        'Administrador',
        'Todos'
    )
);
