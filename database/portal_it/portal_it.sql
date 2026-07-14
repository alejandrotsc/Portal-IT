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


    username VARCHAR(100)
        NOT NULL UNIQUE,


    correo VARCHAR(200)
        NOT NULL UNIQUE,


    password VARCHAR(255)
        NOT NULL,


    rol_id BIGINT
        NOT NULL,


    activo BOOLEAN
        DEFAULT TRUE,


    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,


    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

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
