<div class="preview-dinamico" data-preview="solicitud_compra">
<div class="document-viewer">

    <div class="document-tabs no-print">
        <button type="button" class="document-tab active" data-page="memo">Memorándum</button>
        <button type="button" class="document-tab" data-page="compra">Solicitud de Compra</button>
    </div>

    <div class="document-container">

        <div class="document-page active memo-solicitud-compra" id="page-memo">
            <div class="documento-memo-compra">

                <div class="memo-logo-compra">
                    <img src="{{ asset('img/tvc.png') }}" alt="TVC">
                </div>

                <div class="memo-titulo-compra">
                    <h1>MEMORÁNDUM</h1>
                    <p id="codigoDocumento">PENDIENTE</p>
                </div>

                <table class="memo-info-compra">
                    <tr>
                        <td class="label">Para</td>
                        <td>: <span id="out_para_compra">-</span></td>
                    </tr>

                    <tr>
                        <td class="label">CC</td>
                        <td>: <span id="out_cc_compra">-</span></td>
                    </tr>

                    <tr>
                        <td class="label">DE</td>
                        <td>: <span id="out_de_compra">-</span></td>
                    </tr>

                    <tr>
                        <td class="label">FECHA</td>
                        <td>: <span id="out_fecha_compra">-</span></td>
                    </tr>

                    <tr>
                        <td class="label">ASUNTO</td>
                        <td>: <span id="out_asunto_compra">-</span></td>
                    </tr>
                </table>

                <div class="memo-contenido-compra">
                    <p id="previewTexto">
                        Por medio de la presente se solicita la generación de orden de compra para la compra de lo siguiente:
                    </p>

                    <table class="tabla-detalle-memo">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Descripción</th>
                                <th>Cantidad</th>
                            </tr>
                        </thead>

                        <tbody id="tablaDetalleMemo">
                            <tr>
                                <td colspan="3">Sin artículos registrados</td>
                            </tr>
                        </tbody>
                    </table>

                    <p>Equipos a asignar según solicitud interna.</p>

                    <br>

                    <p>Se adjunta la siguiente documentación:</p>

                    <ol class="lista-documentos">
                        <li>1. Solicitud de orden de compra.</li>
                        <li>2. Cotización por parte de <span id="out_proveedor_documento">-</span>.</li>
                    </ol>
                </div>

            </div>
        </div>

        {{-- SOLICITUD DE COMPRA --}}
        <div class="document-page" id="page-compra">
            <div class="documento-compra">

                <h2 class="compra-titulo">SOLICITUD DE COMPRA</h2>

                {{-- EMPRESAS --}}
                <div class="encabezado-compra">
                    <div class="empresas-col">
                        <div class="empresa-item">
                            <span>Compañía Televisora, S. A.</span>
                            <span class="checkbox" data-empresa="Compañía Televisora, S. A."></span>
                        </div>
                        <div class="empresa-item">
                            <span>Telesistema Hondureño, S. A.</span>
                            <span class="checkbox" data-empresa="Telesistema Hondureño, S. A."></span>
                        </div>
                        <div class="empresa-item">
                            <span>Centroamericana de Televisión</span>
                            <span class="checkbox" data-empresa="Centroamericana de Televisión"></span>
                        </div>
                    </div>

                    <div class="empresas-col">
                        <div class="empresa-item">
                            <span>Inmuebles y Desarrollo</span>
                            <span class="checkbox" data-empresa="Inmuebles y Desarrollo"></span>
                        </div>
                        <div class="empresa-item">
                            <span>Circuito Televicentro</span>
                            <span class="checkbox" data-empresa="Circuito Televicentro"></span>
                        </div>
                        <div class="empresa-item">
                            <span>Market Medios</span>
                            <span class="checkbox" data-empresa="Market Medios"></span>
                        </div>
                        <div class="empresa-item">
                            <span>Emisoras Unidas</span>
                            <span class="checkbox" data-empresa="Emisoras Unidas"></span>
                        </div>

                        <br>

                        <div class="empresa-item">
                            <span>Compra local</span>
                            <span class="checkbox" id="checkLocal"></span>
                        </div>
                        <div class="empresa-item">
                            <span>Compra exterior</span>
                            <span class="checkbox" id="checkExterior"></span>
                        </div>
                    </div>
                </div>

                {{-- FECHA --}}
                <div class="fecha-contenedor">
                    <table class="tabla-fecha">
                        <tr>
                            <th colspan="3">FECHA</th>
                        </tr>
                        <tr>
                            <td>Día</td>
                            <td>Mes</td>
                            <td>Año</td>
                        </tr>
                        <tr>
                            <td id="out_dia">-</td>
                            <td id="out_mes">-</td>
                            <td id="out_anio">-</td>
                        </tr>
                    </table>
                </div>

                <p class="texto-intro">
                    Sírvase efectuar los trámites necesarios para comprar los siguientes artículos o servicios:
                </p>

                {{-- TABLA ARTICULOS --}}
                <table class="tabla-articulos">
                    <thead>
                        <tr>
                            <th>CODIGO</th>
                            <th>DESCRIPCIÓN</th>
                            <th>UNIDAD</th>
                            <th>CANTIDAD</th>
                        </tr>
                    </thead>
                    <tbody id="tablaArticulosPreview">
                        <tr>
                            <td colspan="4">Sin artículos registrados</td>
                        </tr>
                    </tbody>
                </table>

                {{-- MOTIVOS --}}
                <div class="motivos-compra">
                    <p><strong>Estas compras son solicitadas para:</strong></p>

                    <p>A. Satisfacer el nivel mínimo de inventario: <span id="out_inventario">-</span></p>

                    <p>B. Trabajos de mantenimiento o reparación de:</p>
                    <div class="campo-texto" id="out_mantenimiento">Pendiente</div>

                    <p>C. Trabajos en el proyecto de:</p>
                    <div class="campo-texto" id="out_proyecto">Pendiente</div>

                    <p>D. Otros:</p>
                    <div class="campo-texto" id="out_otros">Pendiente</div>
                </div>

                {{-- PROVEEDOR --}}
                <div class="proveedor-compra">
                    <p>Estas compras serán cotizadas directamente a: <strong id="out_proveedor">Pendiente</strong></p>
                    <p>Por las siguientes razones: <span id="out_razon_proveedor">Pendiente</span></p>
                </div>

                {{-- FIRMA SOLICITUD --}}
                <div class="firma-compra">
                    <div class="firma-linea"></div>
                    <p>Solicitó:</p>
                    <p><strong>Jefe de Departamento</strong></p>
                </div>

            </div>
        </div>

    </div>
</div>
</div>