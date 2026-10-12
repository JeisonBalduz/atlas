<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Atlas\Helpers\Router;

// Lempiardor de las URL 
Flight::request()->url = Router::obtenerPathLimpio();

// Define la base de rutas de las vistas
Flight::set('flight.views.path', __DIR__ . '/../app/Views');

// Define de la base de la URL del proyecto
Flight::set('flight.base_url', '/atlas/public');



//Endponits de router para las vistas y las peticiones
require_once __DIR__ . '/../app/Routers/admin.php';
require_once __DIR__ . '/../app/Routers/web.php';
require_once __DIR__ . '/../app/Routers/api.php';

//Inicializador 
Flight::start();