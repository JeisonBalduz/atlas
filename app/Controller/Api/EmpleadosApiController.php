<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Model\Catalogos\CargoModel;
use App\Model\Catalogos\CarreraModel;
use App\Model\Catalogos\DepartamentosModel;
use App\Model\Catalogos\DependenciasModel;
use App\Model\Catalogos\EstadoCivilModel;
use App\Model\Catalogos\EstatusModel;
use App\Model\Catalogos\NivelAcademicosModel;
use App\Model\Personas\DireccionModel;
use App\Model\Personas\EmpleadosModel;
use App\Model\Personas\PersonasModel;
use App\Model\Personas\TelefonoPersonasModel;
use App\Model\Ubicacion\ParroquiasModel;
use App\Services\CatalogosService;
use App\Services\DataTableEloquent;
use App\Services\GestorDocumentos;
use DateTimeImmutable;
use flight\Engine;
use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

/**
 * Endpoints API CRUD y DataTables 3.1.5 Server-Side para Trabajadores (100% Eloquent ORM).
 */
final class EmpleadosApiController
{
    /**
     * @param Engine<object> $app
     */
    public function __construct(
        private Engine $app,
        private GestorDocumentos $gestorDocumentos,
    ) {
    }

    /**
     * [POST|GET] Listado Server-Side para DataTables 3.1.5 usando el manejador genérico DataTableEloquent.
     */
    public function datatable(): void
    {
        $request = $this->app->request();
        $entrada = $request->method === 'GET'
            ? $request->query->getData()
            : $request->data->getData();

        try {
            $query = EmpleadosModel::query()
                ->with(['persona', 'cargo', 'departamento', 'dependencia', 'estatus', 'nivelAcademico', 'carrera'])
                ->withCount('familiares');

            $columnasOrdenables = [
                'codigo_empleado' => 'codigo_empleado',
                'cedula' => static fn (Builder $q, string $dir): Builder => $q->orderBy(
                    PersonasModel::select('cedula')->whereColumn('personas.persona_id', 'empleados.persona_id')->limit(1),
                    $dir
                ),
                'nombre_completo' => static fn (Builder $q, string $dir): Builder => $q
                    ->orderBy(PersonasModel::select('primer_apellido')->whereColumn('personas.persona_id', 'empleados.persona_id')->limit(1), $dir)
                    ->orderBy(PersonasModel::select('primer_nombre')->whereColumn('personas.persona_id', 'empleados.persona_id')->limit(1), $dir),
                'cargo' => static fn (Builder $q, string $dir): Builder => $q->orderBy(
                    CargoModel::select('cargo')->whereColumn('cargo.cargo_id', 'empleados.cargo_id')->limit(1),
                    $dir
                ),
                'departamento' => static fn (Builder $q, string $dir): Builder => $q->orderBy(
                    DepartamentosModel::select('departamento')->whereColumn('departamentos.departamento_id', 'empleados.departamento_id')->limit(1),
                    $dir
                ),
                'dependencia' => static fn (Builder $q, string $dir): Builder => $q->orderBy(
                    DependenciasModel::select('dependencia')->whereColumn('dependencias.dependencia_id', 'empleados.dependencia_id')->limit(1),
                    $dir
                ),
                'estatus' => static fn (Builder $q, string $dir): Builder => $q->orderBy(
                    EstatusModel::select('estatus')->whereColumn('estatus.estatus_id', 'empleados.estatus_id')->limit(1),
                    $dir
                ),
                'estatus_actual' => static fn (Builder $q, string $dir): Builder => $q
                    ->orderBy('estatus_actual', $dir)
                    ->orderBy(EstatusModel::select('estatus')->whereColumn('estatus.estatus_id', 'empleados.estatus_id')->limit(1), $dir),
            ];

            $columnasBuscables = [
                'codigo_empleado',
                'correo',
                'persona.primer_nombre',
                'persona.segundo_nombre',
                'persona.primer_apellido',
                'persona.segundo_apellido',
                'persona.nacionalidad',
                'persona.cedula:int',
                'cargo.cargo',
                'departamento.departamento',
                'dependencia.dependencia',
                'dependencia.codigo',
                'estatus.estatus',
            ];

            $resultado = DataTableEloquent::procesar(
                query: $query,
                entrada: $entrada,
                columnasOrdenables: $columnasOrdenables,
                columnasBuscables: $columnasBuscables,
                transformar: static function (EmpleadosModel $empleado): array {
                    $persona = $empleado->persona;
                    return [
                        'empleado_id' => (int) $empleado->empleado_id,
                        'persona_id' => (int) $empleado->persona_id,
                        'codigo_empleado' => (string) ($empleado->codigo_empleado ?? ''),
                        'cedula' => $persona instanceof PersonasModel
                            ? strtoupper((string) $persona->nacionalidad) . '-' . (string) $persona->cedula
                            : '',
                        'cedula_numero' => $persona instanceof PersonasModel ? (int) $persona->cedula : null,
                        'nombre_completo' => CatalogosService::nombreCompletoPersona($persona),
                        'cargo' => (string) ($empleado->cargo?->cargo ?? ''),
                        'departamento' => (string) ($empleado->departamento?->departamento ?? ''),
                        'dependencia' => (string) ($empleado->dependencia?->dependencia ?? ''),
                        'estatus' => (string) ($empleado->estatus?->estatus ?? ''),
                        'estatus_id' => (int) $empleado->estatus_id,
                        'estatus_actual' => (int) $empleado->estatus_actual,
                        'activo' => (int) $empleado->estatus_actual === 1,
                        'nivel_academico' => (string) ($empleado->nivelAcademico?->nivel_academico ?? ''),
                        'carrera' => (string) ($empleado->carrera?->carrera ?? ''),
                        'correo' => (string) ($empleado->correo ?? ''),
                        'total_familiares' => (int) ($empleado->getAttribute('familiares_count') ?? 0),
                    ];
                },
                aplicarFiltros: static function (Builder $q, array $filtros): void {
                    if (isset($filtros['estatus_id']) && is_numeric($filtros['estatus_id'])) {
                        $q->where('estatus_id', (int) $filtros['estatus_id']);
                    }
                    if (isset($filtros['estatus_actual']) && is_numeric($filtros['estatus_actual'])) {
                        $q->where('estatus_actual', (int) $filtros['estatus_actual']);
                    }
                    if (isset($filtros['dependencia_id']) && is_numeric($filtros['dependencia_id'])) {
                        $q->where('dependencia_id', (int) $filtros['dependencia_id']);
                    }
                    if (isset($filtros['departamento_id']) && is_numeric($filtros['departamento_id'])) {
                        $q->where('departamento_id', (int) $filtros['departamento_id']);
                    }
                },
                ordenPorDefecto: ['empleado_id', 'desc'],
            );

            $activosResumen = EmpleadosModel::query()->where('estatus_actual', 1)->count();
            $resultado['resumen'] = [
                'total' => $resultado['recordsTotal'],
                'activos' => $activosResumen,
                'inactivos' => max(0, $resultado['recordsTotal'] - $activosResumen),
            ];

            $this->app->json($resultado, 200);
        } catch (Throwable) {
            $this->app->json(
                DataTableEloquent::error($entrada, 'Error interno al consultar el listado de trabajadores.'),
                500
            );
        }
    }

