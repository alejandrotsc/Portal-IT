<div class="preview-dinamico preview-documento pase-temporal-preview" data-preview="pase_temporal">

    <!-- ENCABEZADO -->
    <div class="encabezado">
        <div class="logo">
            <img src="{{ asset('img/tvc.png') }}" class="logo-tvc" alt="TVC">
        </div>

        <div class="titulo">
            <h1>
                CORPORACIÓN TELEVICENTRO
            </h1>
            <h2>
                Departamento de Información y Tecnología
            </h2>
            <h3>
                Solicitud de Ingreso / Salida de Equipo
            </h3>
        </div>

        <div class="numero">
            N°
            <span data-field="numero"></span>
        </div>
    </div>

    <!-- INFORMACIÓN GENERAL -->
    <div class="bloque">

        <div class="row">
            <span class="label">
                Fecha:
            </span>
            <div class="linea" data-field="fecha"></div>
        </div>

        <div class="row">
            <span class="label">
                Departamento Solicitante:
            </span>
            <div class="linea depto" data-field="departamento"></div>

            <span class="label tecnico-label">
                Técnico IT:
            </span>
            <div class="linea tecnico" data-field="tecnico"></div>
        </div>

        <div class="row">
            <span class="label">
                Nombre del Solicitante:
            </span>
            <div class="linea" data-field="solicitante"></div>
        </div>

        <div class="row">
            <span class="label">
                Responsable del(os) Equipo(s):
            </span>
            <div class="linea" data-field="responsable"></div>

            <span class="check">
                Ingreso
                <span class="checkbox" data-check="Ingreso"></span>
            </span>

            <span class="check">
                Salida
                <span class="checkbox" data-check="Salida"></span>
            </span>
        </div>

        <div class="row">
            <span class="label">
                Empresa / Persona que nos visita:
            </span>
            <div class="linea" data-field="empresa"></div>
        </div>

    </div>

    <!-- TABLA EQUIPOS -->
    <table class="tabla-equipos-preview">
        <thead>
            <tr>
                <th colspan="5" class="titulo-equipo">
                    EQUIPO
                </th>
            </tr>
            <tr>
                <th>
                    Cant.
                </th>
                <th>
                    Marca
                </th>
                <th>
                    Número de Serie
                </th>
                <th>
                    Detalle
                </th>
                <th>
                    Observaciones
                </th>
            </tr>
        </thead>
        <tbody id="previewEquipos">
        </tbody>
    </table>

    <!-- OBSERVACIONES -->
    <div class="observaciones">
        <strong>
            Observaciones:
        </strong>
        <div class="texto-observaciones" data-field="observaciones"></div>
    </div>

    <!-- FOOTER -->
    <div class="footer">

        <div class="firma">
            <div class="line-firma"></div>
            <div class="text-firma">
                V°B° Dirección de Información y Tecnología
                <br>
                (Firma y Sello)
            </div>
        </div>

        <div class="seguridad">
            <h3>
                PARA USO DE SEGURIDAD
            </h3>

            <div class="seg-row">
                Hora de Salida:
                <span class="seg-line" data-field="hora_salida"></span>
                <br><br>
                Guardia de Turno:
                <span class="seg-line" data-field="guardia_salida"></span>
            </div>

            <div class="seg-row">
                Hora de Entrada:
                <span class="seg-line" data-field="hora_entrada"></span>
                <br><br>
                Guardia de Turno:
                <span class="seg-line" data-field="guardia_entrada"></span>
            </div>

            <div class="cerrar">
                Solicitud Cerrada
                <span class="cierre-box" data-field="cerrada"></span>
            </div>

        </div>

    </div>

</div>