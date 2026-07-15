<?php

namespace App\Services;

use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Log;

class OcrService
{
    public function leerImagen($ruta)
    {
        $process = new Process([
            'tesseract',
            $ruta,
            'stdout',
            '-l',
            'spa+eng',
            '--psm',
            '11'
        ]);

        $process->run();

        if (!$process->isSuccessful()) {

            Log::error(
                'OCR ERROR: ' . $process->getErrorOutput()
            );

            return '';
        }


        return $this->limpiarTextoOCR(
            $process->getOutput()
        );
    }


    private function limpiarTextoOCR($texto)
    {
        if (!$texto) {
            return '';
        }


        /*
        |--------------------------------------------------------------------------
        | Limpieza básica
        |--------------------------------------------------------------------------
        */

        // Eliminar emojis e iconos Unicode
        $texto = preg_replace(
            '/[\x{1F000}-\x{1FAFF}]/u',
            '',
            $texto
        );


        // Textos generados por la interfaz
        $basura = [
            'Texto identificado automáticamente',
            'Texto identificado automaticamente',
            'error.jpg',
        ];


        foreach ($basura as $item) {

            $texto = str_ireplace(
                $item,
                '',
                $texto
            );
        }



        /*
        |--------------------------------------------------------------------------
        | Correcciones comunes Tesseract
        |--------------------------------------------------------------------------
        */


        // Extensiones de imagen mal reconocidas
        $texto = preg_replace(
            '/\.(ipg|jgp|jpq|jpe|jpg1)\b/i',
            '.jpg',
            $texto
        );


        // Errores típicos
        $texto = preg_replace(
            '/\b9ñ\b\s*/u',
            '',
            $texto
        );


        // Ejecutar corrección de rutas
        $texto = $this->corregirRutasWindows($texto);



        /*
        |--------------------------------------------------------------------------
        | Eliminar caracteres basura
        |--------------------------------------------------------------------------
        */

        $texto = preg_replace(
            '/^\s*[|—;ÍX]+\s*$/m',
            '',
            $texto
        );


        // Mantener letras, números, puntuación y saltos
        $texto = preg_replace(
            '/[^\p{L}\p{N}\p{P}\p{Z}\n]/u',
            '',
            $texto
        );



        /*
        |--------------------------------------------------------------------------
        | Limpieza por líneas
        |--------------------------------------------------------------------------
        */

        $resultado = [];


        foreach (explode("\n", $texto) as $linea) {


            $linea = trim($linea);


            if ($linea === '') {
                continue;
            }



            // Eliminar líneas con solo símbolos
            if (
                strlen($linea) <= 2 &&
                !preg_match(
                    '/[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ]/',
                    $linea
                )
            ) {
                continue;
            }



            // Quitar caracteres iniciales raros
            // Ej:
            // | Microsoft
            // A Configuración
            $linea = preg_replace(
                '/^[|A]\s+(?=[A-Z])/u',
                '',
                $linea
            );



            $resultado[] = $linea;
        }



        /*
        |--------------------------------------------------------------------------
        | Normalización final
        |--------------------------------------------------------------------------
        */

        $textoFinal = implode(
            "\n",
            $resultado
        );


        // Espacios repetidos
        $textoFinal = preg_replace(
            '/[ ]{2,}/',
            ' ',
            $textoFinal
        );


        return trim($textoFinal);
    }




    /**
     * Corrige rutas Windows detectadas por OCR
     *
     * Ejemplos:
     *
     * D:iMediaiIMG.jpg
     * D|Media|IMG.jpg
     * C:UsersAlejandrofoto.jpg
     *
     */
    private function corregirRutasWindows($texto)
    {


        /*
        |--------------------------------------------------------------------------
        | Detectar unidades
        |--------------------------------------------------------------------------
        */

        // D:iMedia -> D:\Media
        $texto = preg_replace(
            '/([A-Z]):[iIl|](?=[A-Za-z0-9])/u',
            '$1:\\',
            $texto
        );



        // D|Media -> D:\Media
        $texto = preg_replace(
            '/([A-Z])\|(?=[A-Za-z])/u',
            '$1:\\',
            $texto
        );



        /*
        |--------------------------------------------------------------------------
        | Separar carpetas pegadas
        |--------------------------------------------------------------------------
        */

        // Ej:
        // C:UsersAlejandro
        // C:\Users\Alejandro

        $texto = preg_replace(
            '/([A-Z]:\\\\)([A-Z][a-z]+)([A-Z][a-z]+)/',
            '$1$2\\\\$3',
            $texto
        );



        /*
        |--------------------------------------------------------------------------
        | Archivos IMG comunes
        |--------------------------------------------------------------------------
        */


        $texto = preg_replace(
            '/([A-Za-z0-9])IMG_/',
            '$1\\\\IMG_',
            $texto
        );



        return $texto;
    }
}