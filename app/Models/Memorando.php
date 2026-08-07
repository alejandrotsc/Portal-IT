<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Memorando extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Estados
    |--------------------------------------------------------------------------
    |
    | Define los estados disponibles para representar el ciclo de vida
    | de un memorando dentro del Portal TI.
    |
    */

    public const ESTADO_GENERADO =
        'GENERADO';

    public const ESTADO_EN_FIRMA =
        'EN_FIRMA';

    public const ESTADO_APROBADO =
        'APROBADO';

    public const ESTADO_RECHAZADO =
        'RECHAZADO';

    public const ESTADO_ARCHIVADO =
        'ARCHIVADO';

    /*
    |--------------------------------------------------------------------------
    | Estados utilizados por administración
    |--------------------------------------------------------------------------
    |
    | EN_FIRMA y ARCHIVADO permanecen definidos para conservar compatibilidad
    | con registros o funcionalidades existentes, pero no aparecerán en el
    | nuevo módulo administrativo.
    |
    */

    public const ESTADOS_ADMINISTRATIVOS = [
        self::ESTADO_GENERADO,
        self::ESTADO_APROBADO,
        self::ESTADO_RECHAZADO,
    ];

    /*
    |--------------------------------------------------------------------------
    | Tabla asociada
    |--------------------------------------------------------------------------
    |
    | Define la tabla utilizada para almacenar los memorandos generados
    | dentro del Portal TI.
    |
    */

    protected $table =
        'memorandos';

    /*
    |--------------------------------------------------------------------------
    | Campos asignables
    |--------------------------------------------------------------------------
    |
    | Define los atributos que pueden ser asignados de forma masiva
    | durante la creación o actualización de un memorando.
    |
    */

    protected $fillable = [
        'codigo',
        'tipo_id',
        'solicitante_id',
        'estado',
        'para_nombre',
        'cc_nombre',
        'de_nombre',
        'asunto',
        'observaciones',
        'fecha_documento',
        'archivo_pdf',
        'datos_extra',
    ];

    /*
    |--------------------------------------------------------------------------
    | Conversión de tipos
    |--------------------------------------------------------------------------
    |
    | Convierte automáticamente los datos adicionales a un arreglo y
    | la fecha del documento a un objeto de fecha.
    |
    */

    protected $casts = [
        'datos_extra' =>
            'array',

        'fecha_documento' =>
            'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | Tipo de memorando
    |--------------------------------------------------------------------------
    |
    | Define la relación con el tipo correspondiente al memorando,
    | permitiendo identificar su clasificación y configuración.
    |
    */

    public function tipo(): BelongsTo
    {
        return $this->belongsTo(
            MemorandoTipo::class,
            'tipo_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Solicitante
    |--------------------------------------------------------------------------
    |
    | Define la relación con el usuario que generó o solicitó
    | originalmente el memorando.
    |
    */

    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(
            Usuario::class,
            'solicitante_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Solicitud de compra
    |--------------------------------------------------------------------------
    |
    | Define la relación con la solicitud de compra asociada al memorando
    | cuando este corresponde a una gestión de adquisición.
    |
    */

    public function solicitudCompra(): HasOne
    {
        return $this->hasOne(
            SolicitudCompra::class,
            'memorando_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Artículos
    |--------------------------------------------------------------------------
    |
    | Define la relación con los artículos registrados como parte
    | del contenido o detalle del memorando.
    |
    */

    public function articulos(): HasMany
    {
        return $this->hasMany(
            MemorandoArticulo::class,
            'memorando_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Archivos adjuntos
    |--------------------------------------------------------------------------
    |
    | Define la relación con los archivos vinculados al memorando,
    | incluyendo documentos de respaldo o evidencias relacionadas.
    |
    */

    public function archivos(): HasMany
    {
        return $this->hasMany(
            MemorandoArchivo::class,
            'memorando_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Aprobaciones
    |--------------------------------------------------------------------------
    |
    | Define la relación con los registros de aprobación asociados
    | al proceso de revisión del memorando.
    |
    */

    public function aprobaciones(): HasMany
    {
        return $this->hasMany(
            Aprobacion::class,
            'memorando_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Historial
    |--------------------------------------------------------------------------
    |
    | Define la relación con los registros históricos que permiten
    | consultar los cambios realizados sobre el memorando.
    |
    */

    public function historial(): HasMany
    {
        return $this->hasMany(
            MemorandoHistorial::class,
            'memorando_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Consultar estado
    |--------------------------------------------------------------------------
    |
    | Proporciona métodos auxiliares para determinar rápidamente el
    | estado actual en el que se encuentra el memorando.
    |
    */

    public function estaGenerado(): bool
    {
        return $this->estado
            === self::ESTADO_GENERADO;
    }

    public function estaEnFirma(): bool
    {
        return $this->estado
            === self::ESTADO_EN_FIRMA;
    }

    public function estaAprobado(): bool
    {
        return $this->estado
            === self::ESTADO_APROBADO;
    }

    public function estaRechazado(): bool
    {
        return $this->estado
            === self::ESTADO_RECHAZADO;
    }

    public function estaArchivado(): bool
    {
        return $this->estado
            === self::ESTADO_ARCHIVADO;
    }

    /*
    |--------------------------------------------------------------------------
    | Pendiente de revisión
    |--------------------------------------------------------------------------
    |
    | Determina si el memorando se encuentra en estado generado y,
    | por lo tanto, pendiente de revisión administrativa.
    |
    */

    public function estaPendienteDeRevision(): bool
    {
        return $this->estaGenerado();
    }

    /*
    |--------------------------------------------------------------------------
    | Verificar requerimiento de folio
    |--------------------------------------------------------------------------
    |
    | Determina si el tipo de memorando asociado requiere que se genere
    | un folio para identificar formalmente el documento.
    |
    */

    public function requiereFolio(): bool
    {
        return $this->tipo?->requiere_folio
            === true;
    }

    /*
    |--------------------------------------------------------------------------
    | Verificar documento PDF
    |--------------------------------------------------------------------------
    |
    | Determina si el memorando posee una ruta de archivo PDF registrada
    | y disponible como parte de la gestión.
    |
    */

    public function tienePdf(): bool
    {
        return filled(
            $this->archivo_pdf
        );
    }
}