    /**
     * [GET] Detalle completo de un trabajador con sus relaciones.
     */
    public function show(string $id): void
    {
        if (!ctype_digit($id) || (int) $id <= 0) {
            $this->app->json(['ok' => false, 'message' => 'Identificador de empleado inválido.'], 400);
            return;
        }

        $empleado = EmpleadosModel::query()
            ->with([
                'persona.estadoCivil',
                'persona.telefonos',
                'persona.documentos.tipoDocumento',
                'direccion.parroquia.municipio.estado',
                'dependencia',
                'departamento',
                'cargo',
                'estatus',
                'nivelAcademico',
                'carrera',
            ])
            ->withCount('familiares')
            ->find((int) $id);

        if (!$empleado instanceof EmpleadosModel || !$empleado->persona instanceof PersonasModel) {
            $this->app->json(['ok' => false, 'message' => 'Trabajador no encontrado.'], 404);
            return;
        }

        $persona = $empleado->persona;
        $telPrincipal = $persona->telefonos->firstWhere('emergencia', false);
        $telEmergencia = $persona->telefonos->firstWhere('emergencia', true);

        $this->app->json([
            'ok' => true,
            'data' => [
                'empleado_id' => (int) $empleado->empleado_id,
                'persona_id' => (int) $empleado->persona_id,
                'codigo_empleado' => $empleado->codigo_empleado,
                'nombre_completo' => CatalogosService::nombreCompletoPersona($persona),
                'correo' => $empleado->correo,
                'estatus_actual' => (int) $empleado->estatus_actual,
                'dependencia_id' => (int) $empleado->dependencia_id,
                'dependencia' => (string) ($empleado->dependencia?->dependencia ?? ''),
                'departamento_id' => (int) $empleado->departamento_id,
                'departamento' => (string) ($empleado->departamento?->departamento ?? ''),
                'cargo_id' => (int) $empleado->cargo_id,
                'cargo' => (string) ($empleado->cargo?->cargo ?? ''),
                'estatus_id' => (int) $empleado->estatus_id,
                'estatus' => (string) ($empleado->estatus?->estatus ?? ''),
                'nivel_academico_id' => (int) $empleado->nivel_academico_id,
                'nivel_academico' => (string) ($empleado->nivelAcademico?->nivel_academico ?? ''),
                'carrera_id' => $empleado->carrera_id !== null ? (int) $empleado->carrera_id : null,
                'carrera' => (string) ($empleado->carrera?->carrera ?? ''),
                'total_familiares' => (int) ($empleado->getAttribute('familiares_count') ?? 0),
                'persona' => [
                    'primer_nombre' => $persona->primer_nombre,
                    'segundo_nombre' => $persona->segundo_nombre,
                    'primer_apellido' => $persona->primer_apellido,
                    'segundo_apellido' => $persona->segundo_apellido,
                    'nacionalidad' => $persona->nacionalidad,
                    'cedula' => (int) $persona->cedula,
                    'genero' => $persona->genero,
                    'fecha_nacimiento' => $persona->fecha_nacimiento?->format('Y-m-d'),
                    'estado_civil_id' => $persona->estado_civil_id !== null ? (int) $persona->estado_civil_id : null,
                ],
                'direccion' => $empleado->direccion ? [
                    'parroquia_id' => (int) $empleado->direccion->parroquia_id,
                    'vivienda' => $empleado->direccion->vivienda,
                    'calle' => $empleado->direccion->calle,
                    'nombre_urb' => $empleado->direccion->nombre_urb,
                    'piso_edificio' => $empleado->direccion->piso_edificio,
                    'numero_vivienda' => $empleado->direccion->numero_vivienda,
                    'descripcion' => $empleado->direccion->descripcion,
                ] : null,
                'telefonos' => [
                    'principal' => $telPrincipal?->numero_telefono,
                    'emergencia' => $telEmergencia?->numero_telefono,
                ],
            ],
        ], 200);
    }

