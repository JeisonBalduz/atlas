<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\config\ClavesSesion;
use App\Controller\CatalogoController;
use App\Controller\EditarEmpleadoController;
use App\Controller\EditarFamiliarController;
use App\Controller\RegistrarEmpleadoController;
use App\Controller\RegistrarFamiliarController;
use App\Controller\RegistrosEmpleadosController;
use App\Controller\RegistrosFamiliaresController;
use App\Middleware\SesionMiddleware;
use App\Model\Beneficios\TipoDiscapacidadModel;
use App\Model\Catalogos\CargoModel;
use App\Model\Catalogos\CarreraModel;
use App\Model\Catalogos\DepartamentosModel;
use App\Model\Catalogos\DependenciasModel;
use App\Model\Catalogos\EstadoCivilModel;
use App\Model\Catalogos\EstatusModel;
use App\Model\Catalogos\NivelAcademicosModel;
use App\Model\Catalogos\TipoDocumentosModel;
use App\Model\Catalogos\TipoParentescoModel;
use App\Model\Personas\EmpleadosModel;
use App\Model\Personas\FamiliaresModel;
use App\Model\Personas\PersonasModel;
use App\Model\Ubicacion\EstadosModel;
use App\Model\Ubicacion\MunicipiosModel;
use App\Model\Ubicacion\ParroquiasModel;
use App\Services\CatalogosService;
use App\Services\GestorDocumentos;
use App\Utils\Config;
use flight\Engine;
use flight\net\Request;
use flight\Session;
use flight\util\Collection;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class ApiEloquentEndpointsTest extends TestCase
{
    private Capsule $capsule;
    private string $tempDir;
    private GestorDocumentos $gestor;
    private Config $config;
    private CatalogosService $catalogos;

    /** @var array<string, mixed>|null */
    private ?array $ultimoJson = null;
    private int $ultimoCodigo = 0;
    private ?string $ultimaRedireccion = null;
    private ?string $ultimaVista = null;
    /** @var array<string, mixed> */
    private array $ultimosDatosVista = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->capsule = new Capsule();
        $this->capsule->addConnection([
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);
        $this->capsule->setAsGlobal();
        $this->capsule->bootEloquent();

        $this->crearEsquema();
        $this->poblarDatos();

        $this->tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'atlas_api_test_' . bin2hex(random_bytes(5));
        mkdir($this->tempDir, 0775, true);

        $this->gestor = new GestorDocumentos(
            $this->capsule->getConnection(),
            $this->tempDir . DIRECTORY_SEPARATOR . 'DocumentosInces',
            5242880
        );
        $this->config = new Config(['app' => ['env' => 'testing', 'debug' => false]]);
        $this->catalogos = new CatalogosService();
    }

    protected function tearDown(): void
    {
        $_POST = [];
        $this->eliminarDirectorio($this->tempDir);
        parent::tearDown();
    }

    public function testEmpleadosDatatableSoportaPaginacionBusquedaInteligenteYListaBlanca(): void
    {
        $app = $this->crearMockEngine('POST', [
            'draw' => 2,
            'start' => 0,
            'length' => 10,
            'order' => [['column' => 2, 'dir' => 'asc']],
            'columns' => [2 => ['data' => 'nombre_completo']],
        ]);

        $ctrl = new RegistrosEmpleadosController($app, $this->config, $this->catalogos, $this->gestor);
        $ctrl->datatable();

        self::assertSame(200, $this->ultimoCodigo);
        self::assertSame(2, $this->ultimoJson['draw']);
        self::assertSame(2, $this->ultimoJson['recordsTotal']);
        self::assertSame(2, $this->ultimoJson['recordsFiltered']);
        self::assertCount(2, $this->ultimoJson['data']);
        self::assertSame(['total' => 2, 'activos' => 2, 'inactivos' => 0], $this->ultimoJson['resumen']);
        self::assertSame('EMP-002', $this->ultimoJson['data'][0]['codigo_empleado']);
        self::assertSame(2, $this->ultimoJson['data'][1]['total_familiares']);

        $appShow = $this->crearMockEngine('GET');
        (new RegistrosEmpleadosController($appShow, $this->config, $this->catalogos, $this->gestor))->show('1');
        self::assertSame(200, $this->ultimoCodigo);
        self::assertSame('Carlos Eduardo Mendoza Ríos', $this->ultimoJson['data']['nombre_completo']);
        self::assertSame('Analista', $this->ultimoJson['data']['cargo']);
        self::assertSame('Talento Humano', $this->ultimoJson['data']['departamento']);

        $appBusqueda = $this->crearMockEngine('POST', [
            'draw' => 3,
            'start' => 0,
            'length' => 10,
            'search' => ['value' => 'carlos romana'],
        ]);

        (new RegistrosEmpleadosController($appBusqueda, $this->config, $this->catalogos, $this->gestor))->datatable();

        self::assertSame(200, $this->ultimoCodigo);
        self::assertSame(2, $this->ultimoJson['recordsTotal']);
        self::assertSame(1, $this->ultimoJson['recordsFiltered']);
        self::assertSame('V-21456789', $this->ultimoJson['data'][0]['cedula']);
    }

    public function testEmpleadosFamiliaresEndpointParaModalYNoEncontrado(): void
    {
        $app = $this->crearMockEngine('GET');
        $ctrl = new RegistrosEmpleadosController($app, $this->config, $this->catalogos, $this->gestor);

        $ctrl->familiares('1');

        self::assertSame(200, $this->ultimoCodigo);
        self::assertTrue($this->ultimoJson['ok']);
        self::assertSame(2, $this->ultimoJson['total']);
        self::assertSame('V-21456789', $this->ultimoJson['empleado']['cedula']);
        self::assertIsInt($this->ultimoJson['data'][0]['edad']);
        self::assertSame('Hija', $this->ultimoJson['data'][0]['parentesco']);

        $ctrl->familiares('999');
        self::assertSame(404, $this->ultimoCodigo);
        self::assertFalse($this->ultimoJson['ok']);
    }

    public function testEmpleadosCrudStoreUpdateYDestroyLogico(): void
    {
        $appStore = $this->crearMockEngine('POST', [
            'primer_nombre' => 'Luis',
            'segundo_nombre' => 'Alberto',
            'primer_apellido' => 'Gómez',
            'segundo_apellido' => 'Pérez',
            'nacionalidad' => 'V',
            'cedula' => '25999888',
            'genero' => 'Masculino',
            'fecha_nacimiento' => '1993-04-12',
            'estado_civil_id' => 1,
            'dependencia_id' => 1,
            'departamento_id' => 1,
            'cargo_id' => 1,
            'estatus_id' => 1,
            'nivel_academico_id' => 1,
            'parroquia_id' => 1,
            'vivienda' => 'Casa',
            'calle' => 'Av. Bolívar',
            'nombre_urb' => 'Centro',
            'telefono_principal' => '04149998877',
            'correo' => 'luis.gomez@inces.gob.ve',
        ]);

        (new RegistrarEmpleadoController($appStore, $this->config, $this->catalogos, $this->gestor))->store();

        self::assertSame(201, $this->ultimoCodigo);
        $nuevoId = (int) $this->ultimoJson['data']['empleado_id'];
        self::assertGreaterThan(2, $nuevoId);

        $creado = EmpleadosModel::query()->with(['persona.telefonos', 'direccion'])->find($nuevoId);
        self::assertInstanceOf(EmpleadosModel::class, $creado);
        self::assertSame(1, (int) $creado->estatus_actual);
        self::assertSame('V', $creado->persona?->nacionalidad);
        self::assertSame(25999888, (int) $creado->persona?->cedula);
        self::assertLessThanOrEqual(10, strlen((string) $creado->codigo_empleado));
        self::assertSame('Av. Bolívar', $creado->direccion?->calle);

        $appUpdate = $this->crearMockEngine('PUT', [
            'correo' => 'luis.actualizado@inces.gob.ve',
            'telefono_emergencia' => '04241112233',
        ]);
        (new EditarEmpleadoController($appUpdate, $this->config, $this->catalogos, $this->gestor))->update((string) $nuevoId);
        self::assertSame(200, $this->ultimoCodigo);

        $actualizado = EmpleadosModel::query()->with('persona.telefonos')->find($nuevoId);
        self::assertSame('luis.actualizado@inces.gob.ve', $actualizado?->correo);
        self::assertCount(2, $actualizado?->persona?->telefonos ?? []);

        $appDestroy = $this->crearMockEngine('DELETE');
        (new RegistrosEmpleadosController($appDestroy, $this->config, $this->catalogos, $this->gestor))->destroy((string) $nuevoId);
        self::assertSame(200, $this->ultimoCodigo);
        self::assertSame(0, (int) EmpleadosModel::query()->find($nuevoId)?->estatus_actual);
    }

    public function testFamiliaresDatatableCrudYConservacionDePersona(): void
    {
        $appDt = $this->crearMockEngine('POST', [
            'draw' => 1,
            'start' => 0,
            'length' => 10,
            'parentesco_id' => 4,
        ]);

        (new RegistrosFamiliaresController($appDt, $this->config, $this->catalogos))->datatable();

        self::assertSame(200, $this->ultimoCodigo);
        self::assertSame(2, $this->ultimoJson['recordsTotal']);
        self::assertSame(1, $this->ultimoJson['recordsFiltered']);
        self::assertSame('Hija', $this->ultimoJson['data'][0]['parentesco']);

        $appStore = $this->crearMockEngine('POST', [
            'empleado_id' => 2,
            'parentesco_id' => 1,
            'estado_civil_id' => 2,
            'nacionalidad' => 'V',
            'cedula' => '8456123',
            'primer_nombre' => 'Carmen',
            'segundo_nombre' => 'Rosa',
            'primer_apellido' => 'Silva',
            'segundo_apellido' => 'de López',
            'fecha_nacimiento' => '1962-07-08',
            'genero' => 'Femenino',
            'tomo' => 3,
            'folio' => 88,
        ]);

        (new RegistrarFamiliarController($appStore, $this->config, $this->catalogos, $this->gestor))->store();
        self::assertSame(201, $this->ultimoCodigo);
        $famId = (int) $this->ultimoJson['data']['familia_id'];
        $persId = (int) $this->ultimoJson['data']['persona_id'];

        $personaCreada = PersonasModel::query()->find($persId);
        self::assertSame('V', $personaCreada?->nacionalidad);
        self::assertSame(8456123, (int) $personaCreada?->cedula);

        $appUpd = $this->crearMockEngine('PUT', ['folio' => 99]);
        (new EditarFamiliarController($appUpd, $this->config, $this->catalogos, $this->gestor))->update((string) $famId);
        self::assertSame(200, $this->ultimoCodigo);
        self::assertSame(99, (int) FamiliaresModel::query()->find($famId)?->folio);

        $appDel = $this->crearMockEngine('DELETE');
        (new RegistrosFamiliaresController($appDel, $this->config, $this->catalogos))->destroy((string) $famId);
        self::assertSame(200, $this->ultimoCodigo);
        self::assertNull(FamiliaresModel::query()->find($famId));
        self::assertInstanceOf(PersonasModel::class, PersonasModel::query()->find($persId));
    }

    public function testDocumentosApiListarPorEmpleadoOrdenaHistorialMasViejoAMasNuevo(): void
    {
        $pdfPath1 = $this->tempDir . DIRECTORY_SEPARATOR . 'cedula_v1.pdf';
        $pdfPath2 = $this->tempDir . DIRECTORY_SEPARATOR . 'cedula_v2.pdf';
        file_put_contents($pdfPath1, "%PDF-1.4\n1 0 obj\n<<>>\nendobj\n%%EOF\n");
        file_put_contents($pdfPath2, "%PDF-1.4\n2 0 obj\n<<>>\nendobj\n%%EOF\n");

        $doc1 = $this->gestor->subir([
            'name' => 'cedula_v1.pdf',
            'tmp_name' => $pdfPath1,
            'size' => (int) filesize($pdfPath1),
            'error' => UPLOAD_ERR_OK,
        ], 1, 1, null, false);

        $doc2 = $this->gestor->reemplazar((int) $doc1->documentos_id, [
            'name' => 'cedula_v2.pdf',
            'tmp_name' => $pdfPath2,
            'size' => (int) filesize($pdfPath2),
            'error' => UPLOAD_ERR_OK,
        ], 1, 1, null, false);

        $app = $this->crearMockEngine('GET');
        $ctrl = new RegistrosEmpleadosController($app, $this->config, $this->catalogos, $this->gestor);
        $ctrl->listarDocumentos('1');

        self::assertSame(200, $this->ultimoCodigo);
        self::assertCount(2, $this->ultimoJson['personales']);
        self::assertSame('cedula_v1.pdf', $this->ultimoJson['personales'][0]['nombre_original']);
        self::assertSame('Histórico', $this->ultimoJson['personales'][0]['estatus_documento']);
        self::assertSame('cedula_v2.pdf', $this->ultimoJson['personales'][1]['nombre_original']);
        self::assertSame('Vigente', $this->ultimoJson['personales'][1]['estatus_documento']);
        self::assertSame('/documentos/' . $doc2->documentos_id, $this->ultimoJson['personales'][1]['url_descarga']);
        self::assertFileExists($this->gestor->rutaAbsoluta($doc1));
        self::assertFileExists($this->gestor->rutaAbsoluta($doc2));
    }

    public function testCatalogosServiceReutilizaEstatusModelYFiltraUbicacion(): void
    {
        $svc = new CatalogosService();

        self::assertSame(EstatusModel::todoDatosEstatus(), $svc->estatus(false));
        self::assertArrayNotHasKey(2, $svc->estatus(true));
        self::assertArrayHasKey(1, $svc->estatus(true));

        $niveles = array_values($svc->nivelesAcademicos());
        self::assertSame(['Bachiller', 'Universitario'], $niveles);

        self::assertSame([1 => 'Girardot'], $svc->municipiosPorEstado(1));
        self::assertSame([1 => 'Madre María'], $svc->parroquiasPorMunicipio(1));
        self::assertCount(2, $svc->empleadosActivosParaSelect());
    }

    public function testSesionMiddlewareDevuelve401JsonParaApiSinSesion(): void
    {
        $session = $this->createMock(Session::class);
        $session->method('get')->with(ClavesSesion::ESTA_AUTENTICADO)->willReturn(false);

        $appApi = $this->crearMockEngine('POST', [], [], '/api/empleados/datatable', true);
        $mwApi = new SesionMiddleware($appApi, $session);
        $mwApi->before([]);

        self::assertSame(401, $this->ultimoCodigo);
        self::assertFalse($this->ultimoJson['ok']);

        $appWeb = $this->crearMockEngine('GET', [], [], '/empleado/registros', false);
        $mwWeb = new SesionMiddleware($appWeb, $session);
        $mwWeb->before([]);

        self::assertSame('/autenticacion', $this->ultimaRedireccion);
    }

    public function testUbicacionYCatalogosUnificadosEnControladoresGenerales(): void
    {
        $appMun = $this->crearMockEngine('GET');
        (new RegistrarEmpleadoController($appMun, $this->config, $this->catalogos, $this->gestor))->municipios('1');
        self::assertSame(200, $this->ultimoCodigo);
        self::assertSame([1 => 'Girardot'], $this->ultimoJson['data']);

        $appPar = $this->crearMockEngine('GET');
        (new RegistrarEmpleadoController($appPar, $this->config, $this->catalogos, $this->gestor))->parroquias('1');
        self::assertSame(200, $this->ultimoCodigo);
        self::assertSame([1 => 'Madre María'], $this->ultimoJson['data']);

        $appCatList = $this->crearMockEngine('GET');
        (new CatalogoController($appCatList, $this->config, $this->catalogos))->index('tipo_documentos');
        self::assertSame(200, $this->ultimoCodigo);
        self::assertCount(1, $this->ultimoJson['data']);

        $appCatStore = $this->crearMockEngine('POST', ['cargo' => 'Coordinador Técnico']);
        (new CatalogoController($appCatStore, $this->config, $this->catalogos))->store('cargo');
        self::assertSame(201, $this->ultimoCodigo);
        $nuevoCargoId = (string) $this->ultimoJson['data']['cargo_id'];

        $appCatDel = $this->crearMockEngine('DELETE');
        (new CatalogoController($appCatDel, $this->config, $this->catalogos))->destroy('cargo', $nuevoCargoId);
        self::assertSame(200, $this->ultimoCodigo);
        self::assertFalse((bool) CargoModel::query()->find((int) $nuevoCargoId)?->activo);
    }

    public function testControladoresDeVistasHidratanSelectsYPreseleccionDesdeEloquent(): void
    {
        $session = $this->createMock(Session::class);
        $session->method('get')->willReturnCallback(
            static fn (string $clave, mixed $defecto = null): mixed => $defecto
        );
        \Flight::map('session', static fn () => $session);

        $appRegEmp = $this->crearMockEngine('GET');
        (new RegistrarEmpleadoController($appRegEmp, $this->config, $this->catalogos, $this->gestor))->render();
        self::assertSame('registrar_empleado', $this->ultimaVista);
        self::assertArrayHasKey(1, $this->ultimosDatosVista['estatus']);
        self::assertArrayNotHasKey(2, $this->ultimosDatosVista['estatus']);
        self::assertArrayHasKey(1, $this->ultimosDatosVista['estados']);
        self::assertSame('Visual', $this->ultimosDatosVista['tipos_discapacidad'][1]);

        $appEditEmp = $this->crearMockEngine('GET');
        (new EditarEmpleadoController($appEditEmp, $this->config, $this->catalogos, $this->gestor))->render('1');
        self::assertSame('editar_empleado', $this->ultimaVista);
        self::assertSame(1, $this->ultimosDatosVista['empleado']->empleado_id);
        self::assertArrayHasKey(2, $this->ultimosDatosVista['estatus']);

        $appRegFam = $this->crearMockEngine('GET', [], ['empleado' => '1']);
        (new RegistrarFamiliarController($appRegFam, $this->config, $this->catalogos, $this->gestor))->render();
        self::assertSame('registrar_familiar', $this->ultimaVista);
        self::assertSame('1', $this->ultimosDatosVista['empleado_seleccionado']);
        self::assertCount(2, $this->ultimosDatosVista['empleados']);

        $appEditFam = $this->crearMockEngine('GET');
        (new EditarFamiliarController($appEditFam, $this->config, $this->catalogos, $this->gestor))->render('1');
        self::assertSame('editar_familiar', $this->ultimaVista);
        self::assertSame(1, $this->ultimosDatosVista['familiar']->familia_id);
        self::assertSame('V-21456789', $this->ultimosDatosVista['titular']->cedula);
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $query
     * @return Engine<object>
     */
    private function crearMockEngine(
        string $method,
        array $data = [],
        array $query = [],
        string $url = '/api/test',
        bool $ajax = false
    ): Engine {
        $this->ultimoJson = null;
        $this->ultimoCodigo = 0;
        $this->ultimaRedireccion = null;
        $this->ultimaVista = null;
        $this->ultimosDatosVista = [];

        $_POST = $data;
        $_SERVER['CONTENT_TYPE'] = '';

        $request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();
        $request->method = $method;
        $request->url = $url;
        $request->ajax = $ajax;
        $request->accept = $ajax ? 'application/json' : 'text/html';
        $request->data = new Collection($data);
        $request->query = new Collection($query);
        $request->files = new Collection([]);

        $app = $this->getMockBuilder(Engine::class)
            ->disableOriginalConstructor()
            ->addMethods(['request', 'json', 'jsonHalt', 'redirect', 'render', 'halt'])
            ->getMock();

        $app->method('request')->willReturn($request);
        $app->method('json')->willReturnCallback(function (mixed $payload, int $code = 200): void {
            $this->ultimoJson = is_array($payload) ? $payload : [];
            $this->ultimoCodigo = $code;
        });
        $app->method('jsonHalt')->willReturnCallback(function (mixed $payload, int $code = 200): void {
            $this->ultimoJson = is_array($payload) ? $payload : [];
            $this->ultimoCodigo = $code;
        });
        $app->method('redirect')->willReturnCallback(function (string $destino): void {
            $this->ultimaRedireccion = $destino;
        });
        $app->method('render')->willReturnCallback(function (string $vista, array $datos = []): void {
            $this->ultimaVista = $vista;
            $this->ultimosDatosVista = $datos;
        });

        return $app;
    }

    private function crearEsquema(): void
    {
        $schema = $this->capsule->getConnection()->getSchemaBuilder();

        $schema->create('estados', static function (Blueprint $t): void {
            $t->increments('estado_id');
            $t->string('estado');
        });
        $schema->create('municipios', static function (Blueprint $t): void {
            $t->increments('municipio_id');
            $t->integer('estado_id');
            $t->string('municipio');
        });
        $schema->create('parroquias', static function (Blueprint $t): void {
            $t->increments('parroquia_id');
            $t->integer('municipio_id');
            $t->string('parroquia');
        });
        $schema->create('estado_civil', static function (Blueprint $t): void {
            $t->increments('estado_civil_id');
            $t->string('estado_civil');
        });
        $schema->create('estatus', static function (Blueprint $t): void {
            $t->increments('estatus_id');
            $t->string('estatus');
            $t->boolean('activo')->default(true);
        });
        $schema->create('dependencias', static function (Blueprint $t): void {
            $t->increments('dependencia_id');
            $t->string('dependencia');
            $t->string('codigo');
            $t->boolean('activo')->default(true);
            $t->integer('estado_id')->default(1);
        });
        $schema->create('departamentos', static function (Blueprint $t): void {
            $t->increments('departamento_id');
            $t->string('departamento');
            $t->boolean('activo')->default(true);
        });
        $schema->create('cargo', static function (Blueprint $t): void {
            $t->increments('cargo_id');
            $t->string('cargo');
            $t->boolean('activo')->default(true);
        });
        $schema->create('nivel_academicos', static function (Blueprint $t): void {
            $t->increments('nivel_academico_id');
            $t->string('nivel_academico');
            $t->integer('jerarquia');
        });
        $schema->create('carrera', static function (Blueprint $t): void {
            $t->increments('carrera_id');
            $t->string('carrera');
        });
        $schema->create('tipo_parentesco', static function (Blueprint $t): void {
            $t->increments('parentesco_id');
            $t->string('parentesco');
        });
        $schema->create('tipo_documentos', static function (Blueprint $t): void {
            $t->increments('tpdocumentos_id');
            $t->string('tipo_documentos');
        });
        $schema->create('personas', static function (Blueprint $t): void {
            $t->increments('persona_id');
            $t->string('primer_nombre');
            $t->string('segundo_nombre')->default('');
            $t->string('primer_apellido');
            $t->string('segundo_apellido')->default('');
            $t->string('genero');
            $t->integer('cedula');
            $t->string('nacionalidad');
            $t->date('fecha_nacimiento');
            $t->integer('estado_civil_id')->nullable();
        });
        $schema->create('empleados', static function (Blueprint $t): void {
            $t->increments('empleado_id');
            $t->integer('persona_id');
            $t->integer('dependencia_id');
            $t->integer('departamento_id');
            $t->integer('estatus_id');
            $t->integer('cargo_id');
            $t->integer('nivel_academico_id');
            $t->integer('carrera_id')->nullable();
            $t->string('codigo_empleado')->nullable();
            $t->integer('estatus_actual')->default(1);
            $t->string('correo')->nullable();
        });
        $schema->create('direccion', static function (Blueprint $t): void {
            $t->increments('direccion_id');
            $t->integer('empleado_id');
            $t->integer('parroquia_id');
            $t->string('vivienda');
            $t->string('calle');
            $t->string('nombre_urb');
            $t->string('piso_edificio');
            $t->string('numero_vivienda');
            $t->string('descripcion')->nullable();
        });
        $schema->create('telefono_personas', static function (Blueprint $t): void {
            $t->increments('telefono_id');
            $t->integer('persona_id');
            $t->string('numero_telefono');
            $t->boolean('emergencia')->default(false);
        });
        $schema->create('familiares', static function (Blueprint $t): void {
            $t->increments('familia_id');
            $t->integer('empleado_id');
            $t->integer('persona_id');
            $t->integer('parentesco_id');
            $t->integer('tomo');
            $t->integer('folio');
            $t->string('genero')->nullable();
        });
        $schema->create('documentos', static function (Blueprint $t): void {
            $t->increments('documentos_id');
            $t->integer('tipo_documento_id');
            $t->integer('persona_id');
            $t->string('codigo')->nullable();
            $t->string('ruta');
            $t->string('estatus_documento')->nullable();
            $t->string('nombre_original');
            $t->integer('tamano');
            $t->string('formato');
            $t->dateTime('fecha_subida')->nullable();
        });
        $schema->create('tipo_discapacidad', static function (Blueprint $t): void {
            $t->increments('tpdiscapacidad_id');
            $t->string('tipo_discapacidad');
        });
    }

    private function poblarDatos(): void
    {
        TipoDiscapacidadModel::query()->create(['tipo_discapacidad' => 'Visual']);
        EstadosModel::query()->create(['estado' => 'Aragua']);
        MunicipiosModel::query()->create(['estado_id' => 1, 'municipio' => 'Girardot']);
        ParroquiasModel::query()->create(['municipio_id' => 1, 'parroquia' => 'Madre María']);

        EstadoCivilModel::query()->create(['estado_civil' => 'Soltero(a)']);
        EstadoCivilModel::query()->create(['estado_civil' => 'Casado(a)']);

        EstatusModel::query()->create(['estatus' => 'Empleado', 'activo' => true]);
        EstatusModel::query()->create(['estatus' => 'Estudiante', 'activo' => false]);

        DependenciasModel::query()->create(['dependencia' => 'CFS La Romana', 'codigo' => 'ROM-01', 'activo' => true, 'estado_id' => 1]);
        DependenciasModel::query()->create(['dependencia' => 'Sede Central', 'codigo' => 'CEN-01', 'activo' => true, 'estado_id' => 1]);

        DepartamentosModel::query()->create(['departamento' => 'Talento Humano', 'activo' => true]);
        CargoModel::query()->create(['cargo' => 'Analista', 'activo' => true]);

        NivelAcademicosModel::query()->create(['nivel_academico' => 'Universitario', 'jerarquia' => 2]);
        NivelAcademicosModel::query()->create(['nivel_academico' => 'Bachiller', 'jerarquia' => 1]);
        CarreraModel::query()->create(['carrera' => 'Relaciones Industriales']);

        TipoParentescoModel::query()->create(['parentesco' => 'Madre']);
        TipoParentescoModel::query()->create(['parentesco' => 'Padre']);
        TipoParentescoModel::query()->create(['parentesco' => 'Hijo']);
        TipoParentescoModel::query()->create(['parentesco' => 'Hija']);

        TipoDocumentosModel::query()->create(['tipo_documentos' => 'Cédula de Identidad']);

        PersonasModel::query()->create([
            'primer_nombre' => 'Carlos', 'segundo_nombre' => 'Eduardo',
            'primer_apellido' => 'Mendoza', 'segundo_apellido' => 'Ríos',
            'genero' => 'Masculino', 'cedula' => 21456789, 'nacionalidad' => 'V',
            'fecha_nacimiento' => '1990-08-14', 'estado_civil_id' => 2,
        ]);
        PersonasModel::query()->create([
            'primer_nombre' => 'María', 'segundo_nombre' => 'Fernanda',
            'primer_apellido' => 'López', 'segundo_apellido' => 'Silva',
            'genero' => 'Femenino', 'cedula' => 19876543, 'nacionalidad' => 'V',
            'fecha_nacimiento' => '1988-02-10', 'estado_civil_id' => 1,
        ]);
        PersonasModel::query()->create([
            'primer_nombre' => 'Sofía', 'segundo_nombre' => 'Valentina',
            'primer_apellido' => 'Mendoza', 'segundo_apellido' => 'Herrera',
            'genero' => 'Femenino', 'cedula' => 31245678, 'nacionalidad' => 'V',
            'fecha_nacimiento' => '2012-05-01', 'estado_civil_id' => 1,
        ]);
        PersonasModel::query()->create([
            'primer_nombre' => 'Mateo', 'segundo_nombre' => 'Andrés',
            'primer_apellido' => 'Mendoza', 'segundo_apellido' => 'Herrera',
            'genero' => 'Masculino', 'cedula' => 33245678, 'nacionalidad' => 'V',
            'fecha_nacimiento' => '2016-11-20', 'estado_civil_id' => 1,
        ]);

        EmpleadosModel::query()->create([
            'persona_id' => 1, 'dependencia_id' => 1, 'departamento_id' => 1,
            'estatus_id' => 1, 'cargo_id' => 1, 'nivel_academico_id' => 1,
            'carrera_id' => 1, 'codigo_empleado' => 'EMP-001', 'estatus_actual' => 1,
            'correo' => 'carlos@inces.gob.ve',
        ]);
        EmpleadosModel::query()->create([
            'persona_id' => 2, 'dependencia_id' => 2, 'departamento_id' => 1,
            'estatus_id' => 1, 'cargo_id' => 1, 'nivel_academico_id' => 1,
            'carrera_id' => 1, 'codigo_empleado' => 'EMP-002', 'estatus_actual' => 1,
            'correo' => 'maria@inces.gob.ve',
        ]);

        FamiliaresModel::query()->create([
            'empleado_id' => 1, 'persona_id' => 3, 'parentesco_id' => 4,
            'tomo' => 4, 'folio' => 128, 'genero' => 'Femenino',
        ]);
        FamiliaresModel::query()->create([
            'empleado_id' => 1, 'persona_id' => 4, 'parentesco_id' => 3,
            'tomo' => 5, 'folio' => 12, 'genero' => 'Masculino',
        ]);
    }

    private function eliminarDirectorio(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($it as $item) {
            if ($item->isDir()) {
                @rmdir($item->getPathname());
            } else {
                @unlink($item->getPathname());
            }
        }
        @rmdir($dir);
    }
}
