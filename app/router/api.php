<?php

declare(strict_types=1);

/**
 * Application API routes (100% Eloquent ORM + DataTables 3.1.5 Server-Side + DocumentosInces).
 *
 * @var \flight\net\Router $router
 * @var \flight\Engine<object> $app
 * @var \App\Utils\Config $config
 */

use App\Controller\Api\CatalogosApiController;
use App\Controller\Api\DocumentosApiController;
use App\Controller\Api\EmpleadosApiController;
use App\Controller\Api\FamiliaresApiController;
use App\Controller\Api\UbicacionApiController;
use App\Controller\SesionController;
use App\Middleware\SesionMiddleware;
use flight\net\Router;

// Autenticación pública
$router->post('/api/autenticacion', [SesionController::class, 'autentecacion']);

// Endpoints protegidos por sesión
$router->group('/api', function (Router $router): void {
    // --- Trabajadores (DataTables 3.1.5 + Familiares modal + Documentos + CRUD) ---
    $router->post('/empleados/datatable', [EmpleadosApiController::class, 'datatable']);
    $router->get('/empleados/datatable', [EmpleadosApiController::class, 'datatable']);
    $router->get('/empleados/@id:[0-9]+/familiares', [EmpleadosApiController::class, 'familiares']);
    $router->get('/empleados/@id:[0-9]+/documentos', [DocumentosApiController::class, 'listarPorEmpleado']);
    $router->get('/empleados/@id:[0-9]+', [EmpleadosApiController::class, 'show']);
    $router->post('/empleados', [EmpleadosApiController::class, 'store']);
    $router->put('/empleados/@id:[0-9]+', [EmpleadosApiController::class, 'update']);
    $router->post('/empleados/@id:[0-9]+', [EmpleadosApiController::class, 'update']);
    $router->delete('/empleados/@id:[0-9]+', [EmpleadosApiController::class, 'destroy']);

    // --- Familiares (DataTables 3.1.5 + CRUD) ---
    $router->post('/familiares/datatable', [FamiliaresApiController::class, 'datatable']);
    $router->get('/familiares/datatable', [FamiliaresApiController::class, 'datatable']);
    $router->get('/familiares/@id:[0-9]+', [FamiliaresApiController::class, 'show']);
    $router->post('/familiares', [FamiliaresApiController::class, 'store']);
    $router->put('/familiares/@id:[0-9]+', [FamiliaresApiController::class, 'update']);
    $router->post('/familiares/@id:[0-9]+', [FamiliaresApiController::class, 'update']);
    $router->delete('/familiares/@id:[0-9]+', [FamiliaresApiController::class, 'destroy']);

    // --- Gestión Documental (DocumentosInces, historial sin borrado) ---
    $router->post('/documentos', [DocumentosApiController::class, 'store']);
    $router->post('/documentos/@id:[0-9]+/reemplazar', [DocumentosApiController::class, 'reemplazar']);

    // --- Selects dependientes de Ubicación ---
    $router->get('/ubicacion/municipios/@estadoId:[0-9]+', [UbicacionApiController::class, 'municipios']);
    $router->get('/ubicacion/parroquias/@municipioId:[0-9]+', [UbicacionApiController::class, 'parroquias']);

    // --- Catálogos ---
    $router->get('/catalogos/@tipo', [CatalogosApiController::class, 'index']);
    $router->post('/catalogos/@tipo', [CatalogosApiController::class, 'store']);
    $router->put('/catalogos/@tipo/@id:[0-9]+', [CatalogosApiController::class, 'update']);
    $router->delete('/catalogos/@tipo/@id:[0-9]+', [CatalogosApiController::class, 'destroy']);
}, [SesionMiddleware::class]);
