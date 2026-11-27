<?php

declare(strict_types=1);

/**
 * Application API routes apuntando a los controladores generales de `App\Controller`.
 *
 * @var \flight\net\Router $router
 * @var \flight\Engine $app
 * @var \App\Utils\Config $config
 */

use App\Controller\AsistenciaController;
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
use App\Controller\RolesPermisosController;
use App\Controller\SesionController;
use App\Controller\TablasMaestrasController;
use App\Controller\UsuariosController;
use App\Middleware\SesionMiddleware;
use flight\net\Router;

// Autenticación pública
$router->post('/api/autenticacion', [SesionController::class, 'autentecacion']);

// Endpoints protegidos por sesión
$router->group('/api', function (Router $router): void {
    // --- Inicio (métricas reales, usuarios y actividad reciente) ---
    $router->get('/inicio/datos', [InicioController::class, 'datos']);

    // --- Trabajadores ---
    $router->post('/empleados/datatable', [RegistrosEmpleadosController::class, 'datatable']);
    $router->get('/empleados/datatable', [RegistrosEmpleadosController::class, 'datatable']);
    $router->get('/empleados/@id:[0-9]+/familiares', [RegistrosEmpleadosController::class, 'familiares']);
    $router->get('/empleados/@id:[0-9]+/documentos', [RegistrosEmpleadosController::class, 'listarDocumentos']);
    $router->get('/empleados/@id:[0-9]+/recaudos', [EditarEmpleadoController::class, 'recaudos']);
    $router->get('/empleados/@id:[0-9]+', [RegistrosEmpleadosController::class, 'show']);
    $router->post('/empleados', [RegistrarEmpleadoController::class, 'store']);
    $router->put('/empleados/@id:[0-9]+', [EditarEmpleadoController::class, 'update']);
    $router->post('/empleados/@id:[0-9]+', [EditarEmpleadoController::class, 'update']);
    $router->post('/empleados/activar', [RegistrosEmpleadosController::class, 'activar']);
    $router->post('/empleados/@id:[0-9]+/activar', [RegistrosEmpleadosController::class, 'activar']);
    $router->post('/empleados/@id:[0-9]+/discapacidad', [RegistrosEmpleadosController::class, 'asignarDiscapacidad']);
    $router->get('/discapacidad/catalogos', [RegistrosEmpleadosController::class, 'catalogosDiscapacidad']);
    $router->delete('/empleados/@id:[0-9]+', [RegistrosEmpleadosController::class, 'destroy']);

    // --- Familiares ---
    $router->post('/familiares/datatable', [RegistrosFamiliaresController::class, 'datatable']);
    $router->get('/familiares/datatable', [RegistrosFamiliaresController::class, 'datatable']);
    $router->get('/familiares/@id:[0-9]+/recaudos', [EditarFamiliarController::class, 'recaudos']);
    $router->post('/familiares/@id:[0-9]+/discapacidad', [RegistrosFamiliaresController::class, 'asignarDiscapacidad']);
    $router->get('/familiares/@id:[0-9]+', [RegistrosFamiliaresController::class, 'show']);
    $router->post('/familiares', [RegistrarFamiliarController::class, 'store']);
    $router->put('/familiares/@id:[0-9]+', [EditarFamiliarController::class, 'update']);
    $router->post('/familiares/@id:[0-9]+', [EditarFamiliarController::class, 'update']);
    $router->delete('/familiares/@id:[0-9]+', [RegistrosFamiliaresController::class, 'destroy']);

    // --- Ausencias Justificadas (Permisos y Reposos Médicos) ---
    $router->get('/ausencias', [RegistroAusenciaController::class, 'listar']);
    $router->get('/ausencias/@id:[0-9]+', [RegistroAusenciaController::class, 'show']);
    $router->post('/ausencias', [RegistroAusenciaController::class, 'store']);
    $router->put('/ausencias/@id:[0-9]+', [RegistroAusenciaController::class, 'update']);
    $router->post('/ausencias/@id:[0-9]+/reanudar', [RegistroAusenciaController::class, 'reanudar']);
    $router->post('/ausencias/reposos/@id:[0-9]+/validar', [RegistroAusenciaController::class, 'validarReposo']);

    // --- Vacaciones ---
    $router->get('/vacaciones', [RegistroVacacionesController::class, 'listar']);
    $router->get('/vacaciones/feriados', [RegistroVacacionesController::class, 'feriados']);
    $router->get('/vacaciones/saldo/@empleadoId:[0-9]+', [RegistroVacacionesController::class, 'saldo']);
    $router->get('/vacaciones/@id:[0-9]+', [RegistroVacacionesController::class, 'show']);
    $router->post('/vacaciones', [RegistroVacacionesController::class, 'store']);
    $router->put('/vacaciones/@id:[0-9]+', [RegistroVacacionesController::class, 'update']);
    $router->post('/vacaciones/@id:[0-9]+/estatus', [RegistroVacacionesController::class, 'cambiarEstatus']);

    // --- Asistencia Diaria (Entrada / Salida y bloqueo por reposo) ---
    $router->get('/asistencia', [AsistenciaController::class, 'listar']);
    $router->get('/asistencia/empleado/@empleadoId:[0-9]+', [AsistenciaController::class, 'estadoEmpleado']);
    $router->post('/asistencia/entrada', [AsistenciaController::class, 'entrada']);
    $router->post('/asistencia/salida', [AsistenciaController::class, 'salida']);

    // --- Beneficios Socioeconómicos ---
    $router->get('/beneficios', [RegistroBeneficiosController::class, 'listar']);
    $router->get('/beneficios/estado-documental', [RegistroBeneficiosController::class, 'estadoDocumental']);
    $router->post('/beneficios', [RegistroBeneficiosController::class, 'store']);
    $router->post('/beneficios/carga-masiva', [RegistroBeneficiosController::class, 'cargaMasiva']);
    $router->put('/beneficios/@id:[0-9]+', [RegistroBeneficiosController::class, 'update']);
    $router->post('/beneficios/@id:[0-9]+', [RegistroBeneficiosController::class, 'update']);
    $router->post('/beneficios/@id:[0-9]+/aprobar', [RegistroBeneficiosController::class, 'aprobar']);

    // --- Seguridad: Usuarios, Roles y Permisos ---
    $router->get('/usuarios', [UsuariosController::class, 'listar']);
    $router->post('/usuarios', [UsuariosController::class, 'store']);
    $router->put('/usuarios/@id:[0-9]+', [UsuariosController::class, 'update']);
    $router->post('/usuarios/@id:[0-9]+/estatus', [UsuariosController::class, 'cambiarEstatus']);
    $router->get('/usuarios/@id:[0-9]+/permisos', [UsuariosController::class, 'permisos']);
    $router->put('/usuarios/@id:[0-9]+/permisos', [UsuariosController::class, 'actualizarPermisos']);

    $router->get('/roles', [RolesPermisosController::class, 'listar']);
    $router->post('/roles', [RolesPermisosController::class, 'store']);
    $router->put('/roles/@id:[0-9]+', [RolesPermisosController::class, 'update']);
    $router->put('/roles/@id:[0-9]+/permisos', [RolesPermisosController::class, 'actualizarPermisos']);

    // --- Gestión Documental (DocumentosInces, vigencia y reemplazo) ---
    $router->post('/documentos', [RegistrosEmpleadosController::class, 'subirDocumento']);
    $router->post('/documentos/@id:[0-9]+/reemplazar', [RegistrosEmpleadosController::class, 'reemplazarDocumento']);

    // --- Selects dependientes de Ubicación ---
    $router->get('/ubicacion/municipios/@estadoId:[0-9]+', [RegistrarEmpleadoController::class, 'municipios']);
    $router->get('/ubicacion/parroquias/@municipioId:[0-9]+', [RegistrarEmpleadoController::class, 'parroquias']);

    // --- Selects remotos bajo demanda ---
    $router->get('/select/empleados', [RegistrosEmpleadosController::class, 'buscarParaSelect']);
    $router->get('/select/personas', [RegistroBeneficiosController::class, 'buscarPersonasParaSelect']);

    // --- Catálogos / Tablas Maestras ---
    $router->get('/catalogos/@tipo', [TablasMaestrasController::class, 'index']);
    $router->post('/catalogos/@tipo', [TablasMaestrasController::class, 'store']);
    $router->put('/catalogos/@tipo/@id:[0-9]+', [TablasMaestrasController::class, 'update']);
    $router->post('/catalogos/@tipo/@id:[0-9]+/activar', [TablasMaestrasController::class, 'activar']);
    $router->delete('/catalogos/@tipo/@id:[0-9]+', [TablasMaestrasController::class, 'destroy']);
}, [SesionMiddleware::class]);