    /**
     * [GET] Lista los familiares asociados al trabajador para renderizar en la modal.
     */
    public function familiares(string $id): void
    {
        if (!ctype_digit($id) || (int) $id <= 0) {
            $this->app->json(['ok' => false, 'message' => 'Identificador de empleado inválido.'], 400);
            return;
        }

        $empleado = EmpleadosModel::query()
            ->with(['persona', 'cargo', 'departamento', 'familiares.persona', 'familiares.tipoParentesco'])
            ->find((int) $id);

        if (!$empleado instanceof EmpleadosModel) {
            $this->app->json(['ok' => false, 'message' => 'Trabajador no encontrado.'], 404);
            return;
        }

        $hoy = new DateTimeImmutable('today');
        $familiaresData = [];

        foreach ($empleado->familiares as $familiar) {
            $personaFam = $familiar->persona;
            $edad = null;
            $fechaNac = null;

            if ($personaFam instanceof PersonasModel && $personaFam->fecha_nacimiento !== null) {
                $fechaNac = $personaFam->fecha_nacimiento->format('Y-m-d');
                $edad = $personaFam->fecha_nacimiento->diff($hoy)->y;
            }

            $familiaresData[] = [
                'familia_id' => (int) $familiar->familia_id,
                'persona_id' => (int) $familiar->persona_id,
                'nacionalidad' => $personaFam instanceof PersonasModel ? (string) $personaFam->nacionalidad : '',
                'cedula_numero' => $personaFam instanceof PersonasModel ? (int) $personaFam->cedula : null,
                'cedula' => $personaFam instanceof PersonasModel
                    ? strtoupper((string) $personaFam->nacionalidad) . '-' . (string) $personaFam->cedula
                    : '',
                'nombre_completo' => CatalogosService::nombreCompletoPersona($personaFam),
                'parentesco_id' => (int) $familiar->parentesco_id,
                'parentesco' => (string) ($familiar->tipoParentesco?->parentesco ?? ''),
                'fecha_nacimiento' => $fechaNac,
                'edad' => $edad,
                'genero' => $personaFam instanceof PersonasModel && $personaFam->genero !== ''
                    ? (string) $personaFam->genero
                    : (string) ($familiar->genero ?? ''),
                'tomo' => (int) $familiar->tomo,
                'folio' => (int) $familiar->folio,
            ];
        }

        $personaTitular = $empleado->persona;

        $this->app->json([
            'ok' => true,
            'empleado' => [
                'empleado_id' => (int) $empleado->empleado_id,
                'codigo_empleado' => (string) ($empleado->codigo_empleado ?? ''),
                'cedula' => $personaTitular instanceof PersonasModel
                    ? strtoupper((string) $personaTitular->nacionalidad) . '-' . (string) $personaTitular->cedula
                    : '',
                'nombre_completo' => CatalogosService::nombreCompletoPersona($personaTitular),
                'cargo' => (string) ($empleado->cargo?->cargo ?? ''),
                'departamento' => (string) ($empleado->departamento?->departamento ?? ''),
            ],
            'total' => count($familiaresData),
            'data' => $familiaresData,
        ], 200);
    }

