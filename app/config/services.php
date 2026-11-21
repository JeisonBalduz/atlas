<?php

/**
 * Services + Dice DI wiring.
 *
 * Bootstrap (web) provides:
 *   @var \flight\Engine<object> $app
 *   @var \App\Utils\Config      $config
 *   @var string                $projectRoot
 *   @var string                $ds
 *   @var string                $nonce
 *
 * Runway 1.x also requires this file after loading config.php, with $config as
 * the raw array and no $app. Skip full wiring in that CLI context.
 */

use App\Services\CatalogosService;
use App\Services\Documentos\AlmacenArchivoDocumento;
use App\Services\Documentos\ConfiguracionMime;
use App\Services\Documentos\ConstructorRutaDocumento;
use App\Services\Documentos\ExtensionesPeligrosas;
use App\Services\Documentos\ReemplazarDocumento;
use App\Services\Documentos\SubirDocumento;
use App\Services\Documentos\ValidadorDocumento;
use App\Services\GestorDocumentos;
use App\Utils\Config;
use App\Utils\DatabaseFactory;
use Dice\Dice;
use eftec\bladeone\BladeOne;
use flight\database\SimplePdo;
use flight\debug\tracy\TracyExtensionLoader;
use flight\Engine;
use flight\Session;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\ConnectionInterface;
use Tracy\Debugger;

// Runway loads config.php then services.php for config:get/set. No Engine yet.
if (!isset($app) || !($app instanceof Engine) || !($config instanceof Config)) {
    return;
}

// ---------------------------------------------------------------------------
// Tracy
// ---------------------------------------------------------------------------
$logDir = $projectRoot . $ds . 'app' . $ds . 'log';
if (!is_dir($logDir)) {
    mkdir($logDir, 0775, true);
}

if ($config->isDebug()) {
    Debugger::enable(Debugger::Development);
} else {
    Debugger::enable(Debugger::Production);
}
Debugger::$logDirectory = $logDir;
Debugger::$strictMode = true;

if (Debugger::$showBar === true && PHP_SAPI !== 'cli' && class_exists(TracyExtensionLoader::class)) {
    new TracyExtensionLoader($app);
}

// ---------------------------------------------------------------------------
// Database (SimplePdo) — lazy para no abrir una segunda conexión TCP en cada vista
// ---------------------------------------------------------------------------
if (DatabaseFactory::isEnabled($config)) {
    $app->map('db', function () use ($config) {
        static $db = null;
        if ($db === null) {
            $db = DatabaseFactory::create($config);
        }
        return $db;
    });
}

// ---------------------------------------------------------------------------
// BladeOne (MODE_AUTO compila si falta o cambia y reutiliza caché compilada)
// ---------------------------------------------------------------------------
$viewsPath = $projectRoot . $ds . 'resources' . $ds . 'views';
$bladeCache = $projectRoot . $ds . 'app' . $ds . 'cache' . $ds . 'blade';
if (!is_dir($bladeCache)) {
    mkdir($bladeCache, 0775, true);
}

$blade = new BladeOne($viewsPath, $bladeCache, BladeOne::MODE_AUTO);


// Compartir variables globales con todas las vistas de Blade
$blade->share('csp_nonce', $nonce);
$blade->share('app_env', $config->env());
$blade->share('base_url', $config->baseUrl());
$blade->share('vite_activo', is_file($projectRoot . $ds . 'public' . $ds . 'hot'));

// Mapear Flight::render() para que use BladeOne
$app->map('render', function (string $template, array $data = []) use ($app, $blade) {
    $app->response()->write($blade->run($template, $data));
});

// ---------------------------------------------------------------------------
// Session (flightphp/session)
// ---------------------------------------------------------------------------
$sessionOptions = [
    'prefix' => (string) $config->get('session.prefix', 'flight_sess_'),
    'auto_commit' => true,
];
$savePath = $config->get('session.save_path');
if ($savePath !== null && $savePath !== '') {
    $sessionOptions['save_path'] = (string) $savePath;
}
// Defer session start on CLI
if (PHP_SAPI === 'cli') {
    $sessionOptions['start_session'] = false;
}
$session = new Session($sessionOptions);

$app->map('session', function() use ($session) {
    return $session;
});

