<?php

/**
 * Application routes.
 *
 * @var \flight\net\Router $router
 * @var \flight\Engine<object> $app
 * @var \App\Utils\Config $config
 */

use App\Controller\HomeController;
use App\Controller\PostController;
use App\Middleware\SecurityHeadersMiddleware;
use App\Utils\DatabaseFactory;
use flight\net\Router;

// Grupo global que envuelve todas tus rutas con el middleware de seguridad del esqueleto
$router->group('', function (Router $router) use ($config) {
    
    // 1. Rutas Web generales (vistas BladeOne)
    require_once __DIR__ . '/../router/web.php';

    // 2. Rutas de la API (bajo el prefijo /api)
    Flight::group('/api/', function (Router $router) use ($config) {
        require_once __DIR__ . '/../router/api.php';
    });

    // 3. Rutas del Panel de Administración (bajo el prefijo /admin)
    Flight::group('/admin', function (Router $router) use ($config) {
        require_once __DIR__ . '/../router/admin.php';
    });

}, [SecurityHeadersMiddleware::class]);
