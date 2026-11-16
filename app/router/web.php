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
use App\Controller\AuthController;
use App\Middleware\AuthMiddleware;
// Ruta de bienvenida
$router->get('/', [AuthController::class, 'render']);

// Ruta para el módulo Login
$router->get('/Login', [AuthController::class, 'render']);

// Ruta para el módulo Dashboard
$router->get('/home', [HomeController::class, 'render'])->addMiddleware(AuthMiddleware::class);

//Cerramos la session
$router->get('/logout', [AuthController::class, 'logout']);