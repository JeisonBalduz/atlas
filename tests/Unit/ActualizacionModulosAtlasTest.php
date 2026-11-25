<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Controller\EditarEmpleadoController;
use App\Controller\EditarFamiliarController;
use App\Controller\RegistrarFamiliarController;
use App\Controller\RegistroBeneficiosController;
use App\Controller\RegistrosEmpleadosController;
use App\Controller\RegistrosFamiliaresController;
use App\Model\Beneficios\DetalleBeneficioModel;
use App\Model\Beneficios\DetalleDiscapacidadModel;
use App\Model\Beneficios\GradoDiscapacidadModel;
use App\Model\Beneficios\TipoBeneficiosModel;
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
use App\Model\Personas\DireccionModel;
use App\Model\Personas\EmpleadosModel;
use App\Model\Personas\FamiliaresModel;
use App\Model\Personas\PersonasModel;
use App\Model\Personas\TelefonoPersonasModel;
use App\Model\Ubicacion\EstadosModel;
use App\Model\Ubicacion\MunicipiosModel;
use App\Model\Ubicacion\ParroquiasModel;
use App\Services\BeneficiosService;
use App\Services\CatalogosService;
use App\Services\GestorDocumentos;
use App\Utils\Config;
use flight\Engine;
use flight\net\Request;
use flight\util\Collection;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class ActualizacionModulosAtlasTest extends TestCase
{
    private Capsule $capsule;
    private string $tempDir;
    private GestorDocumentos $gestor;
    private Config $config;
    private CatalogosService $catalogos;

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
        $this->poblarDatos();

        $this->tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'atlas_upd_test_' . bin2hex(random_bytes(5));
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
        $_FILES = [];
        $this->eliminarDirectorio($this->tempDir);
        parent::tearDown();
    }

    /**
     * Valida edición de código único, dirección Casa/Departamento, teléfonos divididos y discapacidad en empleados.
     */
    public function testEditarEmpleadoCodigoUnicoDireccionTelefonosYDiscapacidad(): void
    {
        // 1. Sin discapacidad: el recaudo 10 no aparece en recaudos del empleado
        $recaudosSinDisc = $this->catalogos->recaudosParaEmpleado(1, 1, 1, 1);
        $idsSinDisc = array_column($recaudosSinDisc, 'tipo_documento_id');
        self::assertNotContains(10, $idsSinDisc);

        // 2. Código duplicado de otro trabajador debe rechazarse (422)
        $appDup = $this->crearMockEngine('POST', ['codigo_empleado' => 'EMP-002']);
        (new EditarEmpleadoController($appDup, $this->config, $this->catalogos, $this->gestor))->update('1');
        self::assertSame(422, $this->ultimoCodigo);

        // 3. Dirección inválida (números en nombre_urb para Departamento) debe rechazarse (422)
        $appDirMal = $this->crearMockEngine('POST', [
            'vivienda' => 'Departamento',
            'nombre_urb' => 'Urb 123',
            'calle' => 'Calle 5',
            'piso_edificio' => 'Piso 2',
            'numero_vivienda' => '2B',
            'descripcion' => 'Cerca de plaza',
        ]);
        (new EditarEmpleadoController($appDirMal, $this->config, $this->catalogos, $this->gestor))->update('1');
        self::assertSame(422, $this->ultimoCodigo);

        // 4. Actualización válida con código nuevo, Departamento, teléfonos divididos y discapacidad
        DetalleDiscapacidadModel::query()->create([
            'persona_id' => 1,
            'grado_discapacidad_id' => 1,
            'numero_carnet' => 'CONAPDIS-001',
            'entidad_emisora' => 'CONAPDIS',
            'descripcion' => 'Inicial',
            'dirigido' => 'Visual',
            'fecha_emision_carnet' => '2025-01-01',
            'fecha_vencimiento_carnet' => '2030-01-01',
        ]);

        $appOk = $this->crearMockEngine('POST', [
            'codigo_empleado' => 'EMP-900',
            'vivienda' => 'Departamento',
            'nombre_urb' => 'La Soledad',
            'calle' => 'Av Bolivar 12',
            'piso_edificio' => 'Torre A Piso 4',
            'numero_vivienda' => 'Apto 4B',
            'descripcion' => 'Frente al parque central',
            'parroquia_id' => '1',
            'telefonos_operadora' => ['0412', '0243'],
            'telefonos_digitos' => ['1234567', '7654321'],
            'telefonos_emergencia' => ['1', '0'],
            'discapacidad_activa' => '1',
            'tipo_discapacidad' => 'Visual',
            'grado_discapacidad_id' => '1',
            'numero_carnet' => 'CONAPDIS-999',
            'entidad_emisora' => 'CONAPDIS Aragua',
            'fecha_emision_carnet' => '2025-02-01',
            'fecha_vencimiento_carnet' => '2030-02-01',
            'descripcion_discapacidad' => 'Actualizada',
        ]);
        (new EditarEmpleadoController($appOk, $this->config, $this->catalogos, $this->gestor))->update('1');
        self::assertSame(200, $this->ultimoCodigo);

        $emp = EmpleadosModel::query()->find(1);
        self::assertNotNull($emp);
        self::assertSame('EMP-900', $emp->codigo_empleado);

        $dir = DireccionModel::query()->where('empleado_id', 1)->first();
        self::assertNotNull($dir);
        self::assertSame('Departamento', $dir->vivienda);
        self::assertSame('La Soledad', $dir->nombre_urb);

        $tels = TelefonoPersonasModel::query()->where('persona_id', 1)->orderBy('telefono_id')->pluck('numero_telefono')->all();
        self::assertSame(['0412-1234567', '0243-7654321'], $tels);

        $disc = DetalleDiscapacidadModel::query()->where('persona_id', 1)->first();
        self::assertNotNull($disc);
        self::assertSame('CONAPDIS-999', $disc->numero_carnet);

        // Con discapacidad activa, aparece el documento unificado id=10
        $recaudosConDisc = $this->catalogos->recaudosParaEmpleado(1, 1, 1, 1);
        $idsConDisc = array_column($recaudosConDisc, 'tipo_documento_id');
        self::assertContains(10, $idsConDisc);
    }

    /**
     * Valida registro de menor < 9 años sin cédula, tomo/folio opcional para no hijos y reasignación de titular.
     */
    public function testFamiliarMenorNueveTomoFolioDiscapacidadYReasignacionTitular(): void
    {
        $fechaMenor = date('Y-m-d', strtotime('-5 years'));

        // Registrar hijo menor de 9 años sin cédula y con discapacidad
        $appHijo = $this->crearMockEngine('POST', [
            'empleado_id' => '1',
            'parentesco_id' => '3',
            'primer_nombre' => 'Lucas',
            'segundo_nombre' => 'Daniel',
            'primer_apellido' => 'Mendoza',
            'segundo_apellido' => 'Rios',
            'nacionalidad' => 'V',
            'cedula' => '',
            'fecha_nacimiento' => $fechaMenor,
            'genero' => 'Masculino',
            'tomo' => '12',
            'folio' => '345',
            'tiene_discapacidad' => '1',
            'tipo_discapacidad' => 'Visual',
            'grado_discapacidad_id' => '1',
            'numero_carnet' => 'CONAPDIS-HIJO-1',
            'entidad_emisora' => 'CONAPDIS',
        ]);
        (new RegistrarFamiliarController($appHijo, $this->config, $this->catalogos, $this->gestor))->store();
        self::assertSame(201, $this->ultimoCodigo);

        $famId = (int) ($this->ultimoJson['data']['familia_id'] ?? 0);
        self::assertGreaterThan(0, $famId);

        $familiar = FamiliaresModel::query()->with('persona')->find($famId);
        self::assertNotNull($familiar);
        $personaFam = $familiar->getRelationValue('persona');
        self::assertInstanceOf(PersonasModel::class, $personaFam);
        self::assertSame(900120345, (int) $personaFam->cedula);
        self::assertTrue(DetalleDiscapacidadModel::query()->where('persona_id', (int) $familiar->persona_id)->exists());

        // Registrar Madre sin tomo ni folio (debe guardarse como 0/0)
        $appMadre = $this->crearMockEngine('POST', [
            'empleado_id' => '1',
            'parentesco_id' => '1',
            'primer_nombre' => 'Carmen',
            'segundo_nombre' => 'Elena',
            'primer_apellido' => 'Rios',
            'segundo_apellido' => 'Perez',
            'nacionalidad' => 'V',
            'cedula' => '8123456',
            'fecha_nacimiento' => '1960-04-10',
            'genero' => 'Femenino',
        ]);
        (new RegistrarFamiliarController($appMadre, $this->config, $this->catalogos, $this->gestor))->store();
        self::assertSame(201, $this->ultimoCodigo);

        $madreId = (int) ($this->ultimoJson['data']['familia_id'] ?? 0);
        $madre = FamiliaresModel::query()->find($madreId);
        self::assertNotNull($madre);
        self::assertSame(0, (int) $madre->tomo);
        self::assertSame(0, (int) $madre->folio);

        // Reasignar titular de la Madre del empleado 1 al empleado 2
        $appReasignar = $this->crearMockEngine('POST', [
            'empleado_id' => '2',
            'parentesco_id' => '1',
            'primer_nombre' => 'Carmen',
            'segundo_nombre' => 'Elena',
            'primer_apellido' => 'Rios',
            'segundo_apellido' => 'Perez',
            'nacionalidad' => 'V',
            'cedula' => '8123456',
            'fecha_nacimiento' => '1960-04-10',
            'genero' => 'Femenino',
        ]);
        (new EditarFamiliarController($appReasignar, $this->config, $this->catalogos, $this->gestor))->update((string) $madreId);
        self::assertSame(200, $this->ultimoCodigo);

        $madreActualizada = FamiliaresModel::query()->find($madreId);
        self::assertNotNull($madreActualizada);
        self::assertSame(2, (int) $madreActualizada->empleado_id);
    }

    /**
     * Valida teléfonos y discapacidad en DataTables de empleados y asignación de discapacidad en familiares.
     */
    public function testDatatablesYAsignarDiscapacidadFamiliar(): void
    {
        TelefonoPersonasModel::query()->create([
            'persona_id' => 1,
            'numero_telefono' => '0414-5556677',
            'emergencia' => true,
        ]);

        $appEmpDt = $this->crearMockEngine('POST', ['draw' => 1, 'start' => 0, 'length' => 10]);
        (new RegistrosEmpleadosController($appEmpDt, $this->config, $this->catalogos, $this->gestor))->datatable();
        self::assertSame(200, $this->ultimoCodigo);

        $filasEmp = $this->ultimoJson['data'] ?? [];
        $emp1 = array_values(array_filter($filasEmp, static fn (array $r): bool => (int) $r['empleado_id'] === 1))[0] ?? null;
        self::assertNotNull($emp1);
        self::assertCount(1, $emp1['telefonos']);
        self::assertSame('0414-5556677', $emp1['telefonos'][0]['numero_telefono']);

        // Asignar discapacidad a familiar existente (id=1)
        $appDiscFam = $this->crearMockEngine('POST', [
            'tipo_discapacidad' => 'Visual',
            'grado_discapacidad_id' => '1',
            'numero_carnet' => 'CONAPDIS-FAM-77',
            'entidad_emisora' => 'CONAPDIS',
            'fecha_emision_carnet' => '2025-03-01',
            'fecha_vencimiento_carnet' => '2030-03-01',
        ]);
        (new RegistrosFamiliaresController($appDiscFam, $this->config, $this->catalogos, $this->gestor))->asignarDiscapacidad('1');
        self::assertSame(201, $this->ultimoCodigo);

        $appFamDt = $this->crearMockEngine('POST', ['draw' => 1, 'start' => 0, 'length' => 10]);
        (new RegistrosFamiliaresController($appFamDt, $this->config, $this->catalogos, $this->gestor))->datatable();
        self::assertSame(200, $this->ultimoCodigo);

        $fam1 = $this->ultimoJson['data'][0] ?? null;
        self::assertNotNull($fam1);
        self::assertIsArray($fam1['discapacidad']);
        self::assertSame('CONAPDIS-FAM-77', $fam1['discapacidad']['numero_carnet']);
    }

    /**
     * Valida edición y reasignación de beneficiario en beneficios excluyendo el registro propio de la periodicidad.
     */
    public function testEditarYReasignarBeneficioExcluyePropioRegistro(): void
    {
        $beneficiosService = new BeneficiosService();
        $dt = $beneficiosService->registrar(1, 10, '2026-04-01', null);

        // Actualizar el mismo beneficio anual (max_anio = 1) sin chocar consigo mismo, cambiando fecha y reasignando a persona 2
        $appUpd = $this->crearMockEngine('POST', [
            'persona_id' => '2',
            'beneficio_id' => '10',
            'fecha_solicitud' => '2026-04-10',
            'fecha_aprobada' => '2026-04-15',
        ]);
        (new RegistroBeneficiosController($appUpd, $this->config, $this->catalogos, $beneficiosService, $this->gestor))
            ->update((string) $dt->dtbeneficio_id);

        self::assertSame(200, $this->ultimoCodigo);

        $actualizado = DetalleBeneficioModel::query()->find((int) $dt->dtbeneficio_id);
        self::assertNotNull($actualizado);
        self::assertSame(2, (int) $actualizado->persona_id);
        self::assertSame('2026-04-15', $actualizado->fecha_aprobada?->format('Y-m-d'));

        // Si persona 1 ya tiene ese beneficio anual en 2026, reasignar de vuelta a persona 1 debe ser bloqueado (422)
        $beneficiosService->registrar(1, 10, '2026-05-01', null);
        $appConflicto = $this->crearMockEngine('POST', [
            'persona_id' => '1',
            'beneficio_id' => '10',
            'fecha_solicitud' => '2026-04-10',
        ]);
        (new RegistroBeneficiosController($appConflicto, $this->config, $this->catalogos, $beneficiosService, $this->gestor))
            ->update((string) $dt->dtbeneficio_id);

        self::assertSame(422, $this->ultimoCodigo);
    }

    /**
     * Crea un mock de Engine con Request poblado para probar controladores aislados.
     *
     * @param array<string, mixed> $data
     * @param array<string, mixed> $query
     * @param array<string, mixed> $files
     */
    private function crearMockEngine(
        string $method,
        array $data = [],
        array $query = [],
        array $files = []
    ): Engine {
        $this->ultimoJson = null;
        $this->ultimoCodigo = 0;

        $_POST = $data;
        $_FILES = $files;
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
        $request->files = new Collection($files);
        $request->method('getUploadedFiles')->willReturn([]);

        $app = $this->getMockBuilder(Engine::class)
            ->disableOriginalConstructor()
            ->addMethods(['request', 'json', 'jsonHalt', 'redirect', 'render', 'halt'])
            ->getMock();

        $app->method('request')->willReturn($request);
        $app->method('json')->willReturnCallback(function (mixed $payload, int $code = 200): void {
            $this->ultimoJson = is_array($payload) ? $payload : [];
            $this->ultimoCodigo = $code;
        });

        return $app;
    }

    /**
     * Crea las tablas en SQLite en memoria requeridas por los módulos evaluados.
     */
    private function crearEsquema(): void
    {
        $schema = $this->capsule->getConnection()->getSchemaBuilder();

        $schema->create('estados', static function (Blueprint $t): void {
            $t->increments('estado_id');
            $t->string('estado');
            $t->boolean('activo')->default(true);
        });
        $schema->create('municipios', static function (Blueprint $t): void {
            $t->increments('municipio_id');
            $t->integer('estado_id');
            $t->string('municipio');
            $t->boolean('activo')->default(true);
        });
        $schema->create('parroquias', static function (Blueprint $t): void {
            $t->increments('parroquia_id');
            $t->integer('municipio_id');
            $t->string('parroquia');
            $t->boolean('activo')->default(true);
        });
        $schema->create('estado_civil', static function (Blueprint $t): void {
            $t->increments('estado_civil_id');
            $t->string('estado_civil');
            $t->boolean('activo')->default(true);
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
            $t->boolean('activo')->default(true);
        });
        $schema->create('carrera', static function (Blueprint $t): void {
            $t->increments('carrera_id');
            $t->string('carrera');
            $t->boolean('activo')->default(true);
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
        $schema->create('grado_discapacidad', static function (Blueprint $t): void {
            $t->increments('gradodisca_id');
            $t->string('nombre_gradodis');
            $t->integer('nivel')->nullable();
        });
        $schema->create('detalle_discapacidad', static function (Blueprint $t): void {
            $t->increments('dt_discapacidad_id');
            $t->integer('persona_id');
            $t->integer('grado_discapacidad_id');
            $t->string('numero_carnet');
            $t->string('entidad_emisora');
            $t->text('descripcion')->nullable();
            $t->string('dirigido')->nullable();
            $t->date('fecha_emision_carnet');
            $t->date('fecha_vencimiento_carnet');
        });
        $schema->create('tipo_beneficios', static function (Blueprint $t): void {
            $t->increments('tpbeneficio_id');
            $t->string('tipo_beneficio');
        });
        $schema->create('detalle_beneficio', static function (Blueprint $t): void {
            $t->increments('dtbeneficio_id');
            $t->integer('persona_id');
            $t->integer('beneficio_id');
            $t->date('fecha_solicitud');
            $t->date('fecha_aprobada')->nullable();
        });
    }

    /**
     * Inserta los catálogos y registros mínimos para las pruebas unitarias.
     */
    private function poblarDatos(): void
    {
        TipoDiscapacidadModel::query()->create(['tipo_discapacidad' => 'Visual']);
        GradoDiscapacidadModel::query()->create(['nombre_gradodis' => 'Leve', 'nivel' => 1]);
        EstadosModel::query()->create(['estado' => 'Aragua']);
        MunicipiosModel::query()->create(['estado_id' => 1, 'municipio' => 'Girardot']);
        ParroquiasModel::query()->create(['municipio_id' => 1, 'parroquia' => 'Madre Maria']);
        EstadoCivilModel::query()->create(['estado_civil' => 'Soltero(a)', 'activo' => true]);
        EstatusModel::query()->create(['estatus' => 'Empleado', 'activo' => true]);
        DependenciasModel::query()->create(['dependencia' => 'Sede Central', 'codigo' => 'CEN-01', 'activo' => true, 'estado_id' => 1]);
        DepartamentosModel::query()->create(['departamento' => 'Talento Humano', 'activo' => true]);
        CargoModel::query()->create(['cargo' => 'Analista', 'activo' => true]);
        NivelAcademicosModel::query()->create(['nivel_academico' => 'Universitario', 'jerarquia' => 2]);
        CarreraModel::query()->create(['carrera' => 'Administracion']);

        TipoParentescoModel::query()->create(['parentesco' => 'Madre']);
        TipoParentescoModel::query()->create(['parentesco' => 'Padre']);
        TipoParentescoModel::query()->create(['parentesco' => 'Hijo']);
        TipoParentescoModel::query()->create(['parentesco' => 'Hija']);

        for ($i = 1; $i <= 15; $i++) {
            $nombreDoc = match ($i) {
                1 => 'Cédula de Identidad',
                2 => 'Notificación del Contrato',
                10 => 'Certificado CONAPDIS / Informe Médico IVSS',
                13 => 'Constancia Caja de Ahorros',
                15 => 'Contrato Laboral',
                default => 'Documento ' . (string) $i,
            };
            TipoDocumentosModel::query()->create(['tipo_documentos' => $nombreDoc]);
        }
        for ($i = 1; $i <= 10; $i++) {
            TipoBeneficiosModel::query()->create(['tipo_beneficio' => 'Beneficio ' . (string) $i]);
        }

        PersonasModel::query()->create([
            'primer_nombre' => 'Carlos', 'segundo_nombre' => 'Eduardo',
            'primer_apellido' => 'Mendoza', 'segundo_apellido' => 'Rios',
            'genero' => 'Masculino', 'cedula' => 21456789, 'nacionalidad' => 'V',
            'fecha_nacimiento' => '1990-08-14', 'estado_civil_id' => 1,
        ]);
        PersonasModel::query()->create([
            'primer_nombre' => 'Maria', 'segundo_nombre' => 'Fernanda',
            'primer_apellido' => 'Lopez', 'segundo_apellido' => 'Silva',
            'genero' => 'Femenino', 'cedula' => 19876543, 'nacionalidad' => 'V',
            'fecha_nacimiento' => '1988-02-10', 'estado_civil_id' => 1,
        ]);
        PersonasModel::query()->create([
            'primer_nombre' => 'Sofia', 'segundo_nombre' => 'Valentina',
            'primer_apellido' => 'Mendoza', 'segundo_apellido' => 'Herrera',
            'genero' => 'Femenino', 'cedula' => 31245678, 'nacionalidad' => 'V',
            'fecha_nacimiento' => '2012-05-01', 'estado_civil_id' => 1,
        ]);

        EmpleadosModel::query()->create([
            'persona_id' => 1, 'dependencia_id' => 1, 'departamento_id' => 1,
            'estatus_id' => 1, 'cargo_id' => 1, 'nivel_academico_id' => 1,
            'carrera_id' => 1, 'codigo_empleado' => 'EMP-001', 'estatus_actual' => 1,
            'correo' => 'carlos@inces.gob.ve',
        ]);
        EmpleadosModel::query()->create([
            'persona_id' => 2, 'dependencia_id' => 1, 'departamento_id' => 1,
            'estatus_id' => 1, 'cargo_id' => 1, 'nivel_academico_id' => 1,
            'carrera_id' => 1, 'codigo_empleado' => 'EMP-002', 'estatus_actual' => 1,
            'correo' => 'maria@inces.gob.ve',
        ]);

        FamiliaresModel::query()->create([
            'empleado_id' => 1, 'persona_id' => 3, 'parentesco_id' => 4,
            'tomo' => 4, 'folio' => 128, 'genero' => 'Femenino',
        ]);
    }

    /**
     * Elimina de forma recursiva el directorio temporal usado durante la prueba.
     */
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
