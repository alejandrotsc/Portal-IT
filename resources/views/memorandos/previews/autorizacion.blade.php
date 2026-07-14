{{-- PREVIEW AUTORIZACIÓN DE EQUIPO --}}

<div class="documento-final">


    {{-- ENCABEZADO --}}

    <div class="document-header">

        <img 
            src="{{ asset('img/tvc.png') }}"
            class="logo-documento">

        <h1>
            MEMORÁNDUM
        </h1>

    </div>



    {{-- INFORMACIÓN DEL MEMORANDO --}}

    <div class="datos-documento">


        <table class="tabla-datos">


            <tr>
                <td class="label">
                    PARA:
                </td>

                <td id="out_para">
                    -
                </td>
            </tr>


            <tr>
                <td class="label">
                    CC:
                </td>

                <td id="out_cc">
                    -
                </td>
            </tr>


            <tr>
                <td class="label">
                    DE:
                </td>

                <td id="out_de">
                    -
                </td>
            </tr>


            <tr>
                <td class="label">
                    FECHA:
                </td>

                <td id="out_fecha_documento">
                    -
                </td>
            </tr>


            <tr>
                <td class="label">
                    ASUNTO:
                </td>

                <td id="out_asunto">
                    -
                </td>
            </tr>


        </table>


    </div>




    {{-- CUERPO DOCUMENTO --}}


    <div class="memo-body-preview">


        <p>
            Por este medio solicito la autorización correspondiente para
            el ingreso de equipo tecnológico que será utilizado por:
        </p>



        <p>

            <strong id="out_colaborador">
                -
            </strong>

            quien estará desempeñando actividades dentro de la compañía.

        </p>



        <p>

            Área o cargo:

            <strong id="out_cargo_area">
                -
            </strong>

        </p>



        <p>

            La fecha prevista para el ingreso del equipo será:

            <strong id="out_fecha_ingreso">
                -
            </strong>

        </p>



        <p>
            Motivo de autorización:
        </p>



        <p 
            id="out_motivo_autorizacion"
            class="campo-texto">

            -

        </p>



        <p>
            A continuación se detallan las características del equipo:
        </p>



    </div>




    {{-- TABLA EQUIPOS --}}


    <table class="equipo-output-table">


        <thead>

            <tr>

                <th>
                    EQUIPO
                </th>


                <th>
                    MARCA
                </th>


                <th>
                    MODELO
                </th>


                <th>
                    SERIE
                </th>


                <th>
                    COLOR
                </th>


            </tr>


        </thead>



        <tbody id="equipoSalida">


            <tr>

                <td 
                    colspan="5"
                    class="fila-vacia">

                    Sin equipos registrados

                </td>


            </tr>


        </tbody>



    </table>




    <p class="agradecimiento">

        Gracias por su colaboración.

    </p>




    {{-- FIRMA --}}


    <div class="firma">


        <div></div>


        <p>
            Vo.Bo. Jefe Inmediato
        </p>


        <small>
            Firma y sello
        </small>


    </div>



</div>