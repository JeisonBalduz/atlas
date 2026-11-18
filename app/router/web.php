<?php

/**
 * Application routes.
 *
 * @var \flight\net\Router $router
 * @var \flight\Engine<object> $app
 * @var \App\Utils\Config $config
 */

use flight\net\Router;

//Controllers
use App\Controller\InicioController;
use App\Controller\SesionController;
use App\Controller\RegistroEmpleadoController;

//Middleware
use App\Middleware\SesionMiddleware;
use App\Middleware\PermisosMidleware;

use App\Services\ContextoSesion;
// Ruta de bienvenida
$router->get('/', [SesionController::class, 'render']);

// Ruta para el módulo Login
$router->get('/autenticacion', [SesionController::class, 'render']);

// Ruta para el módulo Dashboard
$router->get('/inicio', [InicioController::class, 'render'])
    ->addMiddleware(SesionMiddleware::class);

//Cerramos la session
$router->get('/cerrar_sesion', [SesionController::class, 'cerrarSesion'])
    ->addMiddleware(SesionMiddleware::class);

//ruta de pruebas
$router->get('/login-test', function() {
    echo '<pre>';
    print_r(ContextoSesion::permisos());
    echo '</pre>';
});

// Ruta para el módulo de registro de empleado
$router->get('/empleado/registro', [RegistroEmpleadoController::class, 'render'])
 ->addMiddleware(SesionMiddleware::class);

// $router->get('/home', [HomeController::class, 'render'])
//     ->addMiddleware([
//         SesionMiddleware::class,
//         PermisosMidleware::permission('beneficios.ver')
//     ]);