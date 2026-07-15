<?php

namespace App\Services;

use Symfony\Component\Process\Process;

class OcrService
{
    public function leerImagen($ruta)
    {

        $process = new Process([
            'tesseract',
            $ruta,
            'stdout',
            '-l',
            'spa',
            '--psm',
            '6'
        ]);


        $process->run();


        if (!$process->isSuccessful()) {

            \Log::error(
                'OCR ERROR: '.$process->getErrorOutput()
            );

            return '';

        }


        return trim(
            $process->getOutput()
        );

    }
}