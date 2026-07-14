@extends('layouts.app')

@section('content')

<div class="portal-container">


    <!-- ================================================= -->
    <!-- HEADER / RESUMEN TI -->
    <!-- ================================================= -->

    <section class="hero-section">

        <div class="hero-content">

            <h1>
                Panel de Gestión TI
            </h1>

            <p>
                Administra solicitudes internas, documentos,
                compras y gestiones operativas del departamento.
            </p>

        </div>

    </section>





    <!-- ================================================= -->
    <!-- INDICADORES -->
    <!-- ================================================= -->

    <section class="requests-section">

        <div class="section-header">
            <div>
                <h2>
                    Resumen operativo
                </h2>

                <p>
                    Estado actual de las gestiones del área.
                </p>
            </div>
        </div>


        <div class="request-stats">


            <div class="stat-box">

                <div class="stat-icon pending">
                    <i data-lucide="clipboard-list"></i>
                </div>

                <div>
                    <strong>12</strong>
                    <span>Solicitudes pendientes</span>
                </div>

            </div>



            <div class="stat-box">

                <div class="stat-icon process">
                    <i data-lucide="shopping-cart"></i>
                </div>

                <div>
                    <strong>5</strong>
                    <span>Compras en proceso</span>
                </div>

            </div>



            <div class="stat-box">

                <div class="stat-icon solved">
                    <i data-lucide="file-check"></i>
                </div>

                <div>
                    <strong>34</strong>
                    <span>Memorandos generados</span>
                </div>

            </div>



            <div class="stat-box">

                <div class="stat-icon history">
                    <i data-lucide="archive"></i>
                </div>

                <div>
                    <strong>245</strong>
                    <span>Histórico</span>
                </div>

            </div>


        </div>

    </section>







<!-- ================================================= -->
<!-- GESTIONES TI -->
<!-- ================================================= -->

<section class="services-section">

    <div class="section-header">

        <div>

            <h2>
                Gestiones internas
            </h2>

            <p>
                Administración de procesos propios del área TI.
            </p>

        </div>

    </div>

    <div class="services-grid">

        <div class="service-card featured clickable"
             onclick="window.location='{{ route('memorandos.create.compra') }}'">

            <div class="service-icon">
                <i data-lucide="shopping-cart"></i>
            </div>

            <h3>
                Memorandos
            </h3>

            <p>
                Pases temporales, memorandos de autorización y solicitudes de orden de compra.
            </p>

        </div>

        <div class="service-card">

            <div class="service-icon">
                <i data-lucide="building-2"></i>
            </div>

            <h3>
                Proveedores
            </h3>

            <p>
                Cotizaciones, ofertas comerciales
                y documentación asociada.
            </p>

        </div>

        <div class="service-card">

            <div class="service-icon">
                <i data-lucide="refresh-cw"></i>
            </div>

            <h3>
                Renovación de servicios
            </h3>

            <p>
                Seguimiento de contratos,
                licencias y servicios activos.
            </p>

        </div>

        <div class="service-card">

            <div class="service-icon">
                <i data-lucide="wrench"></i>
            </div>

            <h3>
                Repuestos
            </h3>

            <p>
                Solicitudes de componentes
                y mantenimiento.
            </p>

        </div>

        <div class="service-card clickable"
             onclick="window.location='{{ route('memorandos.create') }}'">

            <div class="service-icon">
                <i data-lucide="file-text"></i>
            </div>

            <h3>
                Memorandos
            </h3>

            <p>
                Creación y gestión de documentos
                administrativos.
            </p>

        </div>

        <div class="service-card">

            <div class="service-icon">
                <i data-lucide="server"></i>
            </div>

            <h3>
                Inventario TI
            </h3>

            <p>
                Control de equipos,
                asignaciones y activos.
            </p>

        </div>

    </div>

</section>








    <!-- ================================================= -->
    <!-- ACTIVIDAD RECIENTE -->
    <!-- ================================================= -->


    <section class="activity-section">


        <div class="section-header">

            <div>

                <h2>
                    Actividad reciente
                </h2>

                <p>
                    Últimos movimientos registrados.
                </p>

            </div>

        </div>




        <div class="activity-list">


            <div class="activity-card">

                <div class="activity-number">
                    SOL-2026-0015
                </div>

                <div class="activity-content">

                    <h4>
                        Compra de laptop empresarial
                    </h4>

                    <p>
                        Cotización recibida del proveedor.
                    </p>

                </div>

                <span class="status process-status">
                    En proceso
                </span>

            </div>





            <div class="activity-card">

                <div class="activity-number">
                    MEM-2026-0038
                </div>

                <div class="activity-content">

                    <h4>
                        Memorando de renovación
                    </h4>

                    <p>
                        Documento generado y almacenado.
                    </p>

                </div>

                <span class="status completed-status">
                    Finalizado
                </span>

            </div>





        </div>


    </section>







    <!-- ================================================= -->
    <!-- ACCESOS RÁPIDOS -->
    <!-- ================================================= -->


    <section class="quick-section">

        <div class="section-header">

            <div>

                <h2>
                    Accesos rápidos
                </h2>

                <p>
                    Herramientas frecuentes del área.
                </p>

            </div>

        </div>



        <div class="quick-grid">


            <div class="quick-card">
                <i data-lucide="file-text"></i>
                <span>Histórico de memorandos</span>
            </div>


            <div class="quick-card">
                <i data-lucide="folder-open"></i>
                <span>Documentos SharePoint</span>
            </div>


            <div class="quick-card">
                <i data-lucide="truck"></i>
                <span>Proveedores</span>
            </div>


            <div class="quick-card">
                <i data-lucide="database"></i>
                <span>Inventario</span>
            </div>


        </div>


    </section>



</div>


@endsection