<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Controller\AsistenciaController;
use App\Controller\RegistroVacacionesController;
use App\Controller\RolesPermisosController;
use App\Controller\UsuariosController;
use App\Services\CatalogosService;
use App\Model\Asistencia\AusenciaJustificadaModel;
use App\Model\Asistencia\DetalleAsistenciaModel;
use App\Model\Asistencia\DetalleReposoModel;
use App\Model\Asistencia\TipoRepososModel;
use App\Model\Beneficios\TipoBeneficiosModel;
use App\Model\Catalogos\CargoModel;
use App\Model\Catalogos\DepartamentosModel;
use App\Model\Catalogos\TipoDocumentosModel;
use App\Model\Personas\EmpleadosModel;
use App\Model\Personas\PersonasModel;
use App\Model\Seguridad\PermisosModel;
use App\Model\Seguridad\RolModel;
use App\Model\Seguridad\UsuariosModel;
use App\Model\Vacaciones\SolicitudVacacionesModel;
use App\Services\AsistenciaService;
use App\Services\AusenciasRepososService;
use App\Services\BeneficiosService;
use App\Services\GestorDocumentos;
use App\Services\ManejadorClave;
use App\Services\UsuariosService;
use App\Services\VacacionesService;
use App\Utils\Config;
use flight\Engine;
use flight\net\Request;
use flight\util\Collection;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class NuevosModulosIncesTest extends TestCase
{
    private Capsule $capsule;
    private string $tempDir;
    private GestorDocumentos $gestor;
    private Config $config;

    /** @var array<string, mixed>|null */
    private ?array $ultimoJson = null;
    private int $ultimoCodigo = 0;

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
        $this->poblarDatosBase();

        $this->tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'atlas_inces_test_' . bin2hex(random_bytes(5));
        mkdir($this->tempDir, 0775, true);

        $this->gestor = new GestorDocumentos(
            $this->capsule->getConnection(),
            $this->tempDir . DIRECTORY_SEPARATOR . 'DocumentosInces',
            5242880
        );
        $this->config = new Config(['app' => ['env' => 'testing', 'debug' => false]]);
    }

    protected function tearDown(): void
    {
        $_POST = [];
        $this->eliminarDirectorio($this->tempDir);
        parent::tearDown();
    }

    public function testVacacionesDiasHabilesIncesExcluyeFinesDeSemanaYFeriadosContractuales(): void
    {
        $svc = new VacacionesService();

        // Semana del 16 al 22 de noviembre 2026: Lunes 16 a Domingo 22 -> viernes 20-Nov es Aniversario INCES (Cláusula 7) => 4 días hábiles
        self::assertSame(4, $svc->calcularDiasHabilesInces('2026-11-16', '2026-11-22'));

        // Semana Santa 2026 (Pascua 2026-04-05 -> Jueves 2026-04-02 y Viernes 2026-04-03 son no laborables INCES) => 3 días hábiles
        self::assertSame(3, $svc->calcularDiasHabilesInces('2026-03-30', '2026-04-05'));

        // Rango invertido devuelve 0
        self::assertSame(0, $svc->calcularDiasHabilesInces('2026-11-25', '2026-11-20'));
    }

    public function testVacacionesValidaSaldoDisponibleYReintegraDiasAlSuspender(): void
    {
        $svc = new VacacionesService();

        $this->capsule->getConnection()->table('periodo_vacaciones')->insert([
            'empleado_id' => 1,
            'periodo' => 2025,
            'dias_totales' => 30,
            'dias_disfrutados' => 25,
            'dias_pendientes' => 5,
            'estado_vacaciones' => 'Abiertas',
        ]);

        self::assertSame(5, $svc->obtenerDiasDisponibles(1));

        /** @var SolicitudVacacionesModel $excesiva */
        $excesiva = SolicitudVacacionesModel::query()->create([
            'empleado_id' => 1,
            'dias_solicitado' => 8,
            'dias_afectados' => 8,
            'fecha_inicio' => '2026-06-01',
            'fecha_final' => '2026-06-10',
            'estatus' => 1,
            'gerencia' => false,
        ]);

        // No debe permitir aprobar si solicita más días que los disponibles
        $bloqueado = false;
        try {
            $svc->procesarCambioEstatus($excesiva, 2);
        } catch (InvalidArgumentException $e) {
            $bloqueado = true;
            self::assertSame(422, $e->getCode());
        }
        self::assertTrue($bloqueado);

        // Solicitud válida de 5 días hábiles
        /** @var SolicitudVacacionesModel $valida */
        $valida = SolicitudVacacionesModel::query()->create([
            'empleado_id' => 1,
            'dias_solicitado' => 5,
            'dias_afectados' => 5,
            'fecha_inicio' => '2026-07-06',
            'fecha_final' => '2026-07-10',
            'estatus' => 2,
            'gerencia' => false,
        ]);

        // Simulamos el descuento del trigger en periodo_vacaciones + detalle_vacaciones
        $this->capsule->getConnection()->table('periodo_vacaciones')
            ->where('periodo_vacaciones_id', 1)
            ->update([
                'dias_pendientes' => 0,
                'dias_disfrutados' => 30,
                'estado_vacaciones' => 'Cerrado',
            ]);
        $this->capsule->getConnection()->table('detalle_vacaciones')->insert([
            'solicitud_vacaciones_id' => (int) $valida->solicitud_vacaciones_id,
            'periodo_vacaciones_id' => 1,
            'dia_descontados' => 5,
        ]);

        // Suspender con solo 2 días disfrutados debe devolver 3 días a `periodo_vacaciones` y reabrir el período
        $svc->procesarCambioEstatus($valida, 6, 2, 'Suspensión anticipada de prueba');

        self::assertSame(6, (int) $valida->fresh()?->estatus);
        self::assertSame(3, $svc->obtenerDiasDisponibles(1));
    }

    public function testVacacionesCalculoFechaFinalDesdeInicioYCantidadDiasHabiles(): void
    {
        $svc = new VacacionesService();

        // 1. Caso base: Lunes hábil + 5 días (sin feriados) -> Lunes siguiente
        $base = $svc->calcularFechaFinYReincorporacion('2026-11-02', 5);
        self::assertSame('2026-11-09', $base['fecha_final']);
        self::assertSame('2026-11-10', $base['fecha_reincorporacion']);

        // 2. Caso mínimo: Día hábil + 1 día -> siguiente día hábil
        $minimo = $svc->calcularFechaFinYReincorporacion('2026-11-02', 1);
        self::assertSame('2026-11-03', $minimo['fecha_final']);
        self::assertSame('2026-11-04', $minimo['fecha_reincorporacion']);

        // 3. Cruce de fin de semana: Jueves hábil + 4 días -> Miércoles siguiente
        $cruceFinde = $svc->calcularFechaFinYReincorporacion('2026-11-05', 4);
        self::assertSame('2026-11-11', $cruceFinde['fecha_final']);
        self::assertSame('2026-11-12', $cruceFinde['fecha_reincorporacion']);

        // 4. Cruce de feriados INCES y cambio de año (salta 17, 24, 25, 31 dic y 1 ene + fines de semana)
        $cruceAnio = $svc->calcularFechaFinYReincorporacion('2026-12-16', 10);
        self::assertSame('2027-01-06', $cruceAnio['fecha_final']);
        self::assertSame('2027-01-07', $cruceAnio['fecha_reincorporacion']);

        // 5. Consistencia inversa: calcularDiasDisfrutadosPorCorte coincide exactamente con N
        self::assertSame(0, $svc->calcularDiasDisfrutadosPorCorte('2026-11-02', '2026-11-02'));
        self::assertSame(1, $svc->calcularDiasDisfrutadosPorCorte('2026-11-02', '2026-11-03'));
        self::assertSame(5, $svc->calcularDiasDisfrutadosPorCorte('2026-11-02', '2026-11-09'));
        self::assertSame(10, $svc->calcularDiasDisfrutadosPorCorte('2026-12-16', '2027-01-06'));

        // 6. Entradas inválidas: sábado, domingo, feriado o días <= 0
        self::assertFalse($svc->esDiaHabilInces('2026-11-07')); // sábado
        self::assertFalse($svc->esDiaHabilInces('2026-11-08')); // domingo
        self::assertFalse($svc->esDiaHabilInces('2026-11-20')); // Día del Trabajador INCES
        self::assertSame('', $svc->calcularFechaFinYReincorporacion('2026-11-07', 3)['fecha_final']);
        self::assertSame('', $svc->calcularFechaFinYReincorporacion('2026-11-02', 0)['fecha_final']);
    }

    public function testVacacionesLeeCatalogoDiasFeriadosYLoExponePorEndpoint(): void
    {
        $schema = $this->capsule->getConnection()->getSchemaBuilder();
        $schema->create('dias_feriados', static function (Blueprint $t): void {
            $t->increments('feriado_id');
            $t->date('fecha')->nullable();
            $t->integer('mes')->nullable();
            $t->integer('dia')->nullable();
            $t->string('tipo')->default('Nacional');
            $t->string('descripcion');
            $t->boolean('es_recurrente')->default(false);
            $t->boolean('activo')->default(true);
        });

        $this->capsule->getConnection()->table('dias_feriados')->insert([
            ['fecha' => null, 'mes' => 1, 'dia' => 1, 'tipo' => 'Nacional', 'descripcion' => 'Año Nuevo', 'es_recurrente' => true, 'activo' => true],
            ['fecha' => null, 'mes' => 12, 'dia' => 17, 'tipo' => 'Contractual INCES', 'descripcion' => 'INCES 17 Dic', 'es_recurrente' => true, 'activo' => true],
            ['fecha' => null, 'mes' => 12, 'dia' => 24, 'tipo' => 'Contractual INCES', 'descripcion' => 'INCES 24 Dic', 'es_recurrente' => true, 'activo' => true],
            ['fecha' => null, 'mes' => 12, 'dia' => 25, 'tipo' => 'Nacional', 'descripcion' => 'Navidad', 'es_recurrente' => true, 'activo' => true],
            ['fecha' => null, 'mes' => 12, 'dia' => 31, 'tipo' => 'Contractual INCES', 'descripcion' => 'INCES 31 Dic', 'es_recurrente' => true, 'activo' => true],
            ['fecha' => '2026-04-02', 'mes' => null, 'dia' => null, 'tipo' => 'Móvil', 'descripcion' => 'Jueves Santo 2026', 'es_recurrente' => false, 'activo' => true],
            ['fecha' => '2026-04-03', 'mes' => null, 'dia' => null, 'tipo' => 'Móvil', 'descripcion' => 'Viernes Santo 2026', 'es_recurrente' => false, 'activo' => true],
            ['fecha' => '2027-03-25', 'mes' => null, 'dia' => null, 'tipo' => 'Móvil', 'descripcion' => 'Jueves Santo 2027', 'es_recurrente' => false, 'activo' => true],
            ['fecha' => '2027-03-26', 'mes' => null, 'dia' => null, 'tipo' => 'Móvil', 'descripcion' => 'Viernes Santo 2027', 'es_recurrente' => false, 'activo' => true],
        ]);

        $svc = new VacacionesService();
        $catalogo = $svc->obtenerCatalogoFeriados(2026, 2027);
        self::assertContains('2026-12-17', $catalogo['fechas']);
        self::assertContains('2026-12-24', $catalogo['fechas']);
        self::assertContains('2026-12-25', $catalogo['fechas']);
        self::assertContains('2026-12-31', $catalogo['fechas']);
        self::assertContains('2027-01-01', $catalogo['fechas']);
        self::assertContains('2027-03-25', $catalogo['fechas']);

        $app = $this->crearMockEngine('GET', [], ['anio_inicio' => '2026', 'anio_fin' => '2027']);
        $controller = new RegistroVacacionesController($app, $this->config, new CatalogosService(), $svc);
        $controller->feriados();

        self::assertSame(200, $this->ultimoCodigo);
        self::assertTrue((bool) ($this->ultimoJson['ok'] ?? false));
        self::assertContains('2027-01-01', $this->ultimoJson['data']['fechas'] ?? []);
    }

    public function testReanudarAusenciaAcortaLapsoYDevuelveDiasNoCumplidos(): void
    {
        $vacSvc = new VacacionesService();
        $ausSvc = new AusenciasRepososService();

        /** @var AusenciaJustificadaModel $ausencia */
        $ausencia = AusenciaJustificadaModel::query()->create([
            'empleado_id' => 1,
            'fecha_inicio' => '2026-08-10',
            'fecha_fin' => '2026-08-14',
            'dias_ausencia' => 5,
            'tipo_ausencia' => 1,
        ]);

        $res = $ausSvc->reanudarAusencia((int) $ausencia->ausencia_id, '2026-08-12', $vacSvc);

        self::assertSame(3, $res['dias_devueltos']);
        self::assertSame(2, $res['dias_efectivos']);
        self::assertSame('2026-08-11', $ausencia->fresh()?->fecha_fin?->format('Y-m-d'));
    }

    public function testBeneficiosContratoColectivoValida37ReglasPeriodicidadYSoporteDocumental(): void
    {
        $svc = new BeneficiosService();
        $reglas = $svc->obtenerReglasParaCliente([
            4 => 'Acta de Matrimonio',
            5 => 'Unión Estable de Hecho',
            6 => 'Constancia de Inscripción / Estudios',
        ]);

        self::assertCount(37, $reglas);
        self::assertFalse($reglas[1]['requiere_documento']); // Dotación de Uniformes
        self::assertTrue($reglas[23]['requiere_documento']); // Matrimonio
        self::assertTrue($reglas[28]['requiere_documento']); // Útiles escolares

        // Beneficio 30 (Juguetes - hasta 2 veces al año, sin documento obligatorio)
        $svc->registrar(1, 30, '2026-07-15');
        $svc->registrar(1, 30, '2026-11-20');

        $terceroBloqueado = false;
        try {
            $svc->registrar(1, 30, '2026-12-10');
        } catch (InvalidArgumentException $e) {
            $terceroBloqueado = true;
            self::assertSame(422, $e->getCode());
        }
        self::assertTrue($terceroBloqueado);

        // Beneficio 28 (Útiles Escolares - exige documento tipo 6) sin soporte debe bloquear con 422
        $sinDocBloqueado = false;
        try {
            $svc->registrar(1, 28, '2026-09-15');
        } catch (InvalidArgumentException $e) {
            $sinDocBloqueado = true;
            self::assertSame(422, $e->getCode());
        }
        self::assertTrue($sinDocBloqueado);

        // Subiendo soporte PDF vía GestorDocumentos se aprueba y guarda el documento como 'Vigente'
        $pdf = $this->tempDir . DIRECTORY_SEPARATOR . 'inscripcion.pdf';
        file_put_contents($pdf, "%PDF-1.4\n1 0 obj<<>>endobj\n%%EOF");
        $creado = $svc->registrar(
            1,
            28,
            '2026-09-15',
            '2026-09-16',
            [
                'name' => 'inscripcion.pdf',
                'tmp_name' => $pdf,
                'size' => (int) filesize($pdf),
                'error' => UPLOAD_ERR_OK,
            ],
            6,
            $this->gestor,
            false
        );
        self::assertGreaterThan(0, (int) $creado->dtbeneficio_id);
        self::assertTrue($svc->estadoDocumentalPersonaBeneficio(1, 28)['tiene_vigente']);
    }

    public function testAsistenciaBloqueaEntradaDuranteReposoMedicoYPermiteDespuesDeFechaFin(): void
    {
        $ausencia = AusenciaJustificadaModel::query()->create([
            'empleado_id' => 1,
            'fecha_inicio' => '2026-10-05',
            'fecha_fin' => '2026-10-09',
            'dias_ausencia' => 5,
            'tipo_ausencia' => 2,
        ]);
        DetalleReposoModel::query()->create([
            'ausencia_id' => (int) $ausencia->ausencia_id,
            'tpreposo_id' => 1,
            'descripcion' => 'Reposo médico general',
            'fecha_solicitud' => '2026-10-05',
        ]);

        // Intentar marcar entrada dentro del rango de reposo (2026-10-07) debe bloquear con 409
        $appBloq = $this->crearMockEngine('POST', [
            'empleado_id' => 1,
            'turno' => 'Diurno',
            'fecha' => '2026-10-07',
            'hora' => '08:00',
        ]);
        (new AsistenciaController($appBloq, $this->config, new AsistenciaService()))->entrada();
        self::assertSame(409, $this->ultimoCodigo);
        self::assertFalse((bool) ($this->ultimoJson['ok'] ?? true));

        // Después de `fecha_fin` (2026-10-12) permite marcar Entrada (201) y luego Salida (200)
        $appEntrada = $this->crearMockEngine('POST', [
            'empleado_id' => 1,
            'turno' => 'Diurno',
            'fecha' => '2026-10-12',
            'hora' => '08:05',
        ]);
        (new AsistenciaController($appEntrada, $this->config, new AsistenciaService()))->entrada();
        self::assertSame(201, $this->ultimoCodigo);

        // Salida anterior a la entrada debe rechazar con 422
        $appSalidaInvalida = $this->crearMockEngine('POST', [
            'empleado_id' => 1,
            'fecha' => '2026-10-12',
            'hora' => '07:30',
        ]);
        (new AsistenciaController($appSalidaInvalida, $this->config, new AsistenciaService()))->salida();
        self::assertSame(422, $this->ultimoCodigo);

        $appSalidaOk = $this->crearMockEngine('POST', [
            'empleado_id' => 1,
            'fecha' => '2026-10-12',
            'hora' => '16:30',
        ]);
        (new AsistenciaController($appSalidaOk, $this->config, new AsistenciaService()))->salida();
        self::assertSame(200, $this->ultimoCodigo);
        self::assertNotNull(DetalleAsistenciaModel::query()->first()?->hora_salida);
    }

    public function testUsuariosYRolesPermisosGestionanCuentasEstatusYSincronizacionRbac(): void
    {
        $usrSvc = new UsuariosService();

        // Configurar permisos base en el Rol 1 (Administrador) desde RolesPermisosController
        $appSyncRol = $this->crearMockEngine('PUT', [
            'permiso_ids' => [1, 2],
            'sincronizar_usuarios' => true,
        ]);
        (new RolesPermisosController($appSyncRol, $this->config, $usrSvc))->actualizarPermisos('1');
        self::assertSame(200, $this->ultimoCodigo);

        // Registrar nuevo usuario vinculado al empleado 1 -> hereda automáticamente permisos [1, 2]
        $appStoreUsr = $this->crearMockEngine('POST', [
            'empleado_id' => 1,
            'rol_id' => 1,
            'nombre_usuario' => 'cmendoza',
            'contrasena' => 'ClaveSegura2026',
            'pin' => 1234,
        ]);
        (new UsuariosController($appStoreUsr, $this->config, $usrSvc))->store();
        self::assertSame(201, $this->ultimoCodigo);
        $usuarioId = (int) ($this->ultimoJson['data']['usuario_id'] ?? 0);
        self::assertGreaterThan(0, $usuarioId);

        $creado = UsuariosModel::query()->find($usuarioId);
        self::assertInstanceOf(UsuariosModel::class, $creado);
        self::assertTrue(ManejadorClave::verificar('ClaveSegura2026', (string) $creado->contrasena));
        self::assertSame(2, $creado->permisos()->count());

        // Desactivar usuario (`estatus = 0`) y luego reactivarlo (`estatus = 1`)
        $appDesactivar = $this->crearMockEngine('POST', ['estatus' => 0]);
        (new UsuariosController($appDesactivar, $this->config, $usrSvc))->cambiarEstatus((string) $usuarioId);
        self::assertSame(200, $this->ultimoCodigo);
        self::assertSame(0, (int) UsuariosModel::query()->find($usuarioId)?->estatus);

        $appActivar = $this->crearMockEngine('POST', ['estatus' => 1]);
        (new UsuariosController($appActivar, $this->config, $usrSvc))->cambiarEstatus((string) $usuarioId);
        self::assertSame(200, $this->ultimoCodigo);
        self::assertSame(1, (int) UsuariosModel::query()->find($usuarioId)?->estatus);

        // Crear un nuevo rol y sincronizar un nuevo permiso al rol 1 propagándolo al usuario
        $appNuevoRol = $this->crearMockEngine('POST', ['rol' => 'Analista Regional']);
        (new RolesPermisosController($appNuevoRol, $this->config, $usrSvc))->store();
        self::assertSame(201, $this->ultimoCodigo);

        $usrSvc->actualizarPermisosRol(1, [1, 2, 3], true);
        self::assertSame(3, UsuariosModel::query()->find($usuarioId)?->permisos()->count());
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $query
     */
    private function crearMockEngine(string $method, array $data = [], array $query = []): Engine
    {
        $this->ultimoJson = null;
        $this->ultimoCodigo = 0;
        $_POST = $data;
        $_SERVER['CONTENT_TYPE'] = '';

        $request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();
        $request->method = $method;
        $request->url = '/api/test';
        $request->ajax = true;
        $request->accept = 'application/json';
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

        return $app;
    }

    private function crearEsquema(): void
    {
        $schema = $this->capsule->getConnection()->getSchemaBuilder();

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
        $schema->create('empleados', static function (Blueprint $t): void {
            $t->increments('empleado_id');
            $t->integer('persona_id');
            $t->integer('dependencia_id')->default(1);
            $t->integer('departamento_id')->default(1);
            $t->integer('estatus_id')->default(1);
            $t->integer('cargo_id')->default(1);
            $t->integer('nivel_academico_id')->default(1);
            $t->integer('carrera_id')->nullable();
            $t->string('codigo_empleado')->nullable();
            $t->integer('estatus_actual')->default(1);
            $t->string('correo')->nullable();
        });
        $schema->create('familiares', static function (Blueprint $t): void {
            $t->increments('familia_id');
            $t->integer('empleado_id');
            $t->integer('persona_id');
            $t->integer('parentesco_id');
            $t->integer('tomo')->default(1);
            $t->integer('folio')->default(1);
            $t->string('genero')->nullable();
        });
        $schema->create('tipo_documentos', static function (Blueprint $t): void {
            $t->increments('tpdocumentos_id');
            $t->string('tipo_documentos');
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
        $schema->create('periodo_vacaciones', static function (Blueprint $t): void {
            $t->increments('periodo_vacaciones_id');
            $t->integer('empleado_id');
            $t->integer('periodo');
            $t->integer('dias_totales');
            $t->integer('dias_disfrutados')->default(0);
            $t->integer('dias_pendientes');
            $t->string('estado_vacaciones')->default('Abiertas');
        });
        $schema->create('solicitud_vacaciones', static function (Blueprint $t): void {
            $t->increments('solicitud_vacaciones_id');
            $t->integer('empleado_id');
            $t->integer('dias_solicitado');
            $t->integer('dias_afectados')->default(0);
            $t->date('fecha_inicio');
            $t->date('fecha_final');
            $t->integer('estatus')->default(1);
            $t->boolean('gerencia')->default(false);
            $t->dateTime('fecha_solicitud')->nullable();
        });
        $schema->create('detalle_vacaciones', static function (Blueprint $t): void {
            $t->increments('dt_vacaciones_id');
            $t->integer('solicitud_vacaciones_id');
            $t->integer('periodo_vacaciones_id');
            $t->integer('dia_descontados');
        });
        $schema->create('historial_vacaciones', static function (Blueprint $t): void {
            $t->increments('historial_id');
            $t->integer('solicitud_vacaciones_id');
            $t->integer('estatus_id');
            $t->string('observacion')->nullable();
            $t->dateTime('fecha_cambio')->nullable();
        });
        $schema->create('ausencia_justificada', static function (Blueprint $t): void {
            $t->increments('ausencia_id');
            $t->integer('empleado_id');
            $t->date('fecha_inicio');
            $t->date('fecha_fin');
            $t->integer('dias_ausencia');
            $t->integer('tipo_ausencia');
        });
        $schema->create('tipo_reposos', static function (Blueprint $t): void {
            $t->increments('tpreposo_id');
            $t->string('reposo');
            $t->boolean('activo')->default(true);
        });
        $schema->create('detalle_reposo', static function (Blueprint $t): void {
            $t->increments('dtreposo_id');
            $t->integer('ausencia_id');
            $t->integer('tpreposo_id');
            $t->string('descripcion');
            $t->integer('tpinstitucion_emisora_id')->nullable();
            $t->date('fecha_solicitud');
            $t->string('doctor')->nullable();
        });
        $schema->create('detalle_asistencia', static function (Blueprint $t): void {
            $t->increments('dtasistencia_id');
            $t->integer('empleado_id');
            $t->string('turno');
            $t->date('fecha');
            $t->string('hora_entrada');
            $t->string('hora_salida')->nullable();
        });
        $schema->create('tipo_beneficios', static function (Blueprint $t): void {
            $t->integer('tpbeneficio_id')->primary();
            $t->string('tipo_beneficio');
        });
        $schema->create('detalle_beneficio', static function (Blueprint $t): void {
            $t->increments('dtbeneficio_id');
            $t->integer('persona_id');
            $t->integer('beneficio_id');
            $t->date('fecha_solicitud');
            $t->date('fecha_aprobada')->nullable();
        });
        $schema->create('rol', static function (Blueprint $t): void {
            $t->increments('rol_id');
            $t->string('rol');
        });
        $schema->create('permisos', static function (Blueprint $t): void {
            $t->increments('permisos_id');
            $t->string('permiso');
            $t->string('descripcion')->nullable();
        });
        $schema->create('rol_permiso', static function (Blueprint $t): void {
            $t->increments('rol_permiso_id');
            $t->integer('rol_id');
            $t->integer('permiso_id');
        });
        $schema->create('usuarios', static function (Blueprint $t): void {
            $t->increments('usuario_id');
            $t->integer('empleado_id');
            $t->integer('rol_id');
            $t->string('nombre_usuario');
            $t->string('contrasena');
            $t->integer('pin')->nullable();
            $t->integer('estatus')->default(1);
            $t->integer('intentos')->default(0);
        });
        $schema->create('usuario_permisos', static function (Blueprint $t): void {
            $t->integer('usuario_id');
            $t->integer('permiso_id');
        });
    }

    private function poblarDatosBase(): void
    {
        DepartamentosModel::query()->create(['departamento' => 'Talento Humano', 'activo' => true]);
        CargoModel::query()->create(['cargo' => 'Analista RRHH', 'activo' => true]);

        PersonasModel::query()->create([
            'primer_nombre' => 'Carlos',
            'segundo_nombre' => 'Eduardo',
            'primer_apellido' => 'Mendoza',
            'segundo_apellido' => 'Ríos',
            'genero' => 'Masculino',
            'cedula' => 21456789,
            'nacionalidad' => 'V',
            'fecha_nacimiento' => '1990-08-14',
        ]);

        EmpleadosModel::query()->create([
            'persona_id' => 1,
            'dependencia_id' => 1,
            'departamento_id' => 1,
            'estatus_id' => 1,
            'cargo_id' => 1,
            'nivel_academico_id' => 1,
            'codigo_empleado' => 'EMP-001',
            'estatus_actual' => 1,
            'correo' => 'carlos@inces.gob.ve',
        ]);

        for ($i = 1; $i <= 6; $i++) {
            TipoDocumentosModel::query()->create(['tipo_documentos' => "Documento Tipo {$i}"]);
        }

        TipoRepososModel::query()->create(['reposo' => 'Enfermedad Común', 'activo' => true]);

        foreach ([1 => 'Dotación de Uniformes', 23 => 'Bono por Matrimonio', 28 => 'Útiles Escolares', 30 => 'Juguetes'] as $id => $nombre) {
            $this->capsule->getConnection()->table('tipo_beneficios')->insert([
                'tpbeneficio_id' => $id,
                'tipo_beneficio' => $nombre,
            ]);
        }

        RolModel::query()->create(['rol' => 'Administrador']);
        PermisosModel::query()->create(['permiso' => 'empleados.ver', 'descripcion' => 'Ver empleados']);
        PermisosModel::query()->create(['permiso' => 'vacaciones.aprobar', 'descripcion' => 'Aprobar vacaciones']);
        PermisosModel::query()->create(['permiso' => 'usuarios.gestionar', 'descripcion' => 'Gestionar usuarios']);
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
