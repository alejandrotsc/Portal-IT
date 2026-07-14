<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Memorando extends Model
{
    protected $table = 'memorandos';


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
        'datos_extra'
    ];


    protected $casts = [
        'datos_extra' => 'array',
        'fecha_documento' => 'date'
    ];


    /*
    |--------------------------------------------------------------------------
    | Estados del memorando
    |--------------------------------------------------------------------------
    */

    const ESTADO_GENERADO = 'GENERADO';

    const ESTADO_EN_FIRMA = 'EN_FIRMA';

    const ESTADO_APROBADO = 'APROBADO';

    const ESTADO_RECHAZADO = 'RECHAZADO';

    const ESTADO_ARCHIVADO = 'ARCHIVADO';

    const ESTADO_ENVIADO_EMAIL = 'ENVIADO_EMAIL';



    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */


    /**
     * Tipo de memorando
     */
    public function tipo()
    {
        return $this->belongsTo(
            MemorandoTipo::class,
            'tipo_id'
        );
    }



    /**
     * Usuario que creó el memorando
     */
    public function solicitante()
    {
        return $this->belongsTo(
            Usuario::class,
            'solicitante_id'
        );
    }



    /**
     * Información específica de solicitud de compra
     */
    public function solicitudCompra()
    {
        return $this->hasOne(
            SolicitudCompra::class,
            'memorando_id'
        );
    }



    /**
     * Artículos asociados
     */
    public function articulos()
    {
        return $this->hasMany(
            MemorandoArticulo::class,
            'memorando_id'
        );
    }



    /**
     * Archivos adjuntos
     */
    public function archivos()
    {
        return $this->hasMany(
            MemorandoArchivo::class,
            'memorando_id'
        );
    }



    /**
     * Aprobaciones
     */
    public function aprobaciones()
    {
        return $this->hasMany(
            Aprobacion::class,
            'memorando_id'
        );
    }



    /**
     * Historial de cambios
     */
    public function historial()
    {
        return $this->hasMany(
            MemorandoHistorial::class,
            'memorando_id'
        );
    }



    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */


    public function requiereFolio()
    {
        return $this->tipo?->requiere_folio === true;
    }


    public function estaAprobado()
    {
        return $this->estado === self::ESTADO_APROBADO;
    }


    public function tienePdf()
    {
        return !empty($this->archivo_pdf);
    }
}