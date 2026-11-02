<?php

/**
 * Application routes.
 *
 * @var \flight\net\Router $router
 * @var \flight\Engine<object> $app
 * @var \App\Utils\Config $config
 */

use App\Controller\HomeController;
use flight\net\Router;

$router->get('/', [HomeController::class, 'index']);

$router->post('/descargar-foto-cedula', function(){
    // Puedes leerlo con $_POST['cedula'] o con el helper de Flight
    //$cedula = Flight::request()->data->cedula ?? $_POST['cedula'] ?? null;

    $cedula = Flight::request()->data['cedula'] ?? $_POST['cedula'] ?? null;
    
    var_dump($cedula);
});