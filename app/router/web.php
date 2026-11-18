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
use App\Controller\HomeController;
use App\Controller\AuthController;

//Middleware
use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMidleware;

use App\Services\AuthContext;
// Ruta de bienvenida
$router->get('/', [AuthController::class, 'render']);

// Ruta para el módulo Login
$router->get('/login', [AuthController::class, 'render']);

// Ruta para el módulo Dashboard
$router->get('/home', [HomeController::class, 'render'])
    ->addMiddleware(AuthMiddleware::class);

//Cerramos la session
$router->get('/logout', [AuthController::class, 'logout'])
    ->addMiddleware(AuthMiddleware::class);

//ruta de pruebas
$router->get('/login-test', function() {
    echo '<pre>';
    print_r(AuthContext::permissions());
    echo '</pre>';
});

// Ruta para la vista de prueba de componentes y validaciones
$router->get('/prueba', function() use ($app) {
    $app->render('empleado_registro');
});


// $router->get('/home', [HomeController::class, 'render'])
//     ->addMiddleware([
//         AuthMiddleware::class,
//         PermissionMidleware::permission('beneficios.ver')
//     ]);