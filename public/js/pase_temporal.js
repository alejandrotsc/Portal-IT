document.addEventListener('DOMContentLoaded', () => {

    const form = document.getElementById('documentForm');

    if (!form) return;

    /*
    |--------------------------------------------------------------------------
    | SUBMIT → Abrir Outlook web con el correo pre-armado
    |--------------------------------------------------------------------------
    */

    form.addEventListener('submit', (e) => {

        e.preventDefault();

        // Datos de cabecera
        const de        = form.querySelector('[name="de_nombre"]')?.value       || '[Solicitante]';
        const cc        = form.querySelector('[name="cc_nombre"]')?.value       || '';
        const asunto    = form.querySelector('[name="asunto"]')?.value          || 'Autorización de ingreso de equipo';
        const fecha     = form.querySelector('[name="fecha_documento"]')?.value || '';

        // Datos del colaborador
        const colaborador = form.querySelector('[name="colaborador"]')?.value         || '[Colaborador]';
        const cargoArea   = form.querySelector('[name="cargo_area"]')?.value          || '[Cargo/Área]';
        const motivo      = form.querySelector('[name="motivo_autorizacion"]')?.value || '[Motivo]';
        const obs         = form.querySelector('[name="observaciones"]')?.value       || '';

        // Construir listado de equipos
        const tabla = document.getElementById('equipoFilas');
        const filas = tabla ? tabla.querySelectorAll('.fila-equipo') : [];

        let equiposTexto = '';

        filas.forEach((fila, i) => {
            const inputs = fila.querySelectorAll('input[type="text"]');
            
            const desc  = inputs[0]?.value || '-';
            const marca = inputs[1]?.value || '-';
            const modelo= inputs[2]?.value || '-';
            const serie = inputs[3]?.value || '-';
            const color = inputs[4]?.value || '-';

            equiposTexto += `\n  Equipo ${i + 1}:`;
            equiposTexto += `\n    Descripción : ${desc}`;
            equiposTexto += `\n    Marca       : ${marca}`;
            equiposTexto += `\n    Modelo      : ${modelo}`;
            equiposTexto += `\n    N° Serie    : ${serie}`;
            equiposTexto += `\n    Color       : ${color}`;
        });

        // Cuerpo del correo
        const cuerpo =
`Estimado equipo de Helpdesk,

Por este medio solicito la autorización correspondiente para el ingreso de equipo tecnológico que será utilizado por:

  Colaborador : ${colaborador}
  Cargo / Área: ${cargoArea}
  Fecha       : ${fecha}

Motivo de autorización:
  ${motivo}

Detalle del(os) equipo(s):
${equiposTexto}
${obs ? '\nObservaciones:\n  ' + obs : ''}

Gracias por su colaboración.

Atentamente,
${de}`;


        // Armar URL de Outlook web
        const to      = encodeURIComponent('helpdesk@televicentro.hn');
        const subject = encodeURIComponent(`Solicitud de Autorización de Ingreso de Equipo — ${colaborador}`);
        const body    = encodeURIComponent(cuerpo);
        const ccParam = cc ? `&cc=${encodeURIComponent(cc)}` : '';

        const outlookUrl = `https://outlook.office.com/mail/deeplink/compose?to=${to}${ccParam}&subject=${subject}&body=${body}`;

        window.open(outlookUrl, '_blank');

    });

});
