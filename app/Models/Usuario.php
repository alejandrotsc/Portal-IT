<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    /*
    |--------------------------------------------------------------------------
    | Tabla asociada
    |--------------------------------------------------------------------------
    |
    | Define la tabla utilizada para almacenar los usuarios registrados
    | dentro del Portal TI.
    |
    */

    protected $table = 'usuarios';

    /*
    |--------------------------------------------------------------------------
    | Campos asignables
    |--------------------------------------------------------------------------
    |
    | Define los atributos que pueden ser asignados de forma masiva
    | durante la creación o actualización de un usuario.
    |
    */

    protected $fillable = [
        'nombre',
        'correo',
        'correo_verificado_at',
        'rol_id',
        'activo',
        'extension_telefonica',
    ];

    /*
    |--------------------------------------------------------------------------
    | Campos ocultos
    |--------------------------------------------------------------------------
    |
    | Evita que información interna de autenticación sea incluida
    | accidentalmente al serializar el modelo.
    |
    */

    protected $hidden = [
        'remember_token',
    ];

    /*
    |--------------------------------------------------------------------------
    | Conversión de tipos
    |--------------------------------------------------------------------------
    |
    | Convierte automáticamente la fecha de verificación del correo a
    | un objeto de fecha y el estado activo a un valor booleano.
    |
    */

    protected function casts(): array
    {
        return [
            'correo_verificado_at' =>
                'datetime',

            'activo' =>
                'boolean',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Correo
    |--------------------------------------------------------------------------
    |
    | Proporciona métodos auxiliares para consultar y actualizar el
    | estado de verificación del correo electrónico del usuario.
    |
    */

    public function correoEstaVerificado(): bool
    {
        return $this->correo_verificado_at
            !== null;
    }

    public function marcarCorreoComoVerificado(): bool
    {
        if ($this->correoEstaVerificado()) {
            return true;
        }

        return $this->forceFill([
            'correo_verificado_at' =>
                now(),
        ])->save();
    }

    /*
    |--------------------------------------------------------------------------
    | Dirección para notificaciones por correo
    |--------------------------------------------------------------------------
    |
    | Define la dirección de correo electrónico que Laravel utilizará
    | al enviar notificaciones mediante el canal de correo.
    |
    */

    public function routeNotificationForMail(): string
    {
        return $this->correo;
    }

    /*
    |--------------------------------------------------------------------------
    | Canal privado de notificaciones
    |--------------------------------------------------------------------------
    |
    | Laravel Reverb enviará las notificaciones del usuario a este canal.
    | Cada usuario tendrá un canal independiente.
    |
    */

    public function receivesBroadcastNotificationsOn(): string
    {
        return 'usuarios.'.$this->id;
    }

    /*
    |--------------------------------------------------------------------------
    | Comprobación de roles
    |--------------------------------------------------------------------------
    |
    | Proporciona métodos auxiliares para determinar el rol del usuario
    | y comprobar si pertenece al personal encargado del soporte TI.
    |
    */

    public function esAdministrador(): bool
    {
        return $this->rol?->nombre
            === 'Administrador';
    }

    public function esUsuarioTI(): bool
    {
        return $this->rol?->nombre
            === 'UsuarioTI';
    }

    public function perteneceASoporte(): bool
    {
        return in_array(
            $this->rol?->nombre,
            [
                'UsuarioTI',
                'Administrador',
            ],
            true
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Rol asignado
    |--------------------------------------------------------------------------
    |
    | Define la relación con el rol que determina los permisos y nivel
    | de acceso correspondiente al usuario dentro del Portal TI.
    |
    */

    public function rol(): BelongsTo
    {
        return $this->belongsTo(
            Rol::class,
            'rol_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Tokens de autenticación
    |--------------------------------------------------------------------------
    |
    | Define la relación con los tokens temporales generados para los
    | procesos de autenticación asociados al usuario.
    |
    */

    public function tokensAutenticacion(): HasMany
    {
        return $this->hasMany(
            TokenAutenticacion::class,
            'usuario_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Avisos creados
    |--------------------------------------------------------------------------
    |
    | Define la relación con los avisos que fueron registrados por el
    | usuario dentro del Portal TI.
    |
    */

    public function avisosCreados(): HasMany
    {
        return $this->hasMany(
            Aviso::class,
            'creado_por'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Memorandos solicitados
    |--------------------------------------------------------------------------
    |
    | Define la relación con los memorandos en los que el usuario figura
    | como solicitante de la gestión.
    |
    */

    public function memorandos(): HasMany
    {
        return $this->hasMany(
            Memorando::class,
            'solicitante_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Historial de memorandos
    |--------------------------------------------------------------------------
    |
    | Define la relación con los registros históricos de memorandos en
    | los que el usuario aparece como responsable de algún cambio.
    |
    */

    public function historial(): HasMany
    {
        return $this->hasMany(
            MemorandoHistorial::class,
            'usuario_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Guardias asignadas
    |--------------------------------------------------------------------------
    |
    | Define las guardias de soporte que han sido asignadas al usuario
    | para atender los turnos correspondientes.
    |
    */

    public function guardiasAsignadas(): HasMany
    {
        return $this->hasMany(
            GuardiaSoporte::class,
            'usuario_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Guardias creadas
    |--------------------------------------------------------------------------
    |
    | Define las asignaciones de guardia que fueron registradas por el
    | usuario cuando actúa como administrador.
    |
    */

    public function guardiasCreadas(): HasMany
    {
        return $this->hasMany(
            GuardiaSoporte::class,
            'creado_por'
        );
    }
}