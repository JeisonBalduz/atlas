<?php

/**
 * Application routes.
 *
 * @var \flight\net\Router $router
 * @var \flight\Engine<object> $app
 * @var \App\Utils\Config $config
 */

use flight\net\Router;

//Controllers
use App\Controller\CatalogoController;
use App\Controller\EditarEmpleadoController;
use App\Controller\EditarFamiliarController;
use App\Controller\InicioController;
use App\Controller\RegistrarFamiliarController;
use App\Controller\RegistroAusenciaController;
use App\Controller\RegistroBeneficiosController;
use App\Controller\RegistrarEmpleadoController;
use App\Controller\RegistroVacacionesController;
use App\Controller\RegistrosEmpleadosController;
use App\Controller\RegistrosFamiliaresController;
use App\Controller\SesionController;
use App\Model\Personas\EmpleadosModel;
use App\Model\Catalogos\EstadoCivilModel;
use App\Model\Personas\PersonasModel;


//Middleware
use App\Middleware\SesionMiddleware;
use App\Middleware\PermisosMidleware;

use App\Services\ContextoSesion;
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

// Ruta para editar un empleado
$router->get('/empleado/editar', [EditarEmpleadoController::class, 'render'])
    ->addMiddleware(SesionMiddleware::class);

// Ruta de tabla de familaires registrados
$router->get('/empleado/registros/familiar', [RegistrosFamiliaresController::class, 'render'])
    ->addMiddleware(SesionMiddleware::class);

// Ruta para asignar un familiar a un empleado
$router->get('/empleado/registrar/familiar', [RegistrarFamiliarController::class, 'render'])
    ->addMiddleware(SesionMiddleware::class);

// Ruta para editar un familiar
$router->get('/empleado/familiar/editar', [EditarFamiliarController::class, 'render'])
    ->addMiddleware(SesionMiddleware::class);
// Ruta para asignar vacaciones
$router->get('/vacaciones', [RegistroVacacionesController::class, 'render'])
    ->addMiddleware(SesionMiddleware::class);

// Ruta para asignar ausencias jsutificadas de permisos y reposos
$router->get('/ausencia', [RegistroAusenciaController::class, 'render'])
    ->addMiddleware(SesionMiddleware::class);

// Ruta para asignar un beneficio a un trabajadort o familiar
$router->get('/beneficios', [RegistroBeneficiosController::class, 'render'])
    ->addMiddleware(SesionMiddleware::class);

// Ruta de catalogos de dependencia, departamento, cargo, estatus, nivel academico y carrera
$router->get('/catalogo', [CatalogoController::class, 'render'])
    ->addMiddleware(SesionMiddleware::class);

//Cerramos la session
$router->get('/cerrar_sesion', [SesionController::class, 'cerrarSesion'])
    ->addMiddleware(SesionMiddleware::class);

// $router->get('/home', [HomeController::class, 'render'])
//     ->addMiddleware([
//         SesionMiddleware::class,
//         PermisosMidleware::permission('beneficios.ver')
//     ]);