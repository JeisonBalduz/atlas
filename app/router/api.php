<?php

/**
 * Application routes.
 *
 * @var \flight\net\Router $router 
 * @var \flight\Engine<object> $app
 * @var \App\Utils\Config $config
 */

use App\Controller\InicioController;
use App\Controller\SesionController;
use flight\net\Router;

$router->post('/descargar-foto-cedula', function() use ($app) {
    $cedula = $app->request()->data['cedula'] ?? null;
    var_dump($cedula);
});

$router->post('/api/autenticacion', [SesionController::class, 'autentecacion']);


