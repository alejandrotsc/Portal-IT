<?php

namespace App\Services\Chatbot\Knowledge;

class PortalKnowledge
{
    /*
    |--------------------------------------------------------------------------
    | Obtener conocimiento según intención
    |--------------------------------------------------------------------------
    |
    | Selecciona el bloque de conocimiento correspondiente a la intención
    | detectada para proporcionar al chatbot únicamente la información
    | relacionada con la gestión solicitada.
    |
    */

    public function forIntent(string $intent): string
    {
        return match ($intent) {
            'incidencia' =>
                $this->incidencia(),

            'solicitud' =>
                $this->solicitud(),

            'pase_menor_24' =>
                $this->paseMenor(),

            'memorando',
            'pase_mayor_24' =>
                $this->paseMayor(),

            'consultar_estado' =>
                $this->estado(),

            default =>
                $this->general()
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Conocimiento general
    |--------------------------------------------------------------------------
    |
    | Proporciona una descripción general de los módulos disponibles
    | cuando no existe una intención específica reconocida.
    |
    */

    public function general(): string
    {
        return <<<TXT
Portal TI

Módulos disponibles:

- Reporte de incidencia
- Solicitud de servicio
- Pase menor a 24 horas
- Pase mayor a 24 horas

Tu objetivo es orientar al usuario hacia el módulo correcto.

No expliques procesos completos.

No inventes funcionalidades.
TXT;
    }

    /*
    |--------------------------------------------------------------------------
    | Reporte de incidencia
    |--------------------------------------------------------------------------
    |
    | Proporciona el conocimiento utilizado para orientar al usuario
    | cuando la intención detectada corresponde a una falla técnica.
    |
    */

    private function incidencia(): string
    {
        return <<<TXT
Reporte de incidencia

Se utiliza cuando algo dejó de funcionar.

Ejemplos:

- Computadora lenta.
- No enciende.
- Outlook presenta errores.
- Sin Internet.
- Error de impresora.
- Pantalla azul.
- Equipo bloqueado.

Respuesta esperada:

Indica que debe seleccionar
"Reporte de incidencia".

No expliques el formulario.
TXT;
    }

    /*
    |--------------------------------------------------------------------------
    | Solicitud de servicio
    |--------------------------------------------------------------------------
    |
    | Proporciona el conocimiento utilizado para orientar al usuario
    | cuando necesita solicitar un recurso, acceso o servicio de TI.
    |
    */

    private function solicitud(): string
    {
        return <<<TXT
Solicitud de servicio

Se utiliza para solicitar recursos o servicios de TI.

Ejemplos:

- Instalación de Office.
- Instalación de software.
- Acceso VPN.
- Nuevo usuario.
- Permisos.
- Equipo nuevo.
- Licencias.

Respuesta esperada:

Indica que debe seleccionar
"Solicitud de servicio".

No describas el formulario.
TXT;
    }

    /*
    |--------------------------------------------------------------------------
    | Pase menor a 24 horas
    |--------------------------------------------------------------------------
    |
    | Proporciona el conocimiento utilizado para orientar al usuario
    | cuando el acceso solicitado tendrá una duración menor a 24 horas.
    |
    */

    private function paseMenor(): string
    {
        return <<<TXT
Pase menor a 24 horas

Se utiliza cuando el acceso solicitado será por menos de 24 horas.

Respuesta esperada:

Indica que debe seleccionar
"Pase menor a 24 horas".

No expliques el proceso.
TXT;
    }

    /*
    |--------------------------------------------------------------------------
    | Pase mayor a 24 horas
    |--------------------------------------------------------------------------
    |
    | Proporciona el conocimiento utilizado para orientar al usuario
    | cuando el acceso solicitado tendrá una duración mayor a 24 horas.
    |
    */

    private function paseMayor(): string
    {
        return <<<TXT
Pase mayor a 24 horas

Se utiliza cuando el acceso solicitado será por más de 24 horas.

Respuesta esperada:

Indica que debe seleccionar
"Pase mayor a 24 horas".

No expliques el proceso.
TXT;
    }

    /*
    |--------------------------------------------------------------------------
    | Consultar estado
    |--------------------------------------------------------------------------
    |
    | Proporciona el conocimiento utilizado cuando el usuario desea
    | consultar el estado de alguna de sus gestiones registradas.
    |
    */

    private function estado(): string
    {
        return <<<TXT
Consultar estado

El usuario puede consultar el estado de:

- Reportes de incidencia.
- Solicitudes de servicio.
- Pase menor a 24 horas.
- Pase mayor a 24 horas.

Si existen gestiones recientes utiliza esa información.

No inventes estados.
TXT;
    }
}