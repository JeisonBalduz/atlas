<?php

declare(strict_types=1);

/**
 * Application API routes apuntando a los controladores generales de `App\Controller`.
 *
 * @var \flight\net\Router $router
 * @var \flight\Engine<object> $app
 * @var \App\Utils\Config $config
 */

use App\Controller\CatalogoController;
use App\Controller\EditarEmpleadoController;
use App\Controller\EditarFamiliarController;
use App\Controller\RegistrarEmpleadoController;
use App\Controller\RegistrarFamiliarController;
use App\Controller\RegistrosEmpleadosController;
use App\Controller\RegistrosFamiliaresController;
use App\Controller\SesionController;
use App\Middleware\SesionMiddleware;
use flight\net\Router;

// Autenticación pública
$router->post('/api/autenticacion', [SesionController::class, 'autentecacion']);

// Endpoints protegidos por sesión
$router->group('/api', function (Router $router): void {
    // --- Trabajadores ---
    $router->post('/empleados/datatable', [RegistrosEmpleadosController::class, 'datatable']);
    $router->get('/empleados/datatable', [RegistrosEmpleadosController::class, 'datatable']);
    $router->get('/empleados/@id:[0-9]+/familiares', [RegistrosEmpleadosController::class, 'familiares']);
    $router->get('/empleados/@id:[0-9]+/documentos', [RegistrosEmpleadosController::class, 'listarDocumentos']);
    $router->get('/empleados/@id:[0-9]+', [RegistrosEmpleadosController::class, 'show']);
    $router->post('/empleados', [RegistrarEmpleadoController::class, 'store']);
    $router->put('/empleados/@id:[0-9]+', [EditarEmpleadoController::class, 'update']);
    $router->post('/empleados/@id:[0-9]+', [EditarEmpleadoController::class, 'update']);
    $router->delete('/empleados/@id:[0-9]+', [RegistrosEmpleadosController::class, 'destroy']);

    // --- Familiares ---
    $router->post('/familiares/datatable', [RegistrosFamiliaresController::class, 'datatable']);
    $router->get('/familiares/datatable', [RegistrosFamiliaresController::class, 'datatable']);
    $router->get('/familiares/@id:[0-9]+', [RegistrosFamiliaresController::class, 'show']);
    $router->post('/familiares', [RegistrarFamiliarController::class, 'store']);
    $router->put('/familiares/@id:[0-9]+', [EditarFamiliarController::class, 'update']);
    $router->post('/familiares/@id:[0-9]+', [EditarFamiliarController::class, 'update']);
    $router->delete('/familiares/@id:[0-9]+', [RegistrosFamiliaresController::class, 'destroy']);

    // --- Gestión Documental (DocumentosInces, historial sin borrado) ---
    $router->post('/documentos', [RegistrosEmpleadosController::class, 'subirDocumento']);
    $router->post('/documentos/@id:[0-9]+/reemplazar', [RegistrosEmpleadosController::class, 'reemplazarDocumento']);

    // --- Selects dependientes de Ubicación ---
    $router->get('/ubicacion/municipios/@estadoId:[0-9]+', [RegistrarEmpleadoController::class, 'municipios']);
    $router->get('/ubicacion/parroquias/@municipioId:[0-9]+', [RegistrarEmpleadoController::class, 'parroquias']);

    // --- Catálogos ---
    $router->get('/catalogos/@tipo', [CatalogoController::class, 'index']);
    $router->post('/catalogos/@tipo', [CatalogoController::class, 'store']);
    $router->put('/catalogos/@tipo/@id:[0-9]+', [CatalogoController::class, 'update']);
    $router->delete('/catalogos/@tipo/@id:[0-9]+', [CatalogoController::class, 'destroy']);
}, [SesionMiddleware::class]);
