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


    protected $table = 'usuarios';


    protected $fillable = [
        'nombre',
        'correo',
        'correo_verificado_at',
        'rol_id',
        'activo',
    ];


    protected $hidden = [
        'remember_token',
    ];


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
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function rol(): BelongsTo
    {
        return $this->belongsTo(
            Rol::class,
            'rol_id'
        );
    }


    public function tokensAutenticacion(): HasMany
    {
        return $this->hasMany(
            TokenAutenticacion::class,
            'usuario_id'
        );
    }


    public function avisosCreados(): HasMany
    {
        return $this->hasMany(
            Aviso::class,
            'creado_por'
        );
    }


    public function memorandos(): HasMany
    {
        return $this->hasMany(
            Memorando::class,
            'solicitante_id'
        );
    }


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
    | Guardias de sábado o domingo que el UsuarioTI debe atender.
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
    | Asignaciones de guardias realizadas por el administrador.
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