<?php

declare(strict_types=1);

namespace Tests\Unit;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use ReflectionNamedType;

/**
 * Verifica la configuración de los 60 modelos Eloquent de Atlas
 * (tabla, PK, timestamps y relaciones) sin tocar una base de datos real.
 */
final class EloquentModelsTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        // Conexión perezosa: las relaciones solo construyen queries, nunca se ejecutan.
        $capsule = new Capsule();
        $capsule->addConnection(['driver' => 'sqlite', 'database' => ':memory:']);
        $capsule->bootEloquent();
    }

    /**
     * Clase => [tabla, pk, número de relaciones]
     *
     * @return array<string, array{0: string, 1: string|null, 2: int}>
     */
    private static function manifest(): array
    {
        return [
            'App\Model\Personas\PersonasModel' => ['personas', 'persona_id', 11],
            'App\Model\Personas\EmpleadosModel' => ['empleados', 'empleado_id', 16],
            'App\Model\Personas\DireccionModel' => ['direccion', 'direccion_id', 2],
            'App\Model\Personas\FamiliaresModel' => ['familiares', 'familia_id', 3],
            'App\Model\Personas\TallaPersonasModel' => ['talla_personas', 'talla_persona_id', 1],
            'App\Model\Personas\TelefonoPersonasModel' => ['telefono_personas', 'telefono_id', 1],
            'App\Model\Personas\DocumentosModel' => ['documentos', 'documentos_id', 3],
            'App\Model\Seguridad\UsuariosModel' => ['usuarios', 'usuario_id', 7],
            'App\Model\Seguridad\RolModel' => ['rol', 'rol_id', 2],
            'App\Model\Seguridad\PermisosModel' => ['permisos', 'permisos_id', 2],
            'App\Model\Seguridad\RolPermisoModel' => ['rol_permiso', 'rol_permiso_id', 2],
            'App\Model\Seguridad\UsuarioPermisosModel' => ['usuario_permisos', null, 2],
            'App\Model\Seguridad\AuditoriaModel' => ['auditoria', 'auditoria_id', 1],
            'App\Model\Catalogos\DependenciasModel' => ['dependencias', 'dependencia_id', 5],
            'App\Model\Catalogos\DepartamentosModel' => ['departamentos', 'departamento_id', 4],
            'App\Model\Catalogos\CargoModel' => ['cargo', 'cargo_id', 2],
            'App\Model\Catalogos\EstatusModel' => ['estatus', 'estatus_id', 1],
            'App\Model\Catalogos\NivelAcademicosModel' => ['nivel_academicos', 'nivel_academico_id', 1],
            'App\Model\Catalogos\CarreraModel' => ['carrera', 'carrera_id', 1],
            'App\Model\Catalogos\EstadoCivilModel' => ['estado_civil', 'estado_civil_id', 1],
            'App\Model\Catalogos\TipoParentescoModel' => ['tipo_parentesco', 'parentesco_id', 1],
            'App\Model\Catalogos\TipoDocumentosModel' => ['tipo_documentos', 'tpdocumentos_id', 1],
            'App\Model\Ubicacion\EstadosModel' => ['estados', 'estado_id', 3],
            'App\Model\Ubicacion\MunicipiosModel' => ['municipios', 'municipio_id', 2],
            'App\Model\Ubicacion\ParroquiasModel' => ['parroquias', 'parroquia_id', 2],
            'App\Model\Ubicacion\CiudadesModel' => ['ciudades', 'ciudad_id', 1],
            'App\Model\Movimientos\TipoMovimientosModel' => ['tipo_movimientos', 'tpmovimiento_id', 1],
            'App\Model\Movimientos\DetalleMovimientoModel' => ['detalle_movimiento', 'dtmovimiento_id', 5],
            'App\Model\Movimientos\DetalleIngresoModel' => ['detalle_ingreso', 'dtingreso_id', 1],
            'App\Model\Movimientos\TipoEgresosModel' => ['tipo_egresos', 'tpegreso_id', 1],
            'App\Model\Movimientos\DetalleEgresoModel' => ['detalle_egreso', 'dtegreso_id', 2],
            'App\Model\Movimientos\DetalleTrasladoModel' => ['detalle_traslado', 'dttraslado_id', 5],
            'App\Model\Movimientos\TipoSancionesModel' => ['tipo_sanciones', 'tpsanciones_id', 1],
            'App\Model\Movimientos\SancionesDisciplinariasModel' => ['sanciones_disciplinarias', 'sanciones_disciplinaria_id', 2],
            'App\Model\Asistencia\DetalleAsistenciaModel' => ['detalle_asistencia', 'dtasistencia_id', 1],
            'App\Model\Asistencia\AusenciaJustificadaModel' => ['ausencia_justificada', 'ausencia_id', 3],
            'App\Model\Asistencia\TipoPermisosModel' => ['tipo_permisos', 'tppermiso_id', 1],
            'App\Model\Asistencia\DetallePermisoModel' => ['detalle_permiso', 'dtpermiso_id', 2],
            'App\Model\Asistencia\TipoRepososModel' => ['tipo_reposos', 'tpreposo_id', 1],
            'App\Model\Asistencia\TipoInstitucionEmisoraModel' => ['tipo_institucion_emisora', 'tpinstitucion_emisora_id', 2],
            'App\Model\Asistencia\DetalleReposoModel' => ['detalle_reposo', 'dtreposo_id', 5],
            'App\Model\Asistencia\DetalleReposoValidadosModel' => ['detalle_reposo_validados', 'detalle_reposo_validado_id', 2],
            'App\Model\Vacaciones\PeriodoVacacionesModel' => ['periodo_vacaciones', 'periodo_vacaciones_id', 2],
            'App\Model\Vacaciones\SolicitudVacacionesModel' => ['solicitud_vacaciones', 'solicitud_vacaciones_id', 4],
            'App\Model\Vacaciones\DetalleVacacionesModel' => ['detalle_vacaciones', 'dt_vacaciones_id', 2],
            'App\Model\Vacaciones\HistorialVacacionesModel' => ['historial_vacaciones', 'historial_id', 1],
            'App\Model\Vacaciones\DetalleSolicitudReposoModel' => ['detalle_solicitud_reposo', 'dtsolicitud_reposo_id', 2],
            'App\Model\Beneficios\TipoBeneficiosModel' => ['tipo_beneficios', 'tpbeneficio_id', 1],
            'App\Model\Beneficios\DetalleBeneficioModel' => ['detalle_beneficio', 'dtbeneficio_id', 2],
            'App\Model\Beneficios\TipoPatologiasModel' => ['tipo_patologias', 'tppatologia_id', 1],
            'App\Model\Beneficios\DetalleHcmModel' => ['detalle_hcm', 'dthcm_id', 2],
            'App\Model\Beneficios\GradoDiscapacidadModel' => ['grado_discapacidad', 'gradodisca_id', 1],
            'App\Model\Beneficios\TipoDiscapacidadModel' => ['tipo_discapacidad', 'tpdiscapacidad_id', 0],
            'App\Model\Beneficios\DetalleDiscapacidadModel' => ['detalle_discapacidad', 'dt_discapacidad_id', 2],
            'App\Model\Tramites\PostulacionModel' => ['postulacion', 'postulacion_id', 5],
            'App\Model\Tramites\AccesoPostulacionModel' => ['acceso_postulacion', 'acceso_postulacion_id', 2],
            'App\Model\Tramites\TipoTramiteModel' => ['tipo_tramite', 'tptramite_id', 1],
            'App\Model\Tramites\TramiteModel' => ['tramite', 'tramite_id', 4],
            'App\Model\Tramites\TramiteDocumentoModel' => ['tramite_documento', null, 2],
            'App\Model\Tramites\SeguimientoTramiteModel' => ['seguimiento_tramite', 'seguimiento_id', 3],
        ];
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: string|null, 3: int}>
     */
    public static function modelProvider(): array
    {
        $cases = [];
        foreach (self::manifest() as $class => [$table, $pk, $relations]) {
            $cases[$class] = [$class, $table, $pk, $relations];
        }

        return $cases;
    }

    public function testManifestCoversSixtyModels(): void
    {
        self::assertCount(60, self::manifest());
    }

    /**
     * @dataProvider modelProvider
     */
    public function testModelConfigurationAndRelations(string $class, string $table, ?string $pk, int $relationCount): void
    {
        $fqcn = $class;
        self::assertStringEndsWith('Model', $fqcn);
        self::assertTrue(class_exists($fqcn), "No existe la clase $fqcn");

        $model = new $fqcn();
        self::assertInstanceOf(Model::class, $model);
        self::assertSame($table, $model->getTable());
        self::assertSame($pk, $model->getKeyName());
        self::assertFalse($model->usesTimestamps());
        self::assertSame($pk !== null, $model->getIncrementing());
        self::assertNotEmpty($model->getFillable());
        if ($pk !== null) {
            self::assertNotContains($pk, $model->getFillable(), 'La PK no debe ser fillable');
        }

        $found = 0;
        foreach ((new \ReflectionClass($fqcn))->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $type = $method->getReturnType();
            if ($method->getDeclaringClass()->getName() !== $fqcn || !$type instanceof ReflectionNamedType) {
                continue;
            }
            if (!is_subclass_of($type->getName(), Relation::class)) {
                continue;
            }
            $found++;

            $relation = $method->invoke($model);
            self::assertInstanceOf($type->getName(), $relation);
            self::assertInstanceOf(Relation::class, $relation);
            self::assertStringStartsWith('App\\Model\\', get_class($relation->getRelated()));

            $related = $relation->getRelated();
            if ($relation instanceof BelongsTo) {
                self::assertArrayHasKey($relation->getForeignKeyName(), array_flip($model->getFillable()));
                self::assertSame($related->getKeyName(), $relation->getOwnerKeyName());
            } elseif ($relation instanceof HasOneOrMany) {
                self::assertArrayHasKey($relation->getForeignKeyName(), array_flip($related->getFillable()));
                self::assertSame($model->getKeyName(), $relation->getLocalKeyName());
            } elseif ($relation instanceof BelongsToMany) {
                self::assertSame($model->getKeyName(), $relation->getParentKeyName());
                self::assertSame($related->getKeyName(), $relation->getRelatedKeyName());
            }
        }

        self::assertSame($relationCount, $found, "Número de relaciones en $class");
    }
}
