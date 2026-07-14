<div class="bg-card rounded-2xl border border-border overflow-hidden">

    {{-- Header --}}

    <div class="px-6 py-4 border-b border-border flex items-center gap-3">

        <span class="w-6 h-6 rounded-full bg-primary text-white text-xs font-semibold flex items-center justify-center">
            2
        </span>

        <h2 class="text-sm font-semibold text-foreground">
            Información del documento
        </h2>

    </div>



    <div class="px-6 py-5 space-y-5">


        {{-- PARA / CC --}}

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">


            {{-- PARA --}}

            <div>

                <label class="block text-xs font-semibold text-muted-foreground uppercase tracking-widest mb-1.5">
                    PARA
                </label>


                <div class="w-full px-3.5 py-2.5 rounded-lg bg-muted/60 border border-border text-sm text-foreground font-medium">

                    Lic. Byron Castro — Director de Seguridad

                </div>


                <input type="hidden"
                       name="para_nombre"
                       value="Lic. Byron Castro — Director de Seguridad">

            </div>




            {{-- CC --}}

<div>

    <label class="block text-xs font-semibold text-muted-foreground uppercase tracking-widest mb-1.5">

        CC

        <span class="text-primary">*</span>

    </label>


    <select
        name="cc_nombre"
        required
        class="w-full px-3.5 py-2.5 rounded-lg border border-border bg-white text-sm text-foreground focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10">


        <option value="Lic. Wesly López — Director Senior de TI" selected>

            Lic. Wesly López — Director Senior de TI

        </option>


        <option value="Lic. Fernando Figueroa — Coordinador de Infraestructura TI">

            Lic. Fernando Figueroa — Coordinador de Infraestructura TI

        </option>


    </select>


</div>



        </div>






        {{-- DE / FECHA --}}


        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">



            {{-- DE --}}

            <div>


                <label class="block text-xs font-semibold text-muted-foreground uppercase tracking-widest mb-1.5">

                    DE — Nombre y cargo del solicitante

                    <span class="text-primary">*</span>

                </label>



                <input
                    required
                    type="text"
                    name="de_nombre"
                    placeholder="Ej: Ing. Ana Martínez — Coordinadora de Sistemas"
                    class="w-full px-3.5 py-2.5 rounded-lg border border-border bg-white text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10">


            </div>


            {{-- Fecha --}}

            <div>


                <label class="block text-xs font-semibold text-muted-foreground uppercase tracking-widest mb-1.5">

                    Fecha

                    <span class="text-primary">*</span>

                </label>



                <div class="relative">


                    <input
                        type="date"
                        name="fecha_documento"
                        value="{{ date('Y-m-d') }}"
                        class="w-full px-3.5 py-2.5 rounded-lg border border-border bg-white text-sm text-foreground focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10">


                    


                </div>


            </div>


        </div>






        {{-- ASUNTO --}}


        <div>


            <label class="block text-xs font-semibold text-muted-foreground uppercase tracking-widest mb-1.5">


                Asunto

                <span class="text-primary">*</span>


            </label>




            <input
                type="text"
                name="asunto"
                value="Autorización de ingreso de equipo"
                placeholder="Asunto del memorando"
                class="w-full px-3.5 py-2.5 rounded-lg border border-border bg-white text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10">


        </div>



    </div>


</div>