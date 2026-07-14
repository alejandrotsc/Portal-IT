@extends('layouts.app')

@section('content')

<link rel="stylesheet" href="{{ asset('css/memorandos_compra.css') }}">


<div class="memorando-container">



    {{-- =====================================================
         HEADER
    ====================================================== --}}


    <div class="page-header no-print">


        <a href="{{ route('dashboard') }}" class="btn btn-secondary">
            ← Volver al Dashboard
        </a>



        <img
            src="{{ asset('img/tvc.png') }}"
            class="logo-tvc"
            alt="TVC">



        <div>

            <h1>
                Solicitud de Compra
            </h1>


            <p>
                Generación de solicitudes internas de TI
            </p>

        </div>


    </div>






    {{-- =====================================================
         FORMULARIO
    ====================================================== --}}


    <form
        id="documentForm"
        method="POST"
        action="{{ route('memorandos.store') }}">


        @csrf



        <input
            type="hidden"
            name="tipo_documento"
            id="tipo_documento">






        <div class="memo-layout">





            {{-- =================================================
                 PANEL FORMULARIO
            ================================================== --}}


            <div class="formulario-panel no-print">







                {{-- TIPO GESTIÓN --}}


                <div class="form-section highlight-box">


                    <label>
                        Tipo de gestión
                    </label>



                    <select
                        id="tipo_id"
                        name="tipo_id"
                        required>



                        <option value="">
                            Seleccione una opción
                        </option>



                        @foreach($tipos as $tipo)


                            <option

                                value="{{ $tipo->id }}"

                                data-formulario="{{ $tipo->formulario }}"

                                data-folio="{{ $tipo->requiere_folio ? '1':'0' }}">


                                {{ $tipo->nombre }}


                            </option>


                        @endforeach



                    </select>


                </div>









                {{-- INFORMACIÓN DOCUMENTO --}}


                <div class="form-section">


                    <h3>
                        Información del documento
                    </h3>



                    <div class="form-grid">





                        <div class="form-group">


                            <label>
                                PARA
                            </label>


                            <input

                                type="text"

                                name="para"

                                id="para"

                                value="Ing. Osman Madrid - Director Sr Operaciones"

                                readonly>


                        </div>







                        <div class="form-group">


                            <label>
                                CC
                            </label>


                            <input

                                type="text"

                                name="cc"

                                id="cc"

                                value="Lic. Juan Carlos Dique - Director Finanzas y Administración"

                                readonly>


                        </div>







                        <div class="form-group">


                            <label>
                                DE
                            </label>


                            <input

                                type="text"

                                name="de"

                                id="de"

                                value="Lic. Wesly López - Director Senior de Información y Tecnología"

                                required>


                        </div>







                        <div class="form-group">


                            <label>
                                Fecha
                            </label>


                            <input

                                type="date"

                                name="fecha"

                                id="fecha"

                                required>


                        </div>







                        <div class="form-group full">


                            <label>
                                Asunto
                            </label>


                            <input

                                type="text"

                                name="asunto"

                                id="asunto"

                                value="SOLICITUD DE COMPRA">


                        </div>





                    </div>


                </div>









                {{-- DETALLE COMPRA --}}


                <div class="form-section">


                    <h3>
                        Detalle de la compra
                    </h3>





                    <div class="form-grid">






                        <div class="form-group">


                            <label>
                                Empresa
                            </label>



                            <select

                                name="empresa"

                                id="empresa"

                                required>



                                <option value="">
                                    Seleccione empresa
                                </option>



                                <option value="Compañía Televisora, S. A.">
                                    Compañía Televisora, S. A.
                                </option>



                                <option value="Telesistema Hondureño, S. A.">
                                    Telesistema Hondureño, S. A.
                                </option>



                                <option value="Centroamericana de Televisión">
                                    Centroamericana de Televisión
                                </option>



                                <option value="Inmuebles y Desarrollo">
                                    Inmuebles y Desarrollo
                                </option>



                                <option value="Circuito Televicentro">
                                    Circuito Televicentro
                                </option>



                                <option value="Emisoras Unidas">
                                    Emisoras Unidas
                                </option>



                            </select>



                        </div>









                        <div class="form-group">


                            <label>
                                Tipo de compra
                            </label>



                            <select

                                name="tipo_compra"

                                id="tipo_compra"

                                required>



                                <option value="">
                                    Seleccione opción
                                </option>



                                <option>
                                    Orden de pago
                                </option>


                                <option>
                                    Contratación de servicios
                                </option>


                                <option>
                                    Renovación de servicios
                                </option>


                                <option>
                                    Repuestos
                                </option>


                                <option>
                                    Laptops
                                </option>


                                <option>
                                    Desktop
                                </option>


                                <option>
                                    Monitores
                                </option>


                                <option>
                                    Accesorios
                                </option>



                            </select>


                        </div>









                        <div class="form-group">


                            <label>
                                Tipo de compra
                            </label>



                            <select

                                name="tipo_mercado"

                                id="tipo_mercado">



                                <option value="Compra local">
                                    Compra local
                                </option>



                                <option value="Compra exterior">
                                    Compra exterior
                                </option>



                            </select>


                        </div>





                    </div>


                </div>








                {{-- ARTÍCULOS --}}


                <div class="form-section">


                    <h3>
                        Artículos solicitados
                    </h3>





                    <table class="tabla-input-articulos">


                        <thead>

                            <tr>

                                <th>
                                    Código
                                </th>


                                <th>
                                    Descripción
                                </th>


                                <th>
                                    Unidad
                                </th>


                                <th>
                                    Cantidad
                                </th>


                                <th>
                                </th>

                            </tr>

                        </thead>




                        <tbody id="articulosBody">



                            <tr class="fila-articulo">

    <td>
        <input
            type="text"
            name="articulos[0][codigo]">
    </td>


    <td>
        <input
            type="text"
            name="articulos[0][descripcion]">
    </td>


    <td>

        <select
            name="articulos[0][unidad]">

            <option value="Unidad">
                Unidad
            </option>

            <option value="Servicio">
                Servicio
            </option>

        </select>

    </td>


    <td>

        <input
            type="number"
            min="1"
            value="1"
            name="articulos[0][cantidad]">

    </td>


    <td>

        <button
            type="button"
            class="btnEliminarArticulo">
            ✕
        </button>

    </td>


