<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Model\Catalogos\CargoModel;
use App\Model\Personas\EmpleadosModel;
use App\Model\Personas\PersonasModel;
use App\Services\DataTableEloquent;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Schema\Blueprint;
use PHPUnit\Framework\TestCase;

final class DataTableEloquentTest extends TestCase
{
    private Capsule $capsule;

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

        $schema = $this->capsule->getConnection()->getSchemaBuilder();

        $schema->create('cargo', static function (Blueprint $t): void {
            $t->increments('cargo_id');
            $t->string('cargo');
            $t->boolean('activo')->default(true);
        });

        $schema->create('personas', static function (Blueprint $t): void {
            $t->increments('persona_id');
            $t->string('primer_nombre');
            $t->string('segundo_nombre')->default('');
            $t->string('primer_apellido');
            $t->string('segundo_apellido')->default('');
            $t->string('genero')->default('Masculino');
            $t->integer('cedula');
            $t->string('nacionalidad')->default('V');
            $t->date('fecha_nacimiento');
            $t->integer('estado_civil_id')->nullable();
        });

        $schema->create('empleados', static function (Blueprint $t): void {
            $t->increments('empleado_id');
            $t->integer('persona_id');
            $t->integer('dependencia_id')->default(1);
            $t->integer('departamento_id')->default(1);
            $t->integer('estatus_id')->default(1);
            $t->integer('cargo_id');
            $t->integer('nivel_academico_id')->default(1);
            $t->integer('carrera_id')->nullable();
            $t->string('codigo_empleado');
            $t->integer('estatus_actual')->default(1);
            $t->string('correo')->nullable();
        });

        $schema->create('familiares', static function (Blueprint $t): void {
            $t->increments('familia_id');
            $t->integer('empleado_id');
            $t->integer('persona_id');
            $t->integer('parentesco_id')->default(1);
            $t->integer('tomo')->default(1);
            $t->integer('folio')->default(1);
            $t->string('genero')->nullable();
        });

        CargoModel::query()->create(['cargo' => 'Analista', 'activo' => true]);
        CargoModel::query()->create(['cargo' => 'Coordinador', 'activo' => true]);

        PersonasModel::query()->create([
            'primer_nombre' => 'Ana', 'primer_apellido' => 'Zapata',
            'cedula' => 10111222, 'nacionalidad' => 'V', 'fecha_nacimiento' => '1991-01-01',
        ]);
        PersonasModel::query()->create([
            'primer_nombre' => 'Luis', 'primer_apellido' => 'Borges',
            'cedula' => 20333444, 'nacionalidad' => 'V', 'fecha_nacimiento' => '1992-02-02',
        ]);
        PersonasModel::query()->create([
            'primer_nombre' => 'Carla', 'primer_apellido' => 'Mendoza',
            'cedula' => 30555666, 'nacionalidad' => 'E', 'fecha_nacimiento' => '1993-03-03',
        ]);

        EmpleadosModel::query()->create(['persona_id' => 1, 'cargo_id' => 1, 'codigo_empleado' => 'EMP-001', 'estatus_actual' => 1]);
        EmpleadosModel::query()->create(['persona_id' => 2, 'cargo_id' => 2, 'codigo_empleado' => 'EMP-002', 'estatus_actual' => 1]);
        EmpleadosModel::query()->create(['persona_id' => 3, 'cargo_id' => 1, 'codigo_empleado' => 'EMP-003', 'estatus_actual' => 0]);
    }

    public function testPaginacionConteosConWithCountYTransformador(): void
    {
        $resultado = DataTableEloquent::procesar(
            query: EmpleadosModel::query()->with(['persona', 'cargo'])->withCount('familiares'),
            entrada: [
                'draw' => '4',
                'start' => '1',
                'length' => '1',
                'order' => [['column' => '0', 'dir' => 'asc']],
                'columns' => [0 => ['data' => 'codigo_empleado']],
            ],
            columnasOrdenables: ['codigo_empleado' => 'codigo_empleado'],
            transformar: static fn (EmpleadosModel $e): array => [
                'codigo' => $e->codigo_empleado,
                'nombre' => $e->persona?->primer_nombre,
                'familiares' => (int) $e->getAttribute('familiares_count'),
            ],
        );

        self::assertSame(4, $resultado['draw']);
        self::assertSame(3, $resultado['recordsTotal']);
        self::assertSame(3, $resultado['recordsFiltered']);
        self::assertCount(1, $resultado['data']);
        self::assertSame('EMP-002', $resultado['data'][0]['codigo']);
    }

    public function testBusquedaEnRelacionesFiltrosYListaBlancaContraInyeccion(): void
    {
        $resultado = DataTableEloquent::procesar(
            query: EmpleadosModel::query()->with(['persona', 'cargo']),
            entrada: [
                'draw' => 7,
                'start' => 0,
                'length' => 500,
                'search' => ['value' => 'borges coordinador'],
                'order' => [['column' => 0, 'dir' => 'ASC; DROP TABLE empleados']],
                'columns' => [0 => ['data' => 'columna_inventada; DROP TABLE empleados']],
                'estatus_actual' => '1',
            ],
            columnasOrdenables: [
                'codigo_empleado' => 'codigo_empleado',
                'apellido' => static fn (Builder $q, string $dir): Builder => $q->orderBy(
                    PersonasModel::select('primer_apellido')->whereColumn('personas.persona_id', 'empleados.persona_id')->limit(1),
                    $dir
                ),
            ],
            columnasBuscables: ['codigo_empleado', 'persona.primer_apellido', 'persona.cedula:int', 'cargo.cargo'],
            aplicarFiltros: static function (Builder $q, array $entrada): void {
                if (isset($entrada['estatus_actual']) && is_numeric($entrada['estatus_actual'])) {
                    $q->where('estatus_actual', (int) $entrada['estatus_actual']);
                }
            },
            ordenPorDefecto: ['empleado_id', 'desc'],
        );

        self::assertSame(7, $resultado['draw']);
        self::assertSame(3, $resultado['recordsTotal']);
        self::assertSame(1, $resultado['recordsFiltered']);
        self::assertSame('EMP-002', $resultado['data'][0]['codigo_empleado']);
    }

    public function testBusquedaPorCedulaEnteraYContratoDeError(): void
    {
        $resultado = DataTableEloquent::procesar(
            query: EmpleadosModel::query()->with('persona'),
            entrada: [
                'draw' => 2,
                'search' => ['value' => '30555'],
            ],
            columnasBuscables: ['persona.cedula:int'],
        );

        self::assertSame(3, $resultado['recordsTotal']);
        self::assertSame(1, $resultado['recordsFiltered']);
        self::assertSame('EMP-003', $resultado['data'][0]['codigo_empleado']);

        self::assertSame([
            'draw' => 9,
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => [],
            'error' => 'Fallo controlado',
        ], DataTableEloquent::error(['draw' => 9], 'Fallo controlado'));
    }
}