// ---------------------------------------------------------------------------
// Eloquent ORM Standalone (Capsule Manager)
// ---------------------------------------------------------------------------
$capsule = new Capsule();
$capsule->addConnection([
    'driver'    => 'pgsql', // Forzamos explícitamente pgsql aquí por seguridad
    'host'      => $config->get('database.host', 'localhost'),
    'port'      => $config->get('database.port', '5432'),
    'database'  => $config->get('database.dbname', 'talento_humano'),
    'username'  => $config->get('database.user', 'postgres'),
    'password'  => $config->get('database.password', ''),
    'charset'   => 'utf8',
    'prefix'    => '',
    'schema'    => 'public',
    'options'   => PHP_SAPI !== 'cli' ? [PDO::ATTR_PERSISTENT => true] : [],
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$eloquentConnection = $capsule->getConnection();
$catalogosService = new CatalogosService();

$rutaRelativaDocs = (string) $config->get('storage.documentos_path', 'app/storage/DocumentosInces');
$raizDocumentos = str_starts_with($rutaRelativaDocs, DIRECTORY_SEPARATOR)
    || preg_match('/^[A-Za-z]:[\\\\\\/]/', $rutaRelativaDocs) === 1
    ? $rutaRelativaDocs
    : $projectRoot . $ds . str_replace(['/', '\\'], $ds, $rutaRelativaDocs);

$tamanoMaximoDocs = (int) $config->get('storage.max_file_size_bytes', 5242880);
$configMime = new ConfiguracionMime();
$extensionesPeligrosas = new ExtensionesPeligrosas();
$validadorDocumento = new ValidadorDocumento($configMime, $extensionesPeligrosas, $tamanoMaximoDocs);
$constructorRutaDocumento = new ConstructorRutaDocumento();
$almacenArchivoDocumento = new AlmacenArchivoDocumento($raizDocumentos);
$subirDocumento = new SubirDocumento($eloquentConnection, $validadorDocumento, $constructorRutaDocumento, $almacenArchivoDocumento);
$reemplazarDocumento = new ReemplazarDocumento($eloquentConnection, $validadorDocumento, $constructorRutaDocumento, $almacenArchivoDocumento);

$gestorDocumentos = new GestorDocumentos(
    $eloquentConnection,
    $raizDocumentos,
    $tamanoMaximoDocs,
    $subirDocumento,
    $reemplazarDocumento,
    $almacenArchivoDocumento
);

// ---------------------------------------------------------------------------
// Dice + Engine substitutions (required — see Flight DI docs)
// ---------------------------------------------------------------------------
$container = new Dice();

// Critical: reuse the same Engine instance; do not construct a new one
$substitutions = [
    Engine::class => $app,
    Config::class => $config,
    BladeOne::class => $blade,
    Session::class => $session,
    ConnectionInterface::class => $eloquentConnection,
    CatalogosService::class => $catalogosService,
    GestorDocumentos::class => $gestorDocumentos,
    AlmacenArchivoDocumento::class => $almacenArchivoDocumento,
    SubirDocumento::class => $subirDocumento,
    ReemplazarDocumento::class => $reemplazarDocumento,
];

$container = $container->addRule('*', [
    'substitutions' => $substitutions,
]);

// Shared rules for classes resolved by name (belt + suspenders)
$container = $container->addRule(Config::class, ['shared' => true]);
$container = $container->addRule(BladeOne::class, ['shared' => true]);
$container = $container->addRule(Session::class, ['shared' => true]);
$container = $container->addRule(ConnectionInterface::class, ['shared' => true]);
$container = $container->addRule(CatalogosService::class, ['shared' => true]);
$container = $container->addRule(GestorDocumentos::class, ['shared' => true]);
if (DatabaseFactory::isEnabled($config)) {
    $container = $container->addRule(SimplePdo::class, [
        'shared' => true,
        'constructParams' => [
            'pgsql:host=' . $config->get('database.host', 'localhost') . ';port=' . $config->get('database.port', '5432') . ';dbname=' . $config->get('database.dbname', 'talento_humano'),
            $config->get('database.user'),
            $config->get('database.password'),
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
        ],
    ]);
}

$app->registerContainerHandler(function ($class, $params) use ($container) {
    return $container->create($class, $params);
});

// Helper for non-route code: $app->make(SomeClass::class)
$app->map('make', function ($class, $params = []) use ($container) {
    return $container->create($class, $params);
});