document.addEventListener('DOMContentLoaded', () => {


    const form = document.getElementById('documentForm');


    if (!form) return;



    /*
    |--------------------------------------------------------------------------
    | ENVÍO DE PASE TEMPORAL
    |--------------------------------------------------------------------------
    |
    | Este formulario NO genera PDF.
    | Envía los datos a Laravel para:
    | - Guardar memorando
    | - Registrar historial
    | - Enviar correo a Helpdesk
    |
    |--------------------------------------------------------------------------
    */


    form.addEventListener('submit', async (e) => {


        e.preventDefault();



        const boton =
            form.querySelector(
                'button[type="submit"]'
            );



        const textoBoton =
            boton?.innerHTML;



        if (boton) {


            boton.disabled = true;


            boton.innerHTML = `

                <i 
                    data-lucide="loader-circle"
                    class="w-4 h-4 animate-spin">
                </i>

                Enviando...

            `;



            if(window.lucide)
                lucide.createIcons();


        }




        try {



            const formData =
                new FormData(form);





            const response =
                await fetch(
                    form.action,
                    {

                        method:'POST',


                        headers:{


                            'X-Requested-With':
                                'XMLHttpRequest',


                            'Accept':
                                'application/json'


                        },


                        body:
                            formData

                    }
                );






            /*
            |--------------------------------------------------------------------------
            | Leer respuesta Laravel
            |--------------------------------------------------------------------------
            */


            let data;



            const tipoRespuesta =
                response.headers.get(
                    'content-type'
                );



            if(
                tipoRespuesta &&
                tipoRespuesta.includes(
                    'application/json'
                )
            ){


                data =
                    await response.json();


            }
            else {



                const html =
                    await response.text();



                console.error(
                    'Respuesta no JSON:',
                    html
                );



                throw new Error(
                    'Laravel devolvió una respuesta inválida.'
                );


            }






            /*
            |--------------------------------------------------------------------------
            | RESPUESTA CORRECTA
            |--------------------------------------------------------------------------
            */


            if(
                response.ok &&
                data.success
            ){



                mostrarResultado(
                    true,
                    'Solicitud enviada',
                    data.message ??
                    'El correo fue enviado correctamente a Helpdesk.'
                );



                form.reset();




            }
            else {



                console.error(
                    'Error backend:',
                    data
                );



                mostrarResultado(
                    false,
                    'Error al enviar',
                    data.error ??
                    'No se pudo procesar la solicitud.'
                );


            }






        }
        catch(error){



            console.error(
                'Error JS:',
                error
            );



            mostrarResultado(
                false,
                'Error de conexión',
                error.message ??
                'No fue posible comunicarse con el servidor.'
            );



        }
        finally {



            if(boton){



                boton.disabled = false;



                boton.innerHTML =
                    textoBoton;



                if(window.lucide)
                    lucide.createIcons();


            }



        }



    });




});





/*
|--------------------------------------------------------------------------
| MODAL RESULTADO
|--------------------------------------------------------------------------
*/


function mostrarResultado(
    correcto,
    titulo,
    mensaje
){


    const modal =
        document.getElementById(
            'modalResultado'
        );



    if(!modal)
        return;





    const tituloModal =
        document.getElementById(
            'modalTitulo'
        );



    const mensajeModal =
        document.getElementById(
            'modalMensaje'
        );



    const icono =
        document.getElementById(
            'modalIcono'
        );





    if(tituloModal)
        tituloModal.textContent =
            titulo;



    if(mensajeModal)
        mensajeModal.textContent =
            mensaje;





    if(icono){


        if(correcto){


            icono.innerHTML = `

                <i 
                    data-lucide="check-circle"
                    class="w-5 h-5 text-green-600">
                </i>

            `;


            icono.className =
                'w-10 h-10 rounded-full bg-green-100 flex items-center justify-center';


        }
        else {



            icono.innerHTML = `

                <i 
                    data-lucide="x-circle"
                    class="w-5 h-5 text-red-600">
                </i>

            `;


            icono.className =
                'w-10 h-10 rounded-full bg-red-100 flex items-center justify-center';



        }


    }




    modal.classList.remove(
        'hidden'
    );


    modal.classList.add(
        'flex'
    );



    if(window.lucide)
        lucide.createIcons();



}







/*
|--------------------------------------------------------------------------
| CERRAR MODAL
|--------------------------------------------------------------------------
*/


document.addEventListener(
    'click',
    (e)=>{


        const boton =
            e.target.closest(
                '#cerrarModal'
            );



        if(!boton)
            return;



        const modal =
            document.getElementById(
                'modalResultado'
            );



        if(modal){


            modal.classList.add(
                'hidden'
            );


            modal.classList.remove(
                'flex'
            );


        }


    }
);





/*
|--------------------------------------------------------------------------
| Cerrar con ESC
|--------------------------------------------------------------------------
*/


document.addEventListener(
    'keydown',
    (e)=>{


        if(e.key !== 'Escape')
            return;



        const modal =
            document.getElementById(
                'modalResultado'
            );



        if(modal){


            modal.classList.add(
                'hidden'
            );


            modal.classList.remove(
                'flex'
            );


        }



    }
);