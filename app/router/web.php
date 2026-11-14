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
use App\Middleware\AuthMiddleware;
use App\Services\AuthSession;
// Ruta de bienvenida
$router->get('/', [LoginController::class, 'render']);

// Ruta para el módulo Login
$router->get('/login', [LoginController::class, 'render']);

// Ruta para el módulo Dashboard
$router->get('/home', [HomeController::class, 'render'])->addMiddleware(AuthMiddleware::class);

$router->get('/logout-test', function() {
    AuthSession::logout();
});