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
    | Configuración del modelo
    |--------------------------------------------------------------------------
    */

    protected $table =
        'memorandos';


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


    protected $casts = [
        'datos_extra' =>
            'array',

        'fecha_documento' =>
            'date',
    ];


    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function tipo(): BelongsTo
    {
        return $this->belongsTo(
            MemorandoTipo::class,
            'tipo_id'
        );
    }


    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(
            Usuario::class,
            'solicitante_id'
        );
    }


    public function solicitudCompra(): HasOne
    {
        return $this->hasOne(
            SolicitudCompra::class,
            'memorando_id'
        );
    }


    public function articulos(): HasMany
    {
        return $this->hasMany(
            MemorandoArticulo::class,
            'memorando_id'
        );
    }


    public function archivos(): HasMany
    {
        return $this->hasMany(
            MemorandoArchivo::class,
            'memorando_id'
        );
    }


    public function aprobaciones(): HasMany
    {
        return $this->hasMany(
            Aprobacion::class,
            'memorando_id'
        );
    }


    public function historial(): HasMany
    {
        return $this->hasMany(
            MemorandoHistorial::class,
            'memorando_id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Helpers de estado
    |--------------------------------------------------------------------------
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


    public function estaPendienteDeRevision(): bool
    {
        return $this->estaGenerado();
    }


    /*
    |--------------------------------------------------------------------------
    | Helpers generales
    |--------------------------------------------------------------------------
    */

    public function requiereFolio(): bool
    {
        return $this->tipo?->requiere_folio
            === true;
    }


    public function tienePdf(): bool
    {
        return filled(
            $this->archivo_pdf
        );
    }
}