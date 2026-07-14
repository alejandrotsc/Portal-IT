@extends('layouts.app')

@section('content')

<div class="portal-container">


<section class="hero-section">

    <div class="hero-content">

        <h1>
            Administración de Gestiones TI
        </h1>

        <p>
            Control general de usuarios, procesos,
            flujos de trabajo y documentación del portal.
        </p>

    </div>

</section>





<section class="requests-section">

    <div class="section-header">

        <div>

            <h2>
                Resumen general
            </h2>

            <p>
                Indicadores actuales de la plataforma.
            </p>

        </div>

    </div>



    <div class="request-stats">


        <div class="stat-box">

            <div class="stat-icon process">
                <i data-lucide="users"></i>
            </div>

            <div>

                <strong>
                    85
                </strong>

                <span>
                    Usuarios activos
                </span>

            </div>

        </div>



        <div class="stat-box">

            <div class="stat-icon pending">
                <i data-lucide="clipboard-list"></i>
            </div>

            <div>

                <strong>
                    24
                </strong>

                <span>
                    Gestiones pendientes
                </span>

            </div>

        </div>



        <div class="stat-box">

            <div class="stat-icon solved">
                <i data-lucide="file-text"></i>
            </div>

            <div>

                <strong>
                    540
                </strong>

                <span>
                    Documentos registrados
                </span>

            </div>

        </div>



        <div class="stat-box">

            <div class="stat-icon history">
                <i data-lucide="activity"></i>
            </div>

            <div>

                <strong>
                    Activo
                </strong>

                <span>
                    Estado del portal
                </span>

            </div>

        </div>


    </div>

</section>








<section class="services-section">


<div class="section-header">

    <div>

        <h2>
            Administración
        </h2>

        <p>
            Gestión y control de procesos internos.
        </p>

    </div>

</div>





<div class="services-grid">





<a href="{{ route('usuarios.index') }}" class="service-card featured">

    <div class="service-icon">
        <i data-lucide="users"></i>
    </div>

    <h3>
        Usuarios
    </h3>

    <p>
        Crear, modificar, asignar roles
        y administrar accesos.
    </p>

</a>







<div class="service-card">

    <div class="service-icon">
        <i data-lucide="workflow"></i>
    </div>

    <h3>
        Flujos de aprobación
    </h3>

    <p>
        Configurar responsables,
        niveles y procesos de aprobación.
    </p>

</div>







<div class="service-card">

    <div class="service-icon">
        <i data-lucide="layers"></i>
    </div>

    <h3>
        Catálogo de gestiones
    </h3>

    <p>
        Administrar tipos de solicitudes,
        categorías y servicios disponibles.
    </p>

</div>







<div class="service-card">

    <div class="service-icon">
        <i data-lucide="folder-open"></i>
    </div>

    <h3>
        Gestión documental
    </h3>

    <p>
        Control de memorandos,
        cotizaciones y documentos asociados.
    </p>

</div>







<div class="service-card">

    <div class="service-icon">
        <i data-lucide="search"></i>
    </div>

    <h3>
        Seguimiento global
    </h3>

    <p>
        Consulta de todas las gestiones
        registradas en el portal.
    </p>

</div>







<div class="service-card">

    <div class="service-icon">
        <i data-lucide="bar-chart-3"></i>
    </div>

    <h3>
        Reportes
    </h3>

    <p>
        Indicadores, estadísticas
        y análisis de operación.
    </p>

</div>







<div class="service-card">

    <div class="service-icon">
        <i data-lucide="shield-check"></i>
    </div>

    <h3>
        Auditoría
    </h3>

    <p>
        Historial de acciones,
        cambios y movimientos.
    </p>

</div>





</div>


</section>









<section class="activity-section">


<div class="section-header">

    <div>

        <h2>
            Actividad reciente
        </h2>

        <p>
            Últimos movimientos del portal.
        </p>

    </div>

</div>





<div class="activity-list">



<div class="activity-card">


    <div class="activity-number">
        MEM-2026-0038
    </div>


    <div class="activity-content">

        <h4>
            Nuevo memorando registrado
        </h4>

        <p>
            Documento almacenado en histórico.
        </p>

    </div>


    <span class="status completed-status">
        Registrado
    </span>


</div>







<div class="activity-card">


    <div class="activity-number">
        FLOW-2026-004
    </div>


    <div class="activity-content">

        <h4>
            Flujo actualizado
        </h4>

        <p>
            Cambio en responsables de aprobación.
        </p>

    </div>


    <span class="status process-status">
        Actualizado
    </span>


</div>




</div>


</section>








<section class="quick-section">


<div class="section-header">

    <div>

        <h2>
            Accesos rápidos
        </h2>

        <p>
            Opciones administrativas frecuentes.
        </p>

    </div>

</div>





<div class="quick-grid">



<div class="quick-card">

    <i data-lucide="users"></i>

    <span>
        Usuarios
    </span>

</div>




<div class="quick-card">

    <i data-lucide="workflow"></i>

    <span>
        Flujos
    </span>

</div>




<div class="quick-card">

    <i data-lucide="file-search"></i>

    <span>
        Auditoría
    </span>

</div>




<div class="quick-card">

    <i data-lucide="download"></i>

    <span>
        Reportes
    </span>

</div>



</div>


</section>



</div>


@endsection