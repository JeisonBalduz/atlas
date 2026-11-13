<?php

/**
 * Application routes.
 *
 * @var \flight\net\Router $router
 * @var \flight\Engine<object> $app
 * @var \App\Utils\Config $config
 */

use flight\net\Router;
use App\Controller\HomeController;
use App\Controller\LoginController;
use App\Controller\PruebaController;
use App\Services\AuthSession;

// Ruta de bienvenida
$router->get('/', [LoginController::class, 'render']);

// Ruta para el módulo Login
$router->get('/login', [LoginController::class, 'render']);

$router->get('/home', [HomeController::class, 'render']);



$router->get('/test-login-session', function () {
    // Simulamos los datos de un usuario autenticado
    $usuarioPrueba = [
        'usuario_id' => 1,
        'nombre_usuario' => 'admin_test'
    ];

    // Llamamos a tu clase AuthSession para iniciar sesión
    AuthSession::login($usuarioPrueba);

    Flight::json([
        'status' => 'success',
        'mensaje' => '¡Sesión iniciada correctamente!',
        'esta_logueado' => AuthSession::check(),
        'usuario_id' => AuthSession::get('user_id'), // <--- Cambiado a 'user_id'
        'nombre' => AuthSession::get('nombre_usuario')
    ]);
});
