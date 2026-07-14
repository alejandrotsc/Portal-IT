<div
    class="formulario-dinamico"
    data-formulario="solicitud_compra">

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

                    <option value="Market Medios">
                        Market Medios
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