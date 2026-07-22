<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TokenAutenticacion extends Model
{
    protected $table = 'tokens_autenticacion';

    protected $fillable = [
        'usuario_id',
        'correo',
        'token_hash',
        'tipo',
        'expires_at',
        'used_at',
        'attempts',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'attempts' => 'integer',
    ];

    public const TIPO_REGISTRO = 'registro';

    public const TIPO_LOGIN = 'login';

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(
            Usuario::class,
            'usuario_id'
        );
    }

    public function scopeDisponible(Builder $query): Builder
    {
        return $query
            ->whereNull('used_at')
            ->where('expires_at', '>', now());
    }

    public function estaVencido(): bool
    {
        return $this->expires_at->isPast();
    }

    public function fueUtilizado(): bool
    {
        return $this->used_at !== null;
    }

    public function marcarComoUtilizado(): void
    {
        $this->update([
            'used_at' => now(),
        ]);
    }

    public function incrementarIntentos(): void
    {
        $this->increment('attempts');
    }
}