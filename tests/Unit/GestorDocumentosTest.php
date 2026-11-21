<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Model\Catalogos\TipoDocumentosModel;
use App\Model\Personas\DocumentosModel;
use App\Model\Personas\EmpleadosModel;
use App\Model\Personas\FamiliaresModel;
use App\Model\Personas\PersonasModel;
use App\Services\GestorDocumentos;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use Throwable;

final class GestorDocumentosTest extends TestCase
{
    private Capsule $capsule;
    private string $tempDir;
    private string $raizDocs;
    private GestorDocumentos $gestor;

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

        $schema->create('personas', static function (Blueprint $table): void {
            $table->increments('persona_id');
            $table->string('primer_nombre');
            $table->string('segundo_nombre')->default('');
            $table->string('primer_apellido');
            $table->string('segundo_apellido')->default('');
            $table->string('genero')->default('Masculino');
            $table->integer('cedula');
            $table->string('nacionalidad')->default('V');
            $table->date('fecha_nacimiento');
            $table->integer('estado_civil_id')->nullable();
        });

        $schema->create('empleados', static function (Blueprint $table): void {
            $table->increments('empleado_id');
            $table->integer('persona_id');
            $table->integer('dependencia_id')->default(1);
            $table->integer('departamento_id')->default(1);
            $table->integer('estatus_id')->default(1);
            $table->integer('cargo_id')->default(1);
            $table->integer('nivel_academico_id')->default(1);
            $table->integer('carrera_id')->nullable();
            $table->string('codigo_empleado')->nullable();
            $table->integer('estatus_actual')->default(1);
            $table->string('correo')->nullable();
        });

        $schema->create('familiares', static function (Blueprint $table): void {
            $table->increments('familia_id');
            $table->integer('empleado_id');
            $table->integer('persona_id');
            $table->integer('parentesco_id');
            $table->integer('tomo')->default(1);
            $table->integer('folio')->default(1);
            $table->string('genero')->nullable();
        });

        $schema->create('tipo_documentos', static function (Blueprint $table): void {
            $table->increments('tpdocumentos_id');
            $table->string('tipo_documentos');
        });

        $schema->create('documentos', static function (Blueprint $table): void {
            $table->increments('documentos_id');
            $table->integer('tipo_documento_id');
            $table->integer('persona_id');
            $table->string('codigo')->nullable();
            $table->string('ruta');
            $table->string('estatus_documento')->nullable();
            $table->string('nombre_original');
            $table->integer('tamano');
            $table->string('formato');
            $table->dateTime('fecha_subida')->nullable();
        });

        $schema->create('tramite', static function (Blueprint $table): void {
            $table->increments('tramite_id');
            $table->integer('persona_id');
            $table->integer('tptramite_id')->default(1);
            $table->string('estatus')->default('Pendiente');
            $table->dateTime('fecha_creacion')->nullable();
        });

        $schema->create('tramite_documento', static function (Blueprint $table): void {
            $table->integer('tramite_id');
            $table->integer('documento_id');
        });

        $this->seedBase();

        $this->tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'atlas_docs_test_' . bin2hex(random_bytes(5));
        $this->raizDocs = $this->tempDir . DIRECTORY_SEPARATOR . 'DocumentosInces';
        mkdir($this->tempDir, 0775, true);

