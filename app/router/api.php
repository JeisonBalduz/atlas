<?php

/**
 * Application routes.
 *
 * @var \flight\net\Router $router 
 * @var \flight\Engine<object> $app
 * @var \App\Utils\Config $config
 */

use App\Controller\HomeController;
use App\Controller\LoginController;
use flight\net\Router;

$router->post('/descargar-foto-cedula', function(){
    $cedula = Flight::request()->data['cedula'] ?? $_POST['cedula'] ?? null;
    var_dump($cedula);
});

$router->post('/api/login', [LoginController::class, 'authenticate']);