    /**
     * [POST] Crea un nuevo trabajador (persona, empleado, dirección, teléfonos y documento opcional).
     */
    public function store(): void
    {
        $datos = $this->app->request()->data->getData();
        $archivos = $this->app->request()->files->getData();

        $normalizados = $this->normalizarEntrada($datos, true);
        $errores = $this->validarCampos($normalizados, true);

        if ($errores !== []) {
            $this->app->json(['ok' => false, 'message' => 'Datos de entrada inválidos.', 'errors' => $errores], 422);
            return;
        }

        $existePersona = PersonasModel::query()
            ->where('nacionalidad', $normalizados['nacionalidad'])
            ->where('cedula', $normalizados['cedula'])
            ->exists();

        if ($existePersona) {
            $this->app->json([
                'ok' => false,
                'message' => 'Ya existe una persona registrada con esa nacionalidad y cédula.',
            ], 422);
            return;
        }

        $entradaArchivo = $archivos['archivo'] ?? null;
        $tipoDocumentoId = $this->extraerEnteroOpcional($datos, 'tipo_documento_id');
        if (is_array($entradaArchivo) && ($entradaArchivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE && $tipoDocumentoId === null) {
            $this->app->json(['ok' => false, 'message' => 'Debe especificar tipo_documento_id al adjuntar un archivo.'], 422);
            return;
        }

        $conexion = (new EmpleadosModel())->getConnection();

        try {
            $resultado = $conexion->transaction(function () use ($normalizados, $entradaArchivo, $tipoDocumentoId): array {
                $persona = PersonasModel::query()->create([
                    'primer_nombre' => $normalizados['primer_nombre'],
                    'segundo_nombre' => $normalizados['segundo_nombre'],
                    'primer_apellido' => $normalizados['primer_apellido'],
                    'segundo_apellido' => $normalizados['segundo_apellido'],
                    'genero' => $normalizados['genero'],
                    'cedula' => $normalizados['cedula'],
                    'nacionalidad' => $normalizados['nacionalidad'],
                    'fecha_nacimiento' => $normalizados['fecha_nacimiento'],
                    'estado_civil_id' => $normalizados['estado_civil_id'],
                ]);

                $codigoEmpleado = $normalizados['codigo_empleado'];
                if ($codigoEmpleado === null || $codigoEmpleado === '') {
                    $codigoEmpleado = 'EMP-' . str_pad((string) $normalizados['cedula'], 4, '0', STR_PAD_LEFT);
                }

                $empleado = EmpleadosModel::query()->create([
                    'persona_id' => (int) $persona->persona_id,
                    'dependencia_id' => $normalizados['dependencia_id'],
                    'departamento_id' => $normalizados['departamento_id'],
                    'estatus_id' => $normalizados['estatus_id'],
                    'cargo_id' => $normalizados['cargo_id'],
                    'nivel_academico_id' => $normalizados['nivel_academico_id'],
                    'carrera_id' => $normalizados['carrera_id'],
                    'codigo_empleado' => $codigoEmpleado,
                    'estatus_actual' => $normalizados['estatus_actual'],
                    'correo' => $normalizados['correo'],
                ]);

                if ($normalizados['parroquia_id'] !== null) {
                    DireccionModel::query()->create([
                        'empleado_id' => (int) $empleado->empleado_id,
                        'parroquia_id' => $normalizados['parroquia_id'],
                        'vivienda' => $normalizados['vivienda'],
                        'calle' => $normalizados['calle'],
                        'nombre_urb' => $normalizados['nombre_urb'],
                        'piso_edificio' => $normalizados['piso_edificio'],
                        'numero_vivienda' => $normalizados['numero_vivienda'],
                        'descripcion' => $normalizados['descripcion'],
                    ]);
                }

                if ($normalizados['telefono_principal'] !== '') {
                    TelefonoPersonasModel::query()->create([
                        'persona_id' => (int) $persona->persona_id,
                        'numero_telefono' => $normalizados['telefono_principal'],
                        'emergencia' => false,
                    ]);
                }

                if ($normalizados['telefono_emergencia'] !== '') {
                    TelefonoPersonasModel::query()->create([
                        'persona_id' => (int) $persona->persona_id,
                        'numero_telefono' => $normalizados['telefono_emergencia'],
                        'emergencia' => true,
                    ]);
                }

                $documento = null;
                if (
                    is_array($entradaArchivo)
                    && ($entradaArchivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE
                    && $tipoDocumentoId !== null
                ) {
                    $documento = $this->gestorDocumentos->subir($entradaArchivo, $tipoDocumentoId, (int) $empleado->empleado_id);
                }

                return [
                    'empleado_id' => (int) $empleado->empleado_id,
                    'persona_id' => (int) $persona->persona_id,
                    'codigo_empleado' => (string) $empleado->codigo_empleado,
                    'documento_id' => $documento?->documentos_id,
                ];
            });

            $this->app->json([
                'ok' => true,
                'message' => 'Trabajador registrado correctamente.',
                'data' => $resultado,
            ], 201);
        } catch (InvalidArgumentException $e) {
            $this->app->json(['ok' => false, 'message' => $e->getMessage()], $this->codigoHttp($e, 422));
        } catch (RuntimeException $e) {
            $this->app->json(['ok' => false, 'message' => $e->getMessage()], $this->codigoHttp($e, 500));
        } catch (Throwable) {
            $this->app->json(['ok' => false, 'message' => 'No se pudo registrar el trabajador en la base de datos.'], 500);
        }
    }

    /**
     * [PUT|POST] Actualiza un trabajador existente (datos personales, laborales, dirección, teléfonos y documento).
     */
    public function update(string $id): void
    {
        if (!ctype_digit($id) || (int) $id <= 0) {
            $this->app->json(['ok' => false, 'message' => 'Identificador de empleado inválido.'], 400);
            return;
        }

        $empleado = EmpleadosModel::query()
            ->with(['persona.telefonos', 'direccion'])
            ->find((int) $id);

        if (!$empleado instanceof EmpleadosModel || !$empleado->persona instanceof PersonasModel) {
            $this->app->json(['ok' => false, 'message' => 'Trabajador no encontrado.'], 404);
            return;
        }

        $datos = $this->app->request()->data->getData();
        $archivos = $this->app->request()->files->getData();

        $normalizados = $this->normalizarEntrada($datos, false, $empleado);
        $errores = $this->validarCampos($normalizados, false);

        if ($errores !== []) {
            $this->app->json(['ok' => false, 'message' => 'Datos de actualización inválidos.', 'errors' => $errores], 422);
            return;
        }

        $cedulaDuplicada = PersonasModel::query()
            ->where('nacionalidad', $normalizados['nacionalidad'])
            ->where('cedula', $normalizados['cedula'])
            ->where('persona_id', '!=', (int) $empleado->persona_id)
            ->exists();

        if ($cedulaDuplicada) {
            $this->app->json([
                'ok' => false,
                'message' => 'Ya existe otra persona registrada con esa nacionalidad y cédula.',
            ], 422);
            return;
        }

        $entradaArchivo = $archivos['archivo'] ?? null;
        $tipoDocumentoId = $this->extraerEnteroOpcional($datos, 'tipo_documento_id');
        $reemplazarDocumentoId = $this->extraerEnteroOpcional($datos, 'reemplazar_documento_id');

        if (
            is_array($entradaArchivo)
            && ($entradaArchivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE
            && $reemplazarDocumentoId === null
            && $tipoDocumentoId === null
        ) {
            $this->app->json([
                'ok' => false,
                'message' => 'Debe indicar tipo_documento_id o reemplazar_documento_id al adjuntar un archivo.',
            ], 422);
            return;
        }

        $conexion = $empleado->getConnection();

        try {
            $documento = $conexion->transaction(function () use (
                $empleado,
                $normalizados,
                $entradaArchivo,
                $tipoDocumentoId,
                $reemplazarDocumentoId
            ) {
                $persona = $empleado->persona;
                if (!$persona instanceof PersonasModel) {
                    return null;
                }

                $persona->fill([
                    'primer_nombre' => $normalizados['primer_nombre'],
                    'segundo_nombre' => $normalizados['segundo_nombre'],
                    'primer_apellido' => $normalizados['primer_apellido'],
                    'segundo_apellido' => $normalizados['segundo_apellido'],
                    'genero' => $normalizados['genero'],
                    'cedula' => $normalizados['cedula'],
                    'nacionalidad' => $normalizados['nacionalidad'],
                    'fecha_nacimiento' => $normalizados['fecha_nacimiento'],
                    'estado_civil_id' => $normalizados['estado_civil_id'],
                ]);
                $persona->save();

                $empleado->fill([
                    'dependencia_id' => $normalizados['dependencia_id'],
                    'departamento_id' => $normalizados['departamento_id'],
                    'estatus_id' => $normalizados['estatus_id'],
                    'cargo_id' => $normalizados['cargo_id'],
                    'nivel_academico_id' => $normalizados['nivel_academico_id'],
                    'carrera_id' => $normalizados['carrera_id'],
                    'estatus_actual' => $normalizados['estatus_actual'],
                    'correo' => $normalizados['correo'],
                ]);
                if ($normalizados['codigo_empleado'] !== null && $normalizados['codigo_empleado'] !== '') {
                    $empleado->codigo_empleado = $normalizados['codigo_empleado'];
                }
                $empleado->save();

                if ($normalizados['parroquia_id'] !== null) {
                    DireccionModel::query()->updateOrCreate(
                        ['empleado_id' => (int) $empleado->empleado_id],
                        [
                            'parroquia_id' => $normalizados['parroquia_id'],
                            'vivienda' => $normalizados['vivienda'],
                            'calle' => $normalizados['calle'],
                            'nombre_urb' => $normalizados['nombre_urb'],
                            'piso_edificio' => $normalizados['piso_edificio'],
                            'numero_vivienda' => $normalizados['numero_vivienda'],
                            'descripcion' => $normalizados['descripcion'],
                        ]
                    );
                }

                $this->sincronizarTelefono((int) $persona->persona_id, $normalizados['telefono_principal'], false);
                $this->sincronizarTelefono((int) $persona->persona_id, $normalizados['telefono_emergencia'], true);

                if (is_array($entradaArchivo) && ($entradaArchivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                    if ($reemplazarDocumentoId !== null) {
                        return $this->gestorDocumentos->reemplazar(
                            $reemplazarDocumentoId,
                            $entradaArchivo,
                            $tipoDocumentoId,
                            (int) $empleado->empleado_id
                        );
                    }
                    if ($tipoDocumentoId !== null) {
                        return $this->gestorDocumentos->subir($entradaArchivo, $tipoDocumentoId, (int) $empleado->empleado_id);
                    }
                }

                return null;
            });

            $this->app->json([
                'ok' => true,
                'message' => 'Datos del trabajador actualizados correctamente.',
                'data' => [
                    'empleado_id' => (int) $empleado->empleado_id,
                    'documento_id' => $documento?->documentos_id,
                ],
            ], 200);
        } catch (InvalidArgumentException $e) {
            $this->app->json(['ok' => false, 'message' => $e->getMessage()], $this->codigoHttp($e, 422));
        } catch (RuntimeException $e) {
            $this->app->json(['ok' => false, 'message' => $e->getMessage()], $this->codigoHttp($e, 500));
        } catch (Throwable) {
            $this->app->json(['ok' => false, 'message' => 'No se pudo actualizar el trabajador.'], 500);
        }
    }

    /**
     * [DELETE] Baja lógica del trabajador (`estatus_actual = 0`).
     */
    public function destroy(string $id): void
    {
        if (!ctype_digit($id) || (int) $id <= 0) {
            $this->app->json(['ok' => false, 'message' => 'Identificador de empleado inválido.'], 400);
            return;
        }

        $empleado = EmpleadosModel::query()->find((int) $id);
        if (!$empleado instanceof EmpleadosModel) {
            $this->app->json(['ok' => false, 'message' => 'Trabajador no encontrado.'], 404);
            return;
        }

        $empleado->estatus_actual = 0;
        $empleado->save();

        $this->app->json([
            'ok' => true,
            'message' => 'Trabajador desactivado correctamente.',
            'data' => ['empleado_id' => (int) $empleado->empleado_id, 'estatus_actual' => 0],
        ], 200);
    }

    private function codigoHttp(Throwable $e, int $defecto): int
    {
        $codigo = (int) $e->getCode();

        return $codigo >= 400 && $codigo < 600 ? $codigo : $defecto;
    }

    /**
     * @param array<string, mixed> $datos
     * @return array<string, mixed>
     */
    private function normalizarEntrada(array $datos, bool $esCreacion, ?EmpleadosModel $actual = null): array
    {
        $personaActual = $actual?->persona;
        $dirActual = $actual?->direccion;

        $primerNombre = trim((string) ($datos['primer_nombre'] ?? $datos['primerNombre'] ?? ($personaActual?->primer_nombre ?? '')));
        $segundoNombre = trim((string) ($datos['segundo_nombre'] ?? $datos['segundoNombre'] ?? ($personaActual?->segundo_nombre ?? '')));
        $primerApellido = trim((string) ($datos['primer_apellido'] ?? $datos['primerApellido'] ?? ($personaActual?->primer_apellido ?? '')));
        $segundoApellido = trim((string) ($datos['segundo_apellido'] ?? $datos['segundoApellido'] ?? ($personaActual?->segundo_apellido ?? '')));
        $nacionalidad = strtoupper(trim((string) ($datos['nacionalidad'] ?? ($personaActual?->nacionalidad ?? 'V'))));
        $cedulaRaw = trim((string) ($datos['cedula'] ?? ($personaActual?->cedula ?? '')));

        $genero = trim((string) ($datos['genero'] ?? ($personaActual?->genero ?? '')));
        if (strtoupper($genero) === 'M') {
            $genero = 'Masculino';
        } elseif (strtoupper($genero) === 'F') {
            $genero = 'Femenino';
        }

        $fechaNacimiento = trim((string) ($datos['fecha_nacimiento'] ?? ''));
        if ($fechaNacimiento === '' && isset($datos['ano'], $datos['meses'], $datos['dia']) && is_numeric($datos['ano']) && is_numeric($datos['meses']) && is_numeric($datos['dia'])) {
            $fechaNacimiento = sprintf('%04d-%02d-%02d', (int) $datos['ano'], (int) $datos['meses'], (int) $datos['dia']);
        } elseif ($fechaNacimiento === '' && $personaActual?->fecha_nacimiento !== null) {
            $fechaNacimiento = $personaActual->fecha_nacimiento->format('Y-m-d');
        }

        $telPrincipal = trim((string) ($datos['telefono_principal'] ?? ''));
        if ($telPrincipal === '' && isset($datos['linea'], $datos['telefono'])) {
            $telPrincipal = trim((string) $datos['linea']) . trim((string) $datos['telefono']);
        }

        $carreraRaw = $datos['carrera_id'] ?? $datos['carrera'] ?? ($actual?->carrera_id ?? null);
        $carreraId = is_numeric($carreraRaw) && (int) $carreraRaw > 0 ? (int) $carreraRaw : null;

        $parroquiaRaw = $datos['parroquia_id'] ?? $datos['parroquia'] ?? ($dirActual?->parroquia_id ?? null);
        $parroquiaId = is_numeric($parroquiaRaw) && (int) $parroquiaRaw > 0 ? (int) $parroquiaRaw : null;

        return [
            'primer_nombre' => $primerNombre,
            'segundo_nombre' => $segundoNombre,
            'primer_apellido' => $primerApellido,
            'segundo_apellido' => $segundoApellido,
            'nacionalidad' => $nacionalidad,
            'cedula' => ctype_digit($cedulaRaw) ? (int) $cedulaRaw : 0,
            'genero' => $genero,
            'fecha_nacimiento' => $fechaNacimiento,
            'estado_civil_id' => $this->extraerEnteroOpcional($datos, 'estado_civil_id')
                ?? $this->extraerEnteroOpcional($datos, 'civil')
                ?? ($personaActual?->estado_civil_id !== null ? (int) $personaActual->estado_civil_id : null),
            'dependencia_id' => $this->extraerEnteroOpcional($datos, 'dependencia_id')
                ?? $this->extraerEnteroOpcional($datos, 'dependencia')
                ?? ($actual?->dependencia_id),
            'departamento_id' => $this->extraerEnteroOpcional($datos, 'departamento_id')
                ?? $this->extraerEnteroOpcional($datos, 'departamento')
                ?? ($actual?->departamento_id),
            'cargo_id' => $this->extraerEnteroOpcional($datos, 'cargo_id')
                ?? $this->extraerEnteroOpcional($datos, 'cargo')
                ?? ($actual?->cargo_id),
            'estatus_id' => $this->extraerEnteroOpcional($datos, 'estatus_id')
                ?? $this->extraerEnteroOpcional($datos, 'estatus')
                ?? ($actual?->estatus_id),
            'nivel_academico_id' => $this->extraerEnteroOpcional($datos, 'nivel_academico_id')
                ?? $this->extraerEnteroOpcional($datos, 'nivelAcademico')
                ?? ($actual?->nivel_academico_id),
            'carrera_id' => $carreraId,
            'codigo_empleado' => isset($datos['codigo_empleado']) ? trim((string) $datos['codigo_empleado']) : ($actual?->codigo_empleado),
            'estatus_actual' => isset($datos['estatus_actual']) && is_numeric($datos['estatus_actual'])
                ? ((int) $datos['estatus_actual'] === 0 ? 0 : 1)
                : ($esCreacion ? 1 : (int) ($actual?->estatus_actual ?? 1)),
            'correo' => isset($datos['correo']) && trim((string) $datos['correo']) !== ''
                ? trim((string) $datos['correo'])
                : ($esCreacion ? null : $actual?->correo),
            'parroquia_id' => $parroquiaId,
            'vivienda' => trim((string) ($datos['vivienda'] ?? ($dirActual?->vivienda ?? 'Casa'))),
            'calle' => trim((string) ($datos['calle'] ?? ($dirActual?->calle ?? ''))),
            'nombre_urb' => trim((string) ($datos['nombre_urb'] ?? ($dirActual?->nombre_urb ?? ''))),
            'piso_edificio' => trim((string) ($datos['piso_edificio'] ?? ($dirActual?->piso_edificio ?? 'N/A'))),
            'numero_vivienda' => trim((string) ($datos['numero_vivienda'] ?? ($dirActual?->numero_vivienda ?? 'S/N'))),
            'descripcion' => isset($datos['descripcion']) ? trim((string) $datos['descripcion']) : ($dirActual?->descripcion),
            'telefono_principal' => $telPrincipal,
            'telefono_emergencia' => trim((string) ($datos['telefono_emergencia'] ?? '')),
        ];
    }

    /**
     * @param array<string, mixed> $n
     * @return array<string, string>
     */
    private function validarCampos(array $n, bool $esCreacion): array
    {
        $errores = [];

        if ($n['primer_nombre'] === '') {
            $errores['primer_nombre'] = 'El primer nombre es obligatorio.';
        }
        if ($n['primer_apellido'] === '') {
            $errores['primer_apellido'] = 'El primer apellido es obligatorio.';
        }
        if (!in_array($n['nacionalidad'], ['V', 'E'], true)) {
            $errores['nacionalidad'] = 'La nacionalidad debe ser V o E.';
        }
        if ((int) $n['cedula'] <= 0) {
            $errores['cedula'] = 'La cédula debe ser un número entero positivo.';
        }
        if ($n['genero'] === '') {
            $errores['genero'] = 'El género es obligatorio.';
        }
        if (!$this->esFechaValida((string) $n['fecha_nacimiento'])) {
            $errores['fecha_nacimiento'] = 'La fecha de nacimiento es inválida.';
        }

        if ($n['estado_civil_id'] !== null && !EstadoCivilModel::query()->whereKey($n['estado_civil_id'])->exists()) {
            $errores['estado_civil_id'] = 'El estado civil indicado no existe.';
        }
        if ($n['dependencia_id'] === null || !DependenciasModel::query()->whereKey($n['dependencia_id'])->exists()) {
            $errores['dependencia_id'] = 'La dependencia indicada no es válida.';
        }
        if ($n['departamento_id'] === null || !DepartamentosModel::query()->whereKey($n['departamento_id'])->exists()) {
            $errores['departamento_id'] = 'El departamento indicado no es válido.';
        }
        if ($n['cargo_id'] === null || !CargoModel::query()->whereKey($n['cargo_id'])->exists()) {
            $errores['cargo_id'] = 'El cargo indicado no es válido.';
        }
        if ($n['estatus_id'] === null || !EstatusModel::query()->whereKey($n['estatus_id'])->exists()) {
            $errores['estatus_id'] = 'El estatus laboral indicado no es válido.';
        }
        if ($n['nivel_academico_id'] === null || !NivelAcademicosModel::query()->whereKey($n['nivel_academico_id'])->exists()) {
            $errores['nivel_academico_id'] = 'El nivel académico indicado no es válido.';
        }
        if ($n['carrera_id'] !== null && !CarreraModel::query()->whereKey($n['carrera_id'])->exists()) {
            $errores['carrera_id'] = 'La carrera indicada no existe.';
        }
        if ($n['parroquia_id'] !== null && !ParroquiasModel::query()->whereKey($n['parroquia_id'])->exists()) {
            $errores['parroquia_id'] = 'La parroquia indicada no existe.';
        }
        if ($esCreacion && $n['estado_civil_id'] === null) {
            $errores['estado_civil_id'] = 'El estado civil es obligatorio.';
        }

        return $errores;
    }

    private function sincronizarTelefono(int $personaId, string $numero, bool $emergencia): void
    {
        if ($numero === '') {
            return;
        }

        TelefonoPersonasModel::query()->updateOrCreate(
            ['persona_id' => $personaId, 'emergencia' => $emergencia],
            ['numero_telefono' => $numero]
        );
    }

    /**
     * @param array<string, mixed> $datos
     */
    private function extraerEnteroOpcional(array $datos, string $clave): ?int
    {
        if (!isset($datos[$clave]) || $datos[$clave] === '' || !is_numeric($datos[$clave])) {
            return null;
        }

        $valor = (int) $datos[$clave];

        return $valor > 0 ? $valor : null;
    }

    private function esFechaValida(string $fecha): bool
    {
        if ($fecha === '') {
            return false;
        }

        $dt = DateTimeImmutable::createFromFormat('Y-m-d', $fecha);

        return $dt instanceof DateTimeImmutable && $dt->format('Y-m-d') === $fecha;
    }
}
