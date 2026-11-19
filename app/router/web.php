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
use App\Controller\InicioController;
use App\Controller\SesionController;
use App\Controller\RegistroEmpleadoController;
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

//Cerramos la session
$router->get('/cerrar_sesion', [SesionController::class, 'cerrarSesion'])
    ->addMiddleware(SesionMiddleware::class);

//ruta de pruebas
$router->get('/login-test', function () {
    echo '<pre>';
    print_r(ContextoSesion::permisos());
    echo '</pre>';
});

// Ruta de prueba Eloquent: consultar con relaciones, guardar y actualizar en PersonasModel
$router->get('/personas', function () {
    echo '<pre>';

    // 1. CONSULTAR DATOS DE CADA PERSONA (con sus relaciones cargadas)
    $personas = PersonasModel::with([
        'estadoCivil',
        'empleado.cargo',
        'telefonos',
        'familiares',
    ])->limit(10)->get();

    echo "=== 1. LISTADO DE PERSONAS Y SUS RELACIONES ===\n";
    foreach ($personas as $persona) {
        echo "ID: {$persona->persona_id} | Nombre: {$persona->primer_nombre} {$persona->primer_apellido} | Cédula: {$persona->cedula}\n";
        echo 'Estado civil: ' . ($persona->estadoCivil !== null ? $persona->estadoCivil->estado_civil : 'Sin estado civil') . "\n";
        echo 'Cargo (si es empleado): ' . ($persona->empleado?->cargo?->cargo ?? 'No es empleado') . "\n";
        echo 'Teléfonos (' . $persona->telefonos->count() . '): ';
        print_r($persona->telefonos->pluck('numero_telefono')->toArray());
        echo "----------------------------------------\n";
    }

    // 2. GUARDAR UN NUEVO REGISTRO (INSERT)
    $estadoCivilId = EstadoCivilModel::query()->value('estado_civil_id');
    $nuevaPersona = PersonasModel::create([
        'primer_nombre' => 'Prueba',
        'segundo_nombre' => 'Eloquent',
        'primer_apellido' => 'Atlas',
        'segundo_apellido' => 'Test',
        'genero' => 'M',
        'cedula' => random_int(80000000, 99999999),
        'nacionalidad' => 'V',
        'fecha_nacimiento' => '1995-05-15',
        'estado_civil_id' => $estadoCivilId,
    ]);

    echo "\n=== 2. REGISTRO GUARDADO (CREATE) ===\n";
    print_r($nuevaPersona->toArray());

    // 3. ACTUALIZAR UN REGISTRO (UPDATE)
    $personaActualizar = PersonasModel::find($nuevaPersona->persona_id);
    if ($personaActualizar !== null) {
        $personaActualizar->update([
            'primer_nombre' => 'PruebaActualizada',
            'segundo_nombre' => 'Editado',
        ]);

        echo "\n=== 3. REGISTRO ACTUALIZADO (UPDATE) ===\n";
        print_r($personaActualizar->fresh()?->toArray());
    }

    echo '</pre>';
    exit;
});

// Ruta para el módulo de registro de empleado
$router->get('/empleado/registro', [RegistroEmpleadoController::class, 'render'])
    ->addMiddleware(SesionMiddleware::class);

// $router->get('/home', [HomeController::class, 'render'])
//     ->addMiddleware([
//         SesionMiddleware::class,
//         PermisosMidleware::permission('beneficios.ver')
//     ]);