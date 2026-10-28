<?php


/**
 * Application routes.
 *
 * @var \flight\net\Router $router
 * @var \flight\Engine<object> $app
 * @var \App\Utils\Config $config
 */

use App\Controller\HomeController;
use App\Utils\DatabaseFactory;
use flight\net\Router;

// Ruta de bienvenida

$router->get('/', [HomeController::class, 'index']);
