<?php

declare(strict_types=1);

/**
 * Application web routes (vistas BladeOne y descargas protegidas).
 *
 * @var \flight\net\Router $router
 * @var \flight\Engine<object> $app
 * @var \App\Utils\Config $config
 */

use App\Controller\Api\DocumentosApiController;
use App\Controller\CatalogoController;
use App\Controller\EditarEmpleadoController;
use App\Controller\EditarFamiliarController;
use App\Controller\InicioController;
use App\Controller\RegistrarEmpleadoController;
use App\Controller\RegistrarFamiliarController;
use App\Controller\RegistroAusenciaController;
use App\Controller\RegistroBeneficiosController;
use App\Controller\RegistrosEmpleadosController;
use App\Controller\RegistrosFamiliaresController;
use App\Controller\RegistroVacacionesController;
use App\Controller\SesionController;
use App\Middleware\SesionMiddleware;

// Ruta de bienvenida
$router->get('/', [SesionController::class, 'render']);

// Ruta para el módulo Login
$router->get('/autenticacion', [SesionController::class, 'render']);

// Ruta para el módulo Dashboard
$router->get('/inicio', [InicioController::class, 'render'])
    ->addMiddleware(SesionMiddleware::class);

// Ruta para registrar un empleado
$router->get('/empleado/registrar', [RegistrarEmpleadoController::class, 'render'])
    ->addMiddleware(SesionMiddleware::class);

// Rutas de tabla de empleados registrados
$router->get('/empleado/registros', [RegistrosEmpleadosController::class, 'render'])
    ->addMiddleware(SesionMiddleware::class);

// Rutas para editar un empleado (con ID en ruta o fallback por query)
$router->get('/empleado/editar/@id:[0-9]+', [EditarEmpleadoController::class, 'render'])
    ->addMiddleware(SesionMiddleware::class);

$router->get('/empleado/editar', [EditarEmpleadoController::class, 'render'])
    ->addMiddleware(SesionMiddleware::class);

// Ruta de tabla de familiares registrados
$router->get('/empleado/registros/familiar', [RegistrosFamiliaresController::class, 'render'])
    ->addMiddleware(SesionMiddleware::class);

// Ruta para asignar un familiar a un empleado
$router->get('/empleado/registrar/familiar', [RegistrarFamiliarController::class, 'render'])
    ->addMiddleware(SesionMiddleware::class);

// Rutas para editar un familiar (con ID en ruta o fallback por query)
$router->get('/empleado/familiar/editar/@id:[0-9]+', [EditarFamiliarController::class, 'render'])
    ->addMiddleware(SesionMiddleware::class);

$router->get('/empleado/familiar/editar', [EditarFamiliarController::class, 'render'])
    ->addMiddleware(SesionMiddleware::class);

// Ruta para asignar vacaciones
$router->get('/vacaciones', [RegistroVacacionesController::class, 'render'])
    ->addMiddleware(SesionMiddleware::class);

// Ruta para asignar ausencias justificadas de permisos y reposos
$router->get('/ausencia', [RegistroAusenciaController::class, 'render'])
    ->addMiddleware(SesionMiddleware::class);

// Ruta para asignar un beneficio a un trabajador o familiar
$router->get('/beneficios', [RegistroBeneficiosController::class, 'render'])
    ->addMiddleware(SesionMiddleware::class);

// Ruta de catálogos del sistema
$router->get('/catalogo', [CatalogoController::class, 'render'])
    ->addMiddleware(SesionMiddleware::class);

// Descarga / vista previa segura de documentos en DocumentosInces
$router->get('/documentos/@id:[0-9]+', [DocumentosApiController::class, 'descargar'])
    ->addMiddleware(SesionMiddleware::class);

// Cerramos la sesión
$router->get('/cerrar_sesion', [SesionController::class, 'cerrarSesion'])
    ->addMiddleware(SesionMiddleware::class);