</tr>




                        </tbody>



                    </table>





                    <button

                        type="button"

                        id="btnAgregarArticulo"

                        class="btn btn-secondary">


                        + Agregar artículo


                    </button>



                </div>

                                {{-- MOTIVOS DE SOLICITUD --}}


                <div class="form-section">


                    <h3>
                        Motivo de solicitud
                    </h3>




                    <div class="form-grid">






                        <div class="form-group">


                            <label>
                                A. Nivel mínimo de inventario
                            </label>



                            <select

                                name="inventario"

                                id="inventario">



                                <option value="">
                                    Seleccione
                                </option>



                                <option value="Si">
                                    Sí
                                </option>



                                <option value="No">
                                    No
                                </option>



                            </select>



                        </div>








                        <div class="form-group full">


                            <label>
                                B. Trabajos de mantenimiento o reparación de:
                            </label>



                            <input

                                type="text"

                                name="mantenimiento"

                                id="mantenimiento"

                                maxlength="250">



                        </div>








                        <div class="form-group full">


                            <label>
                                C. Trabajos en el proyecto de:
                            </label>



                            <input

                                type="text"

                                name="proyecto"

                                id="proyecto"

                                maxlength="250">



                        </div>








                        <div class="form-group full">


                            <label>
                                D. Otros:
                            </label>



                            <textarea

                                name="otros"

                                id="otros"

                                rows="3"></textarea>



                        </div>





                    </div>


                </div>









                {{-- PROVEEDOR --}}


                <div class="form-section">


                    <h3>
                        Información del proveedor
                    </h3>





                    <div class="form-grid">





                        <div class="form-group">


                            <label>
                                Proveedor
                            </label>



                            <input

                                type="text"

                                name="proveedor"

                                id="proveedor"

                                maxlength="200">



                        </div>







                        <div class="form-group full">


                            <label>
                                Razón del proveedor
                            </label>



                            <textarea

                                name="razon_proveedor"

                                id="razon_proveedor"

                                rows="3"></textarea>
                        </div>
                    </div>
                </div>
            </div>

                        {{-- ==================================================
                 PREVIEW DOCUMENTOS
            =================================================== --}}


            <div class="preview-panel">



                <div class="document-viewer">






                    <div class="document-tabs no-print">



                        <button

                            type="button"

                            class="document-tab active"

                            data-page="memo">


                            Memorándum


                        </button>





                        <button

                            type="button"

                            class="document-tab"

                            data-page="compra">


                            Solicitud de Compra


                        </button>




                    </div>








                    <div class="document-container">







                        {{-- ======================================
                             MEMORÁNDUM
                        ======================================= --}}




                        <div

                            class="document-page active"

                            id="page-memo">





                            <div class="document-header">



                                <img

                                    src="{{ asset('img/tvc.png') }}"

                                    class="logo-documento"

                                    alt="TVC">





                                <div class="titulo-documento">


                                    <h2>
                                        MEMORÁNDUM
                                    </h2>




                                    <div

                                        id="codigoDocumento"

                                        class="codigo-documento">


                                        PENDIENTE


                                    </div>



                                </div>



                            </div>









                            <table class="tabla-datos">


                                <tr>

                                    <td class="label">
                                        PARA
                                    </td>


                                    <td>
                                        : Ing. Osman Madrid - Director Sr Operaciones
                                    </td>


                                </tr>





                                <tr>

                                    <td class="label">
                                        CC
                                    </td>


                                    <td>
                                        : Lic. Juan Carlos Dique - Director Finanzas y Administración
                                    </td>


                                </tr>






                                <tr>

                                    <td class="label">
                                        DE
                                    </td>


                                    <td>

                                        :
                                        <span id="out_de">
                                            -
                                        </span>

                                    </td>


                                </tr>






                                <tr>

                                    <td class="label">
                                        FECHA
                                    </td>


                                    <td>

                                        :
                                        <span id="out_fecha">
                                            -
                                        </span>

                                    </td>


                                </tr>






                                <tr>

                                    <td class="label">
                                        ASUNTO
                                    </td>


                                    <td>

                                        :
                                        <span id="out_asunto">
                                            Solicitud de Compra
                                        </span>


                                    </td>


                                </tr>



                            </table>








                            <div class="cuerpo-documento">


                                <p id="previewTexto">


                                    Por medio de la presente se solicita
                                    la generación de la orden de compra
                                    correspondiente.


                                </p>






                                <br>






                                <p>

                                    Se detalla la siguiente solicitud:

                                </p>






                                <table class="tabla-detalle-memo">


                                    <thead>


                                        <tr>


                                            <th>
                                                Código
                                            </th>


                                            <th>
                                                Descripción
                                            </th>


                                            <th>
                                                Cantidad
                                            </th>



                                        </tr>


                                    </thead>





                                    <tbody id="tablaDetalleMemo">


                                        <tr>


                                            <td colspan="3">
                                                Sin artículos registrados
                                            </td>


                                        </tr>


                                    </tbody>




                                </table>





                            </div>




                        </div>

                        



                        {{-- ======================================
                             SOLICITUD DE COMPRA
                        ======================================= --}}



                        <div

                            class="document-page"

                            id="page-compra">







                            <h2 class="titulo-documento">

                                SOLICITUD DE COMPRA

                            </h2>









                            {{-- EMPRESAS --}}


                            <div class="encabezado-compra">



                                <div class="empresas-col">



                                    <div class="empresa-item">


                                        <span>
                                            Compañía Televisora, S. A.
                                        </span>


                                        <span

                                            class="checkbox"

                                            data-empresa="Compañía Televisora, S. A.">

                                        </span>


                                    </div>





                                    <div class="empresa-item">


                                        <span>
                                            Telesistema Hondureño, S. A.
                                        </span>


                                        <span

                                            class="checkbox"

                                            data-empresa="Telesistema Hondureño, S. A.">

                                        </span>


                                    </div>






                                    <div class="empresa-item">


                                        <span>
                                            Centroamericana de Televisión
                                        </span>


                                        <span

                                            class="checkbox"

                                            data-empresa="Centroamericana de Televisión">

                                        </span>


                                    </div>



                                </div>









                                <div class="empresas-col">





                                    <div class="empresa-item">


                                        <span>
                                            Inmuebles y Desarrollo
                                        </span>


                                        <span

                                            class="checkbox"

                                            data-empresa="Inmuebles y Desarrollo">

                                        </span>


                                    </div>








                                    <div class="empresa-item">


                                        <span>
                                            Circuito Televicentro
                                        </span>


                                        <span

                                            class="checkbox"

                                            data-empresa="Circuito Televicentro">

                                        </span>


                                    </div>








                                    <div class="empresa-item">


                                        <span>
                                            Emisoras Unidas
                                        </span>


                                        <span

                                            class="checkbox"

                                            data-empresa="Emisoras Unidas">

                                        </span>


                                    </div>









                                    <br>





                                    <div class="empresa-item">


                                        <span>
                                            Compra local
                                        </span>


                                        <span

                                            class="checkbox"

                                            id="checkLocal">

                                        </span>


                                    </div>






                                    <div class="empresa-item">


                                        <span>
                                            Compra exterior
                                        </span>


                                        <span

                                            class="checkbox"

                                            id="checkExterior">

                                        </span>


                                    </div>





                                </div>



                            </div>









                            {{-- FECHA --}}


                            <div class="fecha-contenedor">



                                <table class="tabla-fecha">


                                    <tr>

                                        <th colspan="3">
                                            FECHA
                                        </th>

                                    </tr>





                                    <tr>


                                        <td>
                                            Día
                                        </td>


                                        <td>
                                            Mes
                                        </td>


                                        <td>
                                            Año
                                        </td>


                                    </tr>







                                    <tr>


                                        <td id="out_dia">
                                            -
                                        </td>


                                        <td id="out_mes">
                                            -
                                        </td>


                                        <td id="out_anio">
                                            -
                                        </td>


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


                                        <th>
                                            CODIGO
                                        </th>


                                        <th>
                                            DESCRIPCIÓN
                                        </th>


                                        <th>
                                            UNIDAD
                                        </th>


                                        <th>
                                            CANTIDAD
                                        </th>


                                    </tr>


                                </thead>







                                <tbody id="tablaArticulosPreview">


                                    <tr>


                                        <td colspan="4">

                                            Sin artículos registrados

                                        </td>


                                    </tr>



                                </tbody>




                            </table>












                            {{-- MOTIVOS --}}



                            <div class="motivos-compra">



                                <p>


                                    <strong>

                                        Estas compras son solicitadas para:

                                    </strong>


                                </p>








                                <p>


                                    A. Satisfacer el nivel mínimo de inventario:



                                    <span id="out_inventario">

                                        -

                                    </span>



                                </p>









                                <p>


                                    B. Trabajos de mantenimiento o reparación de:



                                </p>



                                <div

                                    class="campo-texto"

                                    id="out_mantenimiento">


                                    Pendiente


                                </div>









                                <p>


                                    C. Trabajos en el proyecto de:



                                </p>





                                <div

                                    class="campo-texto"

                                    id="out_proyecto">


                                    Pendiente


                                </div>









                                <p>


                                    D. Otros:



                                </p>






                                <div

                                    class="campo-texto"

                                    id="out_otros">


                                    Pendiente


                                </div>






                            </div>












                            {{-- PROVEEDOR --}}



                            <div class="proveedor-compra">





                                <p>


                                    Estas compras serán cotizadas directamente a:



                                    <strong id="out_proveedor">

                                        Pendiente

                                    </strong>



                                </p>







                                <p>


                                    Por las siguientes razones:



                                    <span id="out_razon_proveedor">


                                        Pendiente


                                    </span>



                                </p>




                            </div>












                            {{-- FIRMA --}}



                            <div class="firma-compra">



                                <div class="firma-linea"></div>




                                <p>
                                    Solicitó:
                                </p>



                                <p>

                                    <strong>
                                        Jefe de Departamento
                                    </strong>


                                </p>



                            </div>






                        </div>







                    </div>



                </div>



            </div>





        </div>









        {{-- BOTON --}}


        <div class="form-actions no-print">



            <button

                type="submit"

                class="btn btn-primary">



                <span id="btnSubmitText">

                    Generar Solicitud de Compra

                </span>



            </button>



        </div>






    </form>





</div>





@endsection