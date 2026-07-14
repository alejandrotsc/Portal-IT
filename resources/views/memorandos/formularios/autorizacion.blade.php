<div
    class="formulario-dinamico space-y-5"
    data-formulario="autorizacion">


    {{-- INFORMACIÓN DE AUTORIZACIÓN --}}
    <div class="bg-card rounded-2xl border border-border overflow-hidden">

        <div class="px-6 py-4 border-b border-border flex items-center gap-3">

            <span class="w-6 h-6 rounded-full bg-primary text-white text-xs font-semibold flex items-center justify-center">
                3
            </span>

            <h2 class="text-sm font-semibold text-foreground">
                Información de autorización
            </h2>

        </div>


        <div class="px-6 py-5 space-y-4">


            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">


                <div>

                    <label class="block text-xs font-semibold text-muted-foreground uppercase tracking-widest mb-1.5">
                        Colaborador que utilizará el equipo
                        <span class="text-primary">*</span>
                    </label>


                    <input
                        required
                        type="text"
                        name="colaborador"
                        id="colaborador"
                        placeholder="Nombre completo del colaborador"
                        class="w-full px-3.5 py-2.5 rounded-lg border border-border bg-white text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">

                </div>



                <div>

                    <label class="block text-xs font-semibold text-muted-foreground uppercase tracking-widest mb-1.5">
                        Cargo / Área
                        <span class="text-primary">*</span>
                    </label>


                    <input
                        required
                        type="text"
                        name="cargo_area"
                        id="cargo_area"
                        placeholder="Ej: Practicante de Infraestructura"
                        class="w-full px-3.5 py-2.5 rounded-lg border border-border bg-white text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all">

                </div>


            </div>



            <div>

                <label class="block text-xs font-semibold text-muted-foreground uppercase tracking-widest mb-1.5">
                    Motivo de autorización
                    <span class="text-primary">*</span>
                </label>


                <textarea
                    required
                    name="motivo_autorizacion"
                    id="motivo_autorizacion"
                    rows="4"
                    placeholder="Describa el motivo por el cual se requiere autorización de ingreso del equipo..."
                    class="w-full px-3.5 py-2.5 rounded-lg border border-border bg-white text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all resize-none"></textarea>

            </div>


        </div>

    </div>





    {{-- INFORMACIÓN DEL EQUIPO --}}
    <div class="bg-card rounded-2xl border border-border overflow-hidden">


        <div class="px-6 py-4 border-b border-border flex items-center gap-3">

            <span class="w-6 h-6 rounded-full bg-primary text-white text-xs font-semibold text-center flex items-center justify-center">
                4
            </span>

            <h2 class="text-sm font-semibold text-foreground">
                Información del equipo
            </h2>

        </div>



        <div class="px-6 py-5">


            <div class="overflow-x-auto rounded-xl border border-border">


                <table class="w-full text-sm border-collapse">


                    <thead>

                        <tr class="bg-muted/60 border-b border-border">


                            @foreach(['Equipo','Marca','Modelo','Serie','Color'] as $columna)

                            <th class="px-3 py-2.5 text-left text-xs font-semibold text-muted-foreground uppercase tracking-wider">

                                @if($columna === 'Serie')

                                    {{-- El ícono ahora es de "ayuda" (circle-help), no de "play",
                                         para dejar claro que es información consultable y no un
                                         reproductor embebido. Abre el modal #modalSerie de abajo. --}}
                                    <div class="flex items-center gap-1">

                                        Serie

                                        <button
                                            type="button"
                                            id="btnAyudaSerie"
                                            onclick="abrirAyudaSerie()"
                                            title="¿Dónde encontrar el número de serie?"
                                            class="text-muted-foreground hover:text-primary transition-colors">

                                            <i
                                                data-lucide="circle-help"
                                                class="w-4 h-4">
                                            </i>

                                        </button>

                                    </div>

                                @else

                                    {{ $columna }}

                                @endif


                            </th>

                            @endforeach


                            <th></th>


                        </tr>

                    </thead>




                    <tbody
                        id="equipoFilas"
                        class="divide-y divide-border">


                        <tr class="fila-equipo">


                            <td class="px-2 py-2">
                                <input
                                    required
                                    type="text"
                                    name="equipos[0][descripcion]"
                                    placeholder="Laptop"
                                    class="input-equipo">
                            </td>


                            <td class="px-2 py-2">
                                <input
                                    required
                                    type="text"
                                    name="equipos[0][marca]"
                                    placeholder="Dell"
                                    class="input-equipo">
                            </td>


                            <td class="px-2 py-2">
                                <input
                                    required
                                    type="text"
                                    name="equipos[0][modelo]"
                                    placeholder="Latitude 5420"
                                    class="input-equipo">
                            </td>


                            <td class="px-2 py-2">
                                <input
                                    required
                                    type="text"
                                    name="equipos[0][codigo]"
                                    placeholder="SN123456"
                                    class="input-equipo">
                            </td>


                            <td class="px-2 py-2">
                                <input
                                    required
                                    type="text"
                                    name="equipos[0][color]"
                                    placeholder="Negro"
                                    class="input-equipo">
                            </td>



                            <td class="px-2 py-2 text-center">

                                <button
                                    type="button"
                                    class="btn-remove-fila p-2 rounded-lg text-muted-foreground hover:text-red-500 hover:bg-red-50 transition-colors">

                                    <i data-lucide="trash-2" class="w-4 h-4"></i>

                                </button>

                            </td>


                        </tr>


                    </tbody>


                </table>


            </div>




            <button
    type="button"
    id="agregarFila"
    class="mt-4 flex items-center gap-2 px-4 py-2 rounded-xl
           border border-dashed border-border
           text-sm text-muted-foreground
           hover:border-primary
           hover:text-primary
           hover:bg-primary/5
           transition-all duration-200">

    <i
        data-lucide="plus"
        class="w-4 h-4">
    </i>

    Agregar equipo

</button>


        </div>


    </div>



</div>





{{-- TEMPLATE PARA NUEVAS FILAS --}}
<template id="templateEquipo">

<tr class="fila-equipo">


    <td class="px-2 py-2">
        <input
            required
            type="text"
            name="equipos[INDEX][descripcion]"
            placeholder="Laptop"
            class="input-equipo">
    </td>


    <td class="px-2 py-2">
        <input
            required
            type="text"
            name="equipos[INDEX][marca]"
            placeholder="Dell"
            class="input-equipo">
    </td>


    <td class="px-2 py-2">
        <input
            required
            type="text"
            name="equipos[INDEX][modelo]"
            placeholder="Latitude 5420"
            class="input-equipo">
    </td>


    <td class="px-2 py-2">
        <input
            required
            type="text"
            name="equipos[INDEX][codigo]"
            placeholder="SN123456"
            class="input-equipo">
    </td>


    <td class="px-2 py-2">
        <input
            required
            type="text"
            name="equipos[INDEX][color]"
            placeholder="Negro"
            class="input-equipo">
    </td>



    <td class="px-2 py-2 text-center">

        <button
            type="button"
            class="btn-remove-fila p-2 rounded-lg text-muted-foreground hover:text-red-500 hover:bg-red-50 transition-colors">

            <i data-lucide="trash-2" class="w-4 h-4"></i>

        </button>

    </td>


</tr>

</template>




{{-- MODAL AYUDA SERIE --}}
{{-- Nota: se agrega la clase "modal-overlay" solo como gancho opcional
     para cerrar el modal al hacer click fuera del contenido (ver JS).
     Tamaño aumentado de max-w-lg a max-w-3xl (y max-h-[90vh] con scroll)
     para que el video se aprecie bien sin tener que ir a fullscreen. --}}
<div
    id="modalSerie"
    class="modal-overlay fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4">


    <div
        class="w-full max-w-xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white shadow-xl border border-border">


        {{-- HEADER --}}

        <div
            class="px-6 py-4 border-b border-border flex items-center justify-between sticky top-0 bg-white z-10">


            <div class="flex items-center gap-3">


                <div
                    class="w-9 h-9 rounded-xl bg-primary/10 flex items-center justify-center">


                    <i
                        data-lucide="circle-help"
                        class="w-5 h-5 text-primary">
                    </i>


                </div>


                <h3 class="text-sm font-semibold text-foreground">

                    ¿Dónde encontrar el número de serie?

                </h3>


            </div>



            <button
                type="button"
                onclick="cerrarAyudaSerie()"
                class="text-muted-foreground hover:text-red-500 transition-colors">


                <i
                    data-lucide="x"
                    class="w-5 h-5">
                </i>


            </button>


        </div>





        {{-- BODY --}}

        <div class="p-6">


            <video
                controls
                poster="{{ asset('img/snhelp.avif') }}"
                class="mx-auto max-h-[70vh] w-auto max-w-full rounded-xl border border-border">


                <source
                    src="{{ asset('videos/snhelp_fixed.mp4') }}"
                    type="video/mp4">


                {{-- FALLBACK --}}

                <img
                    src="{{ asset('img/snhelp.avif') }}"
                    alt="Ubicación número de serie"
                    class="w-full rounded-xl">


            </video>




            <div class="mt-4 space-y-3">


                <p class="text-xs text-muted-foreground leading-relaxed">

                    Esta guía muestra un ejemplo para equipos
                    <strong>Dell</strong>.

                    La ubicación del número de serie puede cambiar
                    dependiendo de la <strong>marca</strong> y el
                    <strong>modelo</strong> de la laptop.

                </p>



                <p class="text-xs text-muted-foreground leading-relaxed">

                    Busca etiquetas identificadas como:

                    <strong>Serial Number</strong>,
                    <strong>Service Tag</strong>
                    o
                    <strong>S/N</strong>.

                </p>


            </div>


        </div>


    </div>


</div>