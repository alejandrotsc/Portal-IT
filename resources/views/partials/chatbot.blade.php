{{-- ==========================================================
    CHATBOT ASISTENTE VIRTUAL TI
    Compatible con:
    - ChatbotResponseBuilder
    - AIResponse
    - DiagnosticEngine
    - Alpine.js
========================================================== --}}

<section>

<div
    class="bg-card rounded-2xl border border-border overflow-hidden"
    x-data="chatbotWidget()"
    x-init="init()"
>


{{-- HEADER --}}
<div class="px-6 py-4 border-b border-border flex items-center gap-3">


    <div class="w-9 h-9 rounded-xl bg-primary/10 flex items-center justify-center">

        <i
            data-lucide="bot"
            class="w-[18px] h-[18px] text-primary">
        </i>

    </div>



    <div>

        <p class="text-sm font-semibold text-foreground">
            Asistente TI
        </p>


        <div class="flex items-center gap-1.5 mt-0.5">

            <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full"></span>


            <span class="text-xs text-muted-foreground">
                En línea
            </span>

        </div>

    </div>


</div>




{{-- CONTENEDOR MENSAJES --}}

<div
    id="chatbot-messages"
    x-ref="messages"
    class="px-6 py-5 min-h-[140px] max-h-96 overflow-y-auto space-y-4"
>



{{-- MENSAJE INICIAL --}}

<div class="flex gap-3">


<div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0">

<i
data-lucide="bot"
class="w-[15px] h-[15px] text-primary">
</i>

</div>



<div class="bg-muted rounded-2xl rounded-tl-sm px-4 py-3 max-w-lg">


<p class="text-sm text-foreground leading-relaxed">


Hola
{{auth()->check()
    ? ', '.explode(' ',auth()->user()->nombre)[0]
    : ''
}} 👋


</p>


<p class="text-sm text-foreground leading-relaxed mt-2">

Soy el asistente virtual del Portal TI.

Puedo ayudarte con:

</p>


<ul class="text-sm text-foreground mt-2 space-y-1">


<li>• Reportar incidencias</li>

<li>• Crear solicitudes</li>

<li>• Diagnóstico básico</li>

<li>• Consultar gestiones</li>


</ul>



</div>


</div>





{{-- MENSAJES DINÁMICOS --}}

<template
x-for="(msg,index) in messages"
:key="index"
>


<div
class="flex gap-3"
:class="msg.from === 'user'
    ? 'flex-row-reverse'
    : ''"
>



{{-- ICONO --}}

<div
class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0"

:class="msg.from === 'user'
    ? 'bg-primary'
    :'bg-primary/10'"
>


<i

:data-lucide="
msg.from === 'user'
? 'user'
:'bot'
"

class="w-[15px] h-[15px]"

:class="
msg.from === 'user'
?'text-white'
:'text-primary'
"

>
</i>


</div>





{{-- BURBUJA --}}

<div

class="rounded-2xl px-4 py-3 max-w-lg"

:class="msg.from === 'user'

?'bg-primary text-white rounded-tr-sm'

:'bg-muted text-foreground rounded-tl-sm'

"

>



<p

class="text-sm leading-relaxed whitespace-pre-line"

x-text="msg.text"

>
</p>






{{-- INFORMACION IA --}}

<template x-if="msg.ai">


<div class="mt-3 text-xs opacity-70">


<span>
Asistente:
</span>


<span x-text="msg.ai.category"></span>


<template x-if="msg.ai.confidence">

<span>

(
<span x-text="Math.round(msg.ai.confidence * 100)">
</span>
%)

</span>


</template>


</div>


</template>





{{-- GESTIONES --}}

<template
x-if="msg.items && msg.items.length"
>


<div class="mt-3 space-y-2">


<template
x-for="(item,i) in msg.items"
:key="i"
>


<a

:href="item.url"

class="block rounded-lg border border-border bg-card px-3 py-2 hover:border-primary transition"

>


<div class="flex justify-between gap-2">


<span

class="text-xs font-medium"

x-text="item.tipo">

</span>


<span

class="text-[11px] rounded-full bg-primary/10 text-primary px-2 py-0.5"

x-text="item.status">

</span>


</div>


<p

class="text-xs text-muted-foreground mt-1"

x-text="item.title">

</p>


</a>


</template>


</div>


</template>

{{-- REDIRECCIÓN A MÓDULO --}}

<template x-if="msg.redirect">


<a

:href="msg.redirect.url"

class="inline-flex items-center gap-2 mt-3 px-3 py-1.5 rounded-full text-xs font-medium bg-primary text-white hover:bg-blue-700 transition"


>


<i
data-lucide="external-link"
class="w-3 h-3">
</i>


<span x-text="msg.redirect.label"></span>


</a>


</template>







{{-- ACCIONES RÁPIDAS --}}

<template

x-if="msg.quick_actions && msg.quick_actions.length"

>


<div class="flex flex-wrap gap-2 mt-3">


<template

x-for="(action,i) in msg.quick_actions"

:key="i"

>


<button


@click="executeAction(action,msg)"


class="px-3 py-1 rounded-full text-xs font-medium bg-card border border-border text-foreground hover:bg-blue-600 hover:text-white hover:border-blue-600 transition"


>


<span x-text="action.label"></span>


</button>


</template>


</div>


</template>






{{-- DEBUG / INTENT (opcional para desarrollo)
Puedes quitarlo en producción
--}}

<template x-if="msg.intent">


<div class="mt-3 text-[10px] text-muted-foreground">


Intent:

<span x-text="msg.intent.name"></span>


|

Score:

<span x-text="msg.intent.score"></span>


</div>


</template>




</div>


</div>


</template>









{{-- INDICADOR ESCRIBIENDO --}}

<div

x-show="loading"

x-cloak

class="flex gap-3"


>


<div

class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center"

>


<i
data-lucide="bot"
class="w-[15px] h-[15px] text-primary">
</i>


</div>



<div class="bg-muted rounded-2xl px-4 py-3">


<span class="text-sm text-muted-foreground">

Escribiendo...

</span>


</div>



</div>





</div>










{{-- INPUT --}}

<div class="px-6 py-4 border-t border-border">


<form

@submit.prevent="send()"

class="flex items-center gap-3 rounded-xl border border-border bg-muted/50 px-4 py-3"


>


<i

data-lucide="message-circle"

class="w-4 h-4 text-muted-foreground"

>
</i>



<input


x-model="draft"


type="text"


placeholder="Escribe tu consulta de soporte..."


class="flex-1 bg-transparent text-sm outline-none text-foreground placeholder:text-muted-foreground"


>



<button


type="submit"


:disabled="loading || !draft.trim()"


class="w-8 h-8 rounded-lg bg-primary flex items-center justify-center disabled:opacity-50"


>


<i

data-lucide="send"

class="w-4 h-4 text-white"

>
</i>


</button>



</form>





<p class="text-xs text-muted-foreground mt-2">

También puedes usar las opciones rápidas:

</p>







<div class="flex flex-wrap gap-2 mt-3">


<button


@click="send('quiero reportar una incidencia')"


class="quick-button"

>


Reportar incidencia


</button>





<button


@click="send('quiero crear una solicitud')"


class="quick-button"

>


Crear solicitud


</button>





<button


@click="send('consultar estado')"


class="quick-button"

>


Consultar gestiones


</button>





<button


@click="send('necesito soporte tecnico')"


class="quick-button"

>


Soporte técnico


</button>


</div>




</div>



</div>


</section>

<script>

function chatbotWidget(){


    return {


        draft:'',


        loading:false,


        messages:[],






        init(){


            this.$watch(

                'messages',

                ()=>{


                    this.$nextTick(()=>{


                        if(window.lucide){

                            window.lucide.createIcons();

                        }



                        this.scrollBottom();


                    });


                }

            );


        },








        async send(text = null){



            const message =

                (text ?? this.draft)
                .trim();





            if(
                !message ||
                this.loading
            ){

                return;

            }








            /*
            |--------------------------------------------------------------------------
            | Mensaje usuario
            |--------------------------------------------------------------------------
            */


            this.messages.push({


                from:'user',


                text:message


            });





            this.draft='';


            this.loading=true;









            try{



                const response = await fetch(


                    "{{ route('chatbot.message') }}",


                    {


                        method:'POST',


                        headers:{


                            'Content-Type':'application/json',


                            'Accept':'application/json',


                            'X-CSRF-TOKEN':

                                document
                                .querySelector(
                                    'meta[name="csrf-token"]'
                                )
                                ?.content ?? ''


                        },


                        body:JSON.stringify({


                            message:message


                        })


                    }


                );









                if(!response.ok){


                    throw new Error(

                        'Respuesta inválida del servidor'

                    );


                }









                const data = await response.json();









                /*
                |--------------------------------------------------------------------------
                | Respuesta del bot
                |--------------------------------------------------------------------------
                */


                this.messages.push({



                    from:'bot',




                    text:


                        data.message
                        ??
                        'No recibí respuesta.',





                    quick_actions:


                        data.quick_actions
                        ??
                        [],





                    redirect:


                        data.redirect
                        ??
                        null,






                    items:


                        data.items
                        ??
                        null,








                    intent:


                        data.intent
                        ??
                        null,






                    ai:


                        data.ai
                        ??
                        null



                });









            }catch(error){



                console.error(

                    'Chatbot error:',

                    error

                );






                this.messages.push({


                    from:'bot',


                    text:

                    'No pude procesar tu solicitud en este momento. Intenta nuevamente.',



                    quick_actions:[],


                    redirect:null,


                    items:null,


                    intent:null,


                    ai:null



                });



            }finally{



                this.loading=false;


                this.scrollBottom();



            }




        },









        /*
        |--------------------------------------------------------------------------
        | Ejecutar botones dinámicos
        |--------------------------------------------------------------------------
        */


        executeAction(action,msg){



            if(
                action.action === 'redirect'
            ){



                if(
                    msg.redirect?.url
                ){


                    window.location.href =
                        msg.redirect.url;


                }


                return;


            }








            if(
                action.action === 'send'
            ){



                this.send(

                    action.value

                );


            }



        },









        scrollBottom(){



            this.$nextTick(()=>{



                if(this.$refs.messages){



                    this.$refs.messages.scrollTop =

                        this.$refs.messages.scrollHeight;



                }



            });



        }





    }


}


</script>