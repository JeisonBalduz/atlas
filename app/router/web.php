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
use App\Controller\RegistroEmpleadoController;
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

// Ruta para el módulo de registro de empleado
$router->get('/empleado/registro', [RegistroEmpleadoController::class, 'render'])
    ->addMiddleware(SesionMiddleware::class);

// Rutas de los módulos de gestión con datos de prueba
$router->get('/empleado/registros', [RegistrosEmpleadosController::class, 'render'])
    ->addMiddleware(SesionMiddleware::class);

$router->get('/registros_familiares', [RegistrosFamiliaresController::class, 'render'])
    ->addMiddleware(SesionMiddleware::class);

$router->get('/registrar_familiar', [RegistrarFamiliarController::class, 'render'])
    ->addMiddleware(SesionMiddleware::class);

$router->get('/catalogo', [CatalogoController::class, 'render'])
    ->addMiddleware(SesionMiddleware::class);

$router->get('/registro_vacaciones', [RegistroVacacionesController::class, 'render'])
    ->addMiddleware(SesionMiddleware::class);

$router->get('/registro_ausencia', [RegistroAusenciaController::class, 'render'])
    ->addMiddleware(SesionMiddleware::class);

$router->get('/editar_empleado', [EditarEmpleadoController::class, 'render'])
    ->addMiddleware(SesionMiddleware::class);

$router->get('/editar_familiar', [EditarFamiliarController::class, 'render'])
    ->addMiddleware(SesionMiddleware::class);

$router->get('/registro_beneficios', [RegistroBeneficiosController::class, 'render'])
    ->addMiddleware(SesionMiddleware::class);

//Cerramos la session
$router->get('/cerrar_sesion', [SesionController::class, 'cerrarSesion'])
    ->addMiddleware(SesionMiddleware::class);

// $router->get('/home', [HomeController::class, 'render'])
//     ->addMiddleware([
//         SesionMiddleware::class,
//         PermisosMidleware::permission('beneficios.ver')
//     ]);