        $this->gestor = new GestorDocumentos(
            $this->capsule->getConnection(),
            $this->raizDocs,
            5242880
        );
    }

    protected function tearDown(): void
    {
        $this->eliminarDirectorioRecursivo($this->tempDir);
        parent::tearDown();
    }

    public function testSubirDocumentoPersonalCreaJerarquiaYPersisteEnEloquent(): void
    {
        $pdfTmp = $this->crearPdfValido('cedula_titular.pdf');

        $doc = $this->gestor->subir(
            $this->archivoLocal($pdfTmp, 'Mi Cédula (2026).pdf'),
            1,
            1,
            null,
            false
        );

        self::assertGreaterThan(0, $doc->documentos_id);
        self::assertSame(1, $doc->tipo_documento_id);
        self::assertSame(1, $doc->persona_id);
        self::assertSame('pdf', $doc->formato);
        self::assertSame('Vigente', $doc->estatus_documento);
        self::assertStringStartsWith('V12345678-E1/personales/01-cedula-de-identidad/cedula-de-identidad_', $doc->ruta);
        self::assertStringEndsWith('.pdf', $doc->ruta);
        self::assertFileExists($this->gestor->rutaAbsoluta($doc));
    }

    public function testSubirDocumentoFamiliarCreaSubcarpetaFamiliar(): void
    {
        $pngTmp = $this->crearPngValido('partida.png');

        $doc = $this->gestor->subir(
            $this->archivoLocal($pngTmp, 'Partida Hijo.png'),
            3,
            1,
            1,
            false
        );

        self::assertSame(2, $doc->persona_id);
        self::assertStringStartsWith(
            'V12345678-E1/familiares/V30111222-P2/03-partida-de-nacimiento-de-hijos-hijas/partida-de-nacimiento-de-hijos-h_',
            $doc->ruta
        );
        self::assertFileExists($this->gestor->rutaAbsoluta($doc));
    }

    public function testResolucionDinamicaDeNuevoTipoDocumentoSinHardcodear(): void
    {
        $nuevoTipo = TipoDocumentosModel::query()->create([
            'tipo_documentos' => 'Solvencia Académica Especial',
        ]);

        $pdfTmp = $this->crearPdfValido('solvencia.pdf');
        $doc = $this->gestor->subir(
            $this->archivoLocal($pdfTmp, 'solvencia.pdf'),
            (int) $nuevoTipo->tpdocumentos_id,
            1,
            null,
            false
        );

        $prefijoEsperado = sprintf(
            'V12345678-E1/personales/%02d-solvencia-academica-especial/',
            $nuevoTipo->tpdocumentos_id
        );
        self::assertStringStartsWith($prefijoEsperado, $doc->ruta);
    }

    public function testRechazaMimeFalsificadoDobleExtensionTamanoYPropietarioInvalido(): void
    {
        // 1. Archivo PHP renombrado a .pdf (MIME falso)
        $falsoPdf = $this->tempDir . DIRECTORY_SEPARATOR . 'falso.pdf';
        file_put_contents($falsoPdf, "<?php echo 'hack';");
        try {
            $this->gestor->subir($this->archivoLocal($falsoPdf, 'falso.pdf'), 1, 1, null, false);
            self::fail('Debió rechazar un PDF con contenido PHP.');
        } catch (InvalidArgumentException $e) {
            self::assertSame(422, $e->getCode());
        }

        // 2. Doble extensión peligrosa
        $pdfReal = $this->crearPdfValido('ok.pdf');
        try {
            $this->gestor->subir($this->archivoLocal($pdfReal, 'shell.php.pdf'), 1, 1, null, false);
            self::fail('Debió rechazar doble extensión peligrosa.');
        } catch (InvalidArgumentException $e) {
            self::assertSame(422, $e->getCode());
        }

        // 3. Tamaño excedido
        $gestorChico = new GestorDocumentos($this->capsule->getConnection(), $this->raizDocs, 10);
        try {
            $gestorChico->subir($this->archivoLocal($pdfReal, 'grande.pdf'), 1, 1, null, false);
            self::fail('Debió rechazar archivo mayor al tamaño permitido.');
        } catch (InvalidArgumentException $e) {
            self::assertSame(422, $e->getCode());
        }

        // 4. Tipo de documento inexistente
        try {
            $this->gestor->subir($this->archivoLocal($pdfReal, 'ok.pdf'), 9999, 1, null, false);
            self::fail('Debió lanzar InvalidArgumentException 404.');
        } catch (InvalidArgumentException $e) {
            self::assertSame(404, $e->getCode());
        }

        // 5. Familiar perteneciente a otro trabajador
        try {
            $this->gestor->subir($this->archivoLocal($pdfReal, 'ok.pdf'), 1, 2, 1, false);
            self::fail('Debió lanzar InvalidArgumentException 404.');
        } catch (InvalidArgumentException $e) {
            self::assertSame(404, $e->getCode());
        }
    }

    public function testRollbackCompensatorioEliminaArchivoSiFallaBaseDeDatos(): void
    {
        $pdfTmp = $this->crearPdfValido('rollback.pdf');

        $this->capsule->getConnection()->statement(
            "CREATE TRIGGER trg_fail_insert BEFORE INSERT ON documentos
             BEGIN
                 SELECT RAISE(ABORT, 'Fallo simulado de base de datos');
             END"
        );

        try {
            $this->gestor->subir($this->archivoLocal($pdfTmp, 'rollback.pdf'), 1, 1, null, false);
            self::fail('Debió propagar la excepción de BD.');
        } catch (Throwable $e) {
            self::assertStringContainsString('Fallo simulado de base de datos', $e->getMessage());
        } finally {
            $this->capsule->getConnection()->statement('DROP TRIGGER IF EXISTS trg_fail_insert');
        }

        self::assertSame(0, DocumentosModel::query()->count());
        self::assertSame([], $this->listarArchivosEn($this->raizDocs));
    }

    public function testReemplazarConservaArchivoAnteriorEHistorialDeMasViejoAMasNuevo(): void
    {
        $pdfTmp = $this->crearPdfValido('v1.pdf');
        $docV1 = $this->gestor->subir($this->archivoLocal($pdfTmp, 'v1.pdf'), 1, 1, null, false);
        $rutaVieja = $this->gestor->rutaAbsoluta($docV1);

        $pngTmp = $this->crearPngValido('v2.png');
        $docV2 = $this->gestor->reemplazar(
            (int) $docV1->documentos_id,
            $this->archivoLocal($pngTmp, 'v2.png'),
            2,
            null,
            null,
            false
        );

        $rutaNueva = $this->gestor->rutaAbsoluta($docV2);
        $docV1Actualizado = DocumentosModel::query()->find($docV1->documentos_id);

        self::assertSame('Histórico', $docV1Actualizado?->estatus_documento);
        self::assertSame('Vigente', $docV2->estatus_documento);
        self::assertNotSame($docV1->documentos_id, $docV2->documentos_id);
        self::assertSame(2, $docV2->tipo_documento_id);
        self::assertSame('png', $docV2->formato);
        self::assertSame('v2.png', $docV2->nombre_original);
        self::assertFileExists($rutaVieja);
        self::assertFileExists($rutaNueva);
        self::assertCount(2, $this->listarArchivosEn($this->raizDocs));

        $historial = DocumentosModel::query()->where('persona_id', 1)->orderBy('documentos_id', 'asc')->get();
        self::assertCount(2, $historial);
        self::assertSame('v1.pdf', $historial[0]->nombre_original);
        self::assertSame('v2.png', $historial[1]->nombre_original);
    }

    public function testRutaAbsolutaBloqueaPathTraversal(): void
    {
        $doc = new DocumentosModel([
            'tipo_documento_id' => 1,
            'persona_id' => 1,
            'ruta' => '../../app/config/config.php',
            'nombre_original' => 'config.php',
            'tamano' => 100,
            'formato' => 'pdf',
        ]);

        $this->expectException(RuntimeException::class);
        $this->gestor->rutaAbsoluta($doc);
    }

    /**
     * @return array{name: string, tmp_name: string, size: int, error: int}
     */
    private function archivoLocal(string $ruta, ?string $nombreOriginal = null): array
    {
        return [
            'name' => $nombreOriginal ?? basename($ruta),
            'tmp_name' => $ruta,
            'size' => is_file($ruta) ? (int) (filesize($ruta) ?: 0) : 0,
            'error' => UPLOAD_ERR_OK,
        ];
    }

    private function seedBase(): void
    {
        PersonasModel::query()->create([
            'primer_nombre' => 'Carlos',
            'segundo_nombre' => 'Eduardo',
            'primer_apellido' => 'Mendoza',
            'segundo_apellido' => 'Ríos',
            'genero' => 'Masculino',
            'cedula' => 12345678,
            'nacionalidad' => 'V',
            'fecha_nacimiento' => '1990-05-10',
        ]);

        PersonasModel::query()->create([
            'primer_nombre' => 'Sofía',
            'segundo_nombre' => 'Valentina',
            'primer_apellido' => 'Mendoza',
            'segundo_apellido' => 'Herrera',
            'genero' => 'Femenino',
            'cedula' => 30111222,
            'nacionalidad' => 'V',
            'fecha_nacimiento' => '2014-03-15',
        ]);

        PersonasModel::query()->create([
            'primer_nombre' => 'María',
            'segundo_nombre' => 'Fernanda',
            'primer_apellido' => 'López',
            'segundo_apellido' => 'Silva',
            'genero' => 'Femenino',
            'cedula' => 19876543,
            'nacionalidad' => 'V',
            'fecha_nacimiento' => '1988-09-20',
        ]);

        EmpleadosModel::query()->create([
            'persona_id' => 1,
            'codigo_empleado' => 'EMP-001',
            'estatus_actual' => 1,
        ]);

        EmpleadosModel::query()->create([
            'persona_id' => 3,
            'codigo_empleado' => 'EMP-002',
            'estatus_actual' => 1,
        ]);

        FamiliaresModel::query()->create([
            'empleado_id' => 1,
            'persona_id' => 2,
            'parentesco_id' => 4,
            'tomo' => 2,
            'folio' => 45,
            'genero' => 'Femenino',
        ]);

        TipoDocumentosModel::query()->create(['tipo_documentos' => 'Cédula de Identidad']);
        TipoDocumentosModel::query()->create(['tipo_documentos' => 'Partida de Nacimiento del Titular']);
        TipoDocumentosModel::query()->create(['tipo_documentos' => 'Partida de Nacimiento de Hijos / Hijas']);
    }

    private function crearPdfValido(string $nombre): string
    {
        $ruta = $this->tempDir . DIRECTORY_SEPARATOR . $nombre;
        file_put_contents($ruta, "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\ntrailer\n<<>>\n%%EOF\n");
        return $ruta;
    }

    private function crearPngValido(string $nombre): string
    {
        $ruta = $this->tempDir . DIRECTORY_SEPARATOR . $nombre;
        $pngBase64 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=';
        file_put_contents($ruta, (string) base64_decode($pngBase64));
        return $ruta;
    }

    /**
     * @return list<string>
     */
    private function listarArchivosEn(string $directorio): array
    {
        if (!is_dir($directorio)) {
            return [];
        }

        $archivos = [];
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directorio));
        foreach ($it as $item) {
            if ($item->isFile()) {
                $archivos[] = $item->getPathname();
            }
        }

        return $archivos;
    }

    private function eliminarDirectorioRecursivo(string $directorio): void
    {
        if (!is_dir($directorio)) {
            return;
        }

        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directorio, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($it as $item) {
            if ($item->isDir()) {
                @rmdir($item->getPathname());
            } else {
                @unlink($item->getPathname());
            }
        }
        @rmdir($directorio);
    }
}
