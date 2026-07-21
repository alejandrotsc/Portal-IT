document.addEventListener('DOMContentLoaded', () => {


    if(window.lucide){
        lucide.createIcons();
    }



    /*
    |--------------------------------------------------------------------------
    | Evidencia - Drag & Drop
    |--------------------------------------------------------------------------
    */


    const dropzone = document.getElementById('dropzone');
    const input = document.getElementById('archivos');
    const preview = document.getElementById('preview');


    let archivosSeleccionados = [];



    if(dropzone && input){


        dropzone.addEventListener('click',()=>{

            input.click();

        });



        input.addEventListener('change',(e)=>{

            agregarArchivos(e.target.files);

        });



        dropzone.addEventListener(
            'dragover',
            e=>{

                e.preventDefault();

                dropzone.classList.add(
                    'border-primary'
                );

            }
        );



        dropzone.addEventListener(
            'dragleave',
            ()=>{

                dropzone.classList.remove(
                    'border-primary'
                );

            }
        );



        dropzone.addEventListener(
            'drop',
            e=>{

                e.preventDefault();


                dropzone.classList.remove(
                    'border-primary'
                );


                agregarArchivos(
                    e.dataTransfer.files
                );

            }
        );


    }




    function agregarArchivos(files){


        Array.from(files).forEach(file=>{


            if(
                file.type.startsWith('image/')
                &&
                file.size <= 10485760
            ){

                archivosSeleccionados.push(file);

            }


        });


        renderPreview();


    }





    function renderPreview(){


        preview.innerHTML = "";


        const dataTransfer =
            new DataTransfer();



        archivosSeleccionados.forEach(
            (file,index)=>{


                dataTransfer.items.add(file);


                const reader =
                    new FileReader();



                reader.onload=function(e){


                    preview.innerHTML += `

                    <div class="relative rounded-xl overflow-hidden border border-border">

                        <img src="${e.target.result}"
                        class="w-full h-28 object-cover">


                        <button
                        type="button"
                        class="btn-remove absolute top-1 right-1 bg-black/50 text-white rounded-full w-6 h-6">

                        ×

                        </button>

                    </div>

                    `;



                    preview
                    .querySelectorAll('.btn-remove')
                    .forEach(
                        (btn,i)=>{


                        btn.onclick=()=>{


                            archivosSeleccionados.splice(i,1);

                            renderPreview();


                        };


                    });


                };


                reader.readAsDataURL(file);


            }
        );



        input.files =
            dataTransfer.files;


    }






    /*
    |--------------------------------------------------------------------------
    | Submit AJAX
    |--------------------------------------------------------------------------
    */


    const form =
    document.getElementById(
        'incidenciaForm'
    );


const btnCancelar =
    document.getElementById(
        'btnCancelar'
    );



if(form){


    /*
    |--------------------------------------------------------------------------
    | Cancelar y limpiar formulario
    |--------------------------------------------------------------------------
    */

    if(btnCancelar){

        btnCancelar.addEventListener(
            'click',
            ()=>{

                /*
                 * Limpiar los campos del formulario.
                 */
                form.reset();


                /*
                 * Limpiar archivos guardados en JavaScript.
                 */
                archivosSeleccionados = [];


                /*
                 * Limpiar el input de archivos.
                 */
                if(input){

                    input.value = '';

                }


                /*
                 * Actualizar la vista previa.
                 */
                renderPreview();

            }
        );

    }


        form.addEventListener(
            'submit',
            async(e)=>{


                e.preventDefault();



                const btn =
                document.getElementById(
                    'btnEnviar'
                );


                const texto =
                document.getElementById(
                    'btnEnviarTexto'
                );



                btn.disabled=true;


                texto.textContent =
                "Enviando...";



                try{


                    let response =
                    await fetch(
                        form.action,
                        {

                        method:'POST',

                        headers:{

                            'X-CSRF-TOKEN':
                            document
                            .querySelector(
                            'input[name="_token"]'
                            ).value,

                            'Accept':
                            'application/json'

                        },

                        body:
                        new FormData(form)

                        }
                    );



                    let data =
                    await response.json();



                    abrirModal(
                        data.success,
                        data.message,
                        data.codigo
                    );



                    if(data.success){


                        form.reset();

                        archivosSeleccionados=[];

                        renderPreview();


                    }



                }
                catch(error){


                    abrirModal(
                        false,
                        'Error enviando la incidencia.',
                        ''
                    );


                }
                finally{


                    btn.disabled=false;


                    texto.textContent =
                    "Enviar reporte";


                    if(window.lucide){

                        lucide.createIcons();

                    }


                }



            }
        );


    }



});



function abrirModal(exito,mensaje,codigo){


    const modal =
    document.getElementById(
        'modalIncidencia'
    );


    if(!modal)return;



    document.getElementById(
        'modalMensaje'
    ).textContent =
    mensaje;



    document.getElementById(
        'codigoIncidencia'
    ).textContent =
    codigo
    ?
    "Código: "+codigo
    :
    "";



    modal.classList.remove(
        'hidden'
    );


    modal.classList.add(
        'flex'
    );



    if(window.lucide){

        lucide.createIcons();

    }


}



window.cerrarModal=function(){


    const modal =
    document.getElementById(
        'modalIncidencia'
    );


    modal.classList.add(
        'hidden'
    );


    modal.classList.remove(
        'flex'
    );


};