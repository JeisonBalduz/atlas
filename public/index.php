<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Atlas\controllers\CapturarErrorController;
use App\Atlas\controllers\RouterController;

CapturarErrorController::init();

Flight::set('flight.views.path', __DIR__ . '/../app/views');

Flight::request()->url = RouterController::obtenerPathLimpio();

Flight::set('flight.base_url', '/atlas/public');

//Endponits de router para las vistas y las peticiones

require_once __DIR__ . '/../app/routers/web.php';
require_once __DIR__ . '/../app/routers/api.php';


//Inicializador 
Flight::start();

