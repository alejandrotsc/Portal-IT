document.addEventListener('DOMContentLoaded',()=>{


    const form =
        document.getElementById('documentForm');


    const fecha =
        document.getElementById('fecha');


    const codigoDocumento =
        document.getElementById('codigoDocumento');


    const btnText =
        document.getElementById('btnSubmitText');



    /*
    ==========================================
    FECHA
    ==========================================
    */


    function fechaActual(){


        const hoy = new Date();


        return hoy.getFullYear()
        + "-"
        + String(hoy.getMonth()+1).padStart(2,'0')
        + "-"
        + String(hoy.getDate()).padStart(2,'0');


    }




    function fechaLarga(valor){


        if(!valor)
            return '—';



        const meses=[

            'enero',
            'febrero',
            'marzo',
            'abril',
            'mayo',
            'junio',
            'julio',
            'agosto',
            'septiembre',
            'octubre',
            'noviembre',
            'diciembre'

        ];



        const partes =
            valor.split('-');



        return `${partes[2]} de ${meses[Number(partes[1])-1]} del ${partes[0]}`;


    }




    window.fechaLarga =
        fechaLarga;



    if(fecha && !fecha.value){

        fecha.value =
            fechaActual();

    }




    /*
    ==========================================
    CAMPOS GENERALES PREVIEW
    ==========================================
    */


    function actualizarDatosGenerales(){


        const mapa = {


            out_de:'de',

            out_cc:'cc',

            out_asunto:'asunto',

            out_fecha:'fecha'


        };




        Object.entries(mapa)
        .forEach(([salida,entrada])=>{


            const out =
                document.getElementById(salida);


            const input =
                document.getElementById(entrada);



            if(out && input){


                if(entrada === 'fecha'){


                    out.textContent =
                        fechaLarga(input.value);


                }
                else{


                    out.textContent =
                        input.value || '—';


                }


            }


        });


    }






    document.addEventListener(
        'input',
        actualizarDatosGenerales
    );



    document.addEventListener(
        'change',
        actualizarDatosGenerales
    );



    actualizarDatosGenerales();





    /*
    ==========================================
    SUBMIT GENERAL
    ==========================================
    */


    if(form){


        form.addEventListener(
            'submit',
            async(e)=>{


                e.preventDefault();



                const datos =
                    new FormData(form);




                try{


                    if(btnText)
                    btnText.textContent =
                    'Generando...';




                    const response =
                    await fetch(

                        form.action,

                        {

                            method:'POST',

                            body:datos,


                            headers:{

                                'X-Requested-With':
                                'XMLHttpRequest'

                            }

                        }

                    );





                    const result =
                        await response.json();




                    if(result.success){



                        if(result.codigo && codigoDocumento){


                            codigoDocumento.textContent =
                            result.codigo;


                        }



                        alert(
                        'Documento generado correctamente'
                        );



                    }
                    else{


                        alert(
                        result.error ||
                        'Error generando documento'
                        );


                    }



                }
                catch(error){


                    console.error(error);


                    alert(
                    'Error comunicándose con el servidor'
                    );


                }
                finally{


                    if(btnText)
                    btnText.textContent =
                    'Generar documento';


                }


            }

        );


    }



});