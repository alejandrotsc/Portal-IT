<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class LimpiarDatosTemporales extends Command
{
    protected $signature = 'portal:limpiar
                            {--dry-run : Solo mostrar cuántos registros se eliminarían}
                            {--force : Ejecutar sin solicitar confirmación}';

    protected $description =
        'Elimina registros temporales antiguos del Portal IT.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $force = (bool) $this->option('force');

        $ahora = now();

        /*
        |--------------------------------------------------------------------------
        | Límites de retención
        |--------------------------------------------------------------------------
        */

        $limites = [
            'sessions' => $ahora->copy()->subDays(7)->timestamp,
            'tokens' => $ahora->copy()->subDays(30),
            'chatbot' => $ahora->copy()->subDays(180),
            'emails_enviados' => $ahora->copy()->subDays(180),
            'emails_fallidos' => $ahora->copy()->subDays(365),
            'notifications' => $ahora->copy()->subDays(90),
        ];

        /*
        |--------------------------------------------------------------------------
        | Consultas que se eliminarán
        |--------------------------------------------------------------------------
        */

        $consultas = [
            'Caché vencida' => DB::table('cache')
                ->where('expiration', '<', $ahora->timestamp),

            'Bloqueos de caché vencidos' => DB::table('cache_locks')
                ->where('expiration', '<', $ahora->timestamp),

            'Sesiones inactivas por más de 7 días' => DB::table('sessions')
                ->where('last_activity', '<', $limites['sessions']),

            'Tokens usados o vencidos hace más de 30 días' =>
                DB::table('tokens_autenticacion')
                    ->where(function ($query) use ($limites) {
                        $query
                            ->where(
                                'expires_at',
                                '<',
                                $limites['tokens']
                            )
                            ->orWhere(
                                'used_at',
                                '<',
                                $limites['tokens']
                            );
                    }),

            'Conversaciones del chatbot mayores a 180 días' =>
                DB::table('chatbot_conversations')
                    ->where(
                        'created_at',
                        '<',
                        $limites['chatbot']
                    ),

            'Entregas de correo enviadas mayores a 180 días' =>
                DB::table('email_deliveries')
                    ->where('status', 'enviado')
                    ->where(
                        'created_at',
                        '<',
                        $limites['emails_enviados']
                    ),

            'Entregas de correo fallidas mayores a 365 días' =>
                DB::table('email_deliveries')
                    ->where('status', 'fallido')
                    ->where(
                        'created_at',
                        '<',
                        $limites['emails_fallidos']
                    ),

            'Notificaciones leídas hace más de 90 días' =>
                DB::table('notifications')
                    ->whereNotNull('read_at')
                    ->where(
                        'read_at',
                        '<',
                        $limites['notifications']
                    ),
        ];

        /*
        |--------------------------------------------------------------------------
        | Mostrar resumen
        |--------------------------------------------------------------------------
        */

        $resumen = [];
        $total = 0;

        foreach ($consultas as $nombre => $consulta) {
            $cantidad = (clone $consulta)->count();

            $resumen[] = [
                'tipo' => $nombre,
                'registros' => $cantidad,
            ];

            $total += $cantidad;
        }

        $this->newLine();

        $this->table(
            ['Tipo de registro', 'Cantidad'],
            $resumen
        );

        $this->info("Total encontrado: {$total}");

        if ($total === 0) {
            $this->info('No hay registros temporales por limpiar.');

            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->warn(
                'Modo simulación: no se eliminó ningún registro.'
            );

            return self::SUCCESS;
        }

        if (
            ! $force
            && ! $this->confirm(
                "¿Deseas eliminar estos {$total} registros?",
                false
            )
        ) {
            $this->warn('Limpieza cancelada.');

            return self::SUCCESS;
        }

        /*
        |--------------------------------------------------------------------------
        | Ejecutar limpieza
        |--------------------------------------------------------------------------
        */

        try {
            $eliminados = DB::transaction(
                function () use ($consultas): array {
                    $resultado = [];

                    foreach ($consultas as $nombre => $consulta) {
                        $resultado[$nombre] =
                            (clone $consulta)->delete();
                    }

                    return $resultado;
                }
            );

            /*
            |--------------------------------------------------------------------------
            | Desactivar avisos vencidos
            |--------------------------------------------------------------------------
            |
            | Los avisos no se eliminan. Solo dejan de mostrarse cuando finaliza
            | su vigencia.
            |
            */

            $avisosDesactivados = DB::table('avisos')
                ->where('activo', true)
                ->whereNotNull('fecha_fin')
                ->where('fecha_fin', '<', $ahora)
                ->update([
                    'activo' => false,
                    'updated_at' => $ahora,
                ]);

            $totalEliminado = array_sum($eliminados);

            $this->newLine();
            $this->info(
                "Registros eliminados: {$totalEliminado}"
            );

            $this->info(
                "Avisos vencidos desactivados: {$avisosDesactivados}"
            );

            Log::info(
                'Limpieza automática del Portal IT completada.',
                [
                    'registros_eliminados' => $eliminados,
                    'total_eliminado' => $totalEliminado,
                    'avisos_desactivados' =>
                        $avisosDesactivados,
                    'ejecutado_en' => $ahora->toDateTimeString(),
                ]
            );

            return self::SUCCESS;
        } catch (Throwable $exception) {
            Log::error(
                'Falló la limpieza de datos temporales.',
                [
                    'error' => $exception->getMessage(),
                ]
            );

            $this->error(
                'No se pudo completar la limpieza: '
                . $exception->getMessage()
            );

            return self::FAILURE;
        }
    }
}