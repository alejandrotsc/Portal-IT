<?php

use App\Providers\AppServiceProvider;
use App\Providers\ChatbotServiceProvider;

/*
|--------------------------------------------------------------------------
| Proveedores de servicios de la aplicación
|--------------------------------------------------------------------------
|
| Define los Service Providers que Laravel debe registrar durante el
| proceso de inicialización de la aplicación.
|
| AppServiceProvider contiene configuraciones generales del proyecto,
| mientras que ChatbotServiceProvider registra las dependencias y
| componentes relacionados con el módulo del chatbot.
|
*/

return [
    AppServiceProvider::class,
    ChatbotServiceProvider::class,
];