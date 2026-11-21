<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Model\Catalogos\EstadoCivilModel;
use App\Model\Catalogos\TipoParentescoModel;
use App\Model\Personas\EmpleadosModel;
use App\Model\Personas\FamiliaresModel;
use App\Model\Personas\PersonasModel;
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
 * Endpoints API CRUD y DataTables 3.1.5 Server-Side para Carga Familiar (100% Eloquent ORM).
 */
final class FamiliaresApiController
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

        $hoy = new DateTimeImmutable('today');

        try {
            $query = FamiliaresModel::query()
                ->with(['persona', 'tipoParentesco', 'empleado.persona', 'empleado.cargo']);

            $columnasOrdenables = [
                'cedula_titular' => static fn (Builder $q, string $dir): Builder => $q->orderBy(
                    PersonasModel::select('cedula')
                        ->join('empleados', 'empleados.persona_id', '=', 'personas.persona_id')
                        ->whereColumn('empleados.empleado_id', 'familiares.empleado_id')
                        ->limit(1),
                    $dir
                ),
                'empleado_titular' => static fn (Builder $q, string $dir): Builder => $q->orderBy(
                    PersonasModel::select('primer_apellido')
                        ->join('empleados', 'empleados.persona_id', '=', 'personas.persona_id')
                        ->whereColumn('empleados.empleado_id', 'familiares.empleado_id')
                        ->limit(1),
                    $dir
                ),
                'cedula_familiar' => static fn (Builder $q, string $dir): Builder => $q->orderBy(
                    PersonasModel::select('cedula')->whereColumn('personas.persona_id', 'familiares.persona_id')->limit(1),
                    $dir
                ),
                'nombre_familiar' => static fn (Builder $q, string $dir): Builder => $q
                    ->orderBy(PersonasModel::select('primer_apellido')->whereColumn('personas.persona_id', 'familiares.persona_id')->limit(1), $dir)
                    ->orderBy(PersonasModel::select('primer_nombre')->whereColumn('personas.persona_id', 'familiares.persona_id')->limit(1), $dir),
                'parentesco' => static fn (Builder $q, string $dir): Builder => $q->orderBy(
                    TipoParentescoModel::select('parentesco')->whereColumn('tipo_parentesco.parentesco_id', 'familiares.parentesco_id')->limit(1),
                    $dir
                ),
                'edad' => static fn (Builder $q, string $dir): Builder => $q->orderBy(
                    PersonasModel::select('fecha_nacimiento')->whereColumn('personas.persona_id', 'familiares.persona_id')->limit(1),
                    $dir === 'asc' ? 'desc' : 'asc'
                ),
                'genero' => static fn (Builder $q, string $dir): Builder => $q->orderBy(
                    PersonasModel::select('genero')->whereColumn('personas.persona_id', 'familiares.persona_id')->limit(1),
                    $dir
                ),
            ];

            $columnasBuscables = [
                'persona.primer_nombre',
                'persona.segundo_nombre',
                'persona.primer_apellido',
                'persona.segundo_apellido',
                'persona.cedula:int',
                'empleado.persona.primer_nombre',
                'empleado.persona.primer_apellido',
                'empleado.persona.cedula:int',
                'tipoParentesco.parentesco',
            ];

            $resultado = DataTableEloquent::procesar(
                query: $query,
                entrada: $entrada,
                columnasOrdenables: $columnasOrdenables,
                columnasBuscables: $columnasBuscables,
                transformar: static function (FamiliaresModel $familiar) use ($hoy): array {
                    $personaFam = $familiar->persona;
                    $personaTit = $familiar->empleado?->persona;

                    $edad = null;
                    $fechaNac = null;
                    if ($personaFam instanceof PersonasModel && $personaFam->fecha_nacimiento !== null) {
                        $fechaNac = $personaFam->fecha_nacimiento->format('Y-m-d');
                        $edad = $personaFam->fecha_nacimiento->diff($hoy)->y;
                    }

                    $genero = $personaFam instanceof PersonasModel && $personaFam->genero !== ''
                        ? (string) $personaFam->genero
                        : (string) ($familiar->genero ?? '');

                    return [
                        'familia_id' => (int) $familiar->familia_id,
                        'empleado_id' => (int) $familiar->empleado_id,
                        'persona_id' => (int) $familiar->persona_id,
                        'cedula_titular' => $personaTit instanceof PersonasModel
                            ? strtoupper((string) $personaTit->nacionalidad) . '-' . (string) $personaTit->cedula
                            : '',
                        'empleado_titular' => CatalogosService::nombreCompletoPersona($personaTit),
                        'cedula_familiar' => $personaFam instanceof PersonasModel
                            ? strtoupper((string) $personaFam->nacionalidad) . '-' . (string) $personaFam->cedula
                            : '',
                        'nombre_familiar' => CatalogosService::nombreCompletoPersona($personaFam),
                        'parentesco_id' => (int) $familiar->parentesco_id,
                        'parentesco' => (string) ($familiar->tipoParentesco?->parentesco ?? ''),
                        'edad' => $edad,
                        'fecha_nacimiento' => $fechaNac,
                        'genero' => $genero,
                        'tomo' => (int) $familiar->tomo,
                        'folio' => (int) $familiar->folio,
                        'tomo_folio' => 'T-' . (string) $familiar->tomo . ' / F-' . (string) $familiar->folio,
                    ];
                },
                aplicarFiltros: static function (Builder $q, array $filtros): void {
                    if (isset($filtros['empleado_id']) && is_numeric($filtros['empleado_id'])) {
                        $q->where('empleado_id', (int) $filtros['empleado_id']);
                    }
                    if (isset($filtros['parentesco_id']) && is_numeric($filtros['parentesco_id'])) {
                        $q->where('parentesco_id', (int) $filtros['parentesco_id']);
                    } elseif (
                        isset($filtros['parentesco'])
                        && is_string($filtros['parentesco'])
                        && trim($filtros['parentesco']) !== ''
                        && trim($filtros['parentesco']) !== 'todos'
                    ) {
                        $texto = trim($filtros['parentesco']);
                        $op = $q->getConnection()->getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';
                        $q->whereHas('tipoParentesco', static fn (Builder $sub): Builder => $sub->where('parentesco', $op, $texto));
                    }
                },
                ordenPorDefecto: ['familia_id', 'desc'],
            );

            $this->app->json($resultado, 200);
        } catch (Throwable) {
            $this->app->json(
                DataTableEloquent::error($entrada, 'Error interno al consultar los registros familiares.'),
                500
            );
        }
    }

    /**
     * [GET] Detalle de un familiar.
     */
    public function show(string $id): void
    {
        if (!ctype_digit($id) || (int) $id <= 0) {
            $this->app->json(['ok' => false, 'message' => 'Identificador de familiar inválido.'], 400);
            return;
        }

        $familiar = FamiliaresModel::query()
            ->with(['persona.estadoCivil', 'persona.documentos.tipoDocumento', 'tipoParentesco', 'empleado.persona', 'empleado.cargo'])
            ->find((int) $id);

        if (!$familiar instanceof FamiliaresModel || !$familiar->persona instanceof PersonasModel) {
            $this->app->json(['ok' => false, 'message' => 'Familiar no encontrado.'], 404);
            return;
        }

        $persona = $familiar->persona;
        $titularPersona = $familiar->empleado?->persona;

        $this->app->json([
            'ok' => true,
            'data' => [
                'familia_id' => (int) $familiar->familia_id,
                'empleado_id' => (int) $familiar->empleado_id,
                'persona_id' => (int) $familiar->persona_id,
                'parentesco_id' => (int) $familiar->parentesco_id,
                'parentesco' => $familiar->tipoParentesco?->parentesco,
                'tomo' => (int) $familiar->tomo,
                'folio' => (int) $familiar->folio,
                'genero' => $persona->genero !== '' ? $persona->genero : $familiar->genero,
                'titular' => [
                    'empleado_id' => (int) $familiar->empleado_id,
                    'nombre_completo' => CatalogosService::nombreCompletoPersona($titularPersona),
                    'cedula' => $titularPersona instanceof PersonasModel
                        ? strtoupper((string) $titularPersona->nacionalidad) . '-' . (string) $titularPersona->cedula
                        : '',
                    'cargo' => $familiar->empleado?->cargo?->cargo,
                ],
                'persona' => [
                    'primer_nombre' => $persona->primer_nombre,
                    'segundo_nombre' => $persona->segundo_nombre,
                    'primer_apellido' => $persona->primer_apellido,
                    'segundo_apellido' => $persona->segundo_apellido,
                    'nacionalidad' => $persona->nacionalidad,
                    'cedula' => (int) $persona->cedula,
                    'fecha_nacimiento' => $persona->fecha_nacimiento?->format('Y-m-d'),
                    'estado_civil_id' => $persona->estado_civil_id !== null ? (int) $persona->estado_civil_id : null,
                ],
            ],
        ], 200);
    }

    /**
     * [POST] Registra un familiar y su vínculo con el trabajador titular (+ documento opcional).
     */
    public function store(): void
    {
        $datos = $this->app->request()->data->getData();
        $archivos = $this->app->request()->files->getData();

        $empleadoId = $this->extraerEntero($datos, 'empleado_id');
        $parentescoId = $this->extraerEntero($datos, 'parentesco_id');
        $estadoCivilId = $this->extraerEntero($datos, 'estado_civil_id');
        $nacionalidad = strtoupper(trim((string) ($datos['nacionalidad'] ?? 'V')));
        $cedulaRaw = trim((string) ($datos['cedula'] ?? ''));
        $cedula = ctype_digit($cedulaRaw) ? (int) $cedulaRaw : 0;
        $primerNombre = trim((string) ($datos['primer_nombre'] ?? ''));
        $segundoNombre = trim((string) ($datos['segundo_nombre'] ?? ''));
        $primerApellido = trim((string) ($datos['primer_apellido'] ?? ''));
        $segundoApellido = trim((string) ($datos['segundo_apellido'] ?? ''));
        $fechaNacimiento = trim((string) ($datos['fecha_nacimiento'] ?? ''));
        $genero = trim((string) ($datos['genero'] ?? ''));
        $tomo = isset($datos['tomo']) && is_numeric($datos['tomo']) ? (int) $datos['tomo'] : -1;
        $folio = isset($datos['folio']) && is_numeric($datos['folio']) ? (int) $datos['folio'] : -1;

        $errores = $this->validarDatos(
            $empleadoId,
            $parentescoId,
            $estadoCivilId,
            $nacionalidad,
            $cedula,
            $primerNombre,
            $primerApellido,
            $fechaNacimiento,
            $genero,
            $tomo,
            $folio,
            true
        );

        if ($errores !== []) {
            $this->app->json(['ok' => false, 'message' => 'Datos del familiar inválidos.', 'errors' => $errores], 422);
            return;
        }

        $entradaArchivo = $archivos['archivo'] ?? null;
        $tipoDocumentoId = $this->extraerEntero($datos, 'tipo_documento_id');
        if (is_array($entradaArchivo) && ($entradaArchivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE && $tipoDocumentoId === null) {
            $this->app->json(['ok' => false, 'message' => 'Debe indicar tipo_documento_id al adjuntar un archivo.'], 422);
            return;
        }

        $conexion = (new FamiliaresModel())->getConnection();

        try {
            $resultado = $conexion->transaction(function () use (
                $empleadoId,
                $parentescoId,
                $estadoCivilId,
                $nacionalidad,
                $cedula,
                $primerNombre,
                $segundoNombre,
                $primerApellido,
                $segundoApellido,
                $fechaNacimiento,
                $genero,
                $tomo,
                $folio,
                $entradaArchivo,
                $tipoDocumentoId
            ): array {
                $persona = PersonasModel::query()
                    ->where('nacionalidad', $nacionalidad)
                    ->where('cedula', $cedula)
                    ->first();

                if ($persona instanceof PersonasModel) {
                    $yaVinculado = FamiliaresModel::query()
                        ->where('empleado_id', $empleadoId)
                        ->where('persona_id', (int) $persona->persona_id)
                        ->exists();

                    if ($yaVinculado) {
                        throw new InvalidArgumentException('Esta persona ya se encuentra registrada como familiar del trabajador.', 422);
                    }
                } else {
                    $persona = PersonasModel::query()->create([
                        'primer_nombre' => $primerNombre,
                        'segundo_nombre' => $segundoNombre,
                        'primer_apellido' => $primerApellido,
                        'segundo_apellido' => $segundoApellido,
                        'genero' => $genero,
                        'cedula' => $cedula,
                        'nacionalidad' => $nacionalidad,
                        'fecha_nacimiento' => $fechaNacimiento,
                        'estado_civil_id' => $estadoCivilId,
                    ]);
                }

                $familiar = FamiliaresModel::query()->create([
                    'empleado_id' => $empleadoId,
                    'persona_id' => (int) $persona->persona_id,
                    'parentesco_id' => $parentescoId,
                    'tomo' => $tomo,
                    'folio' => $folio,
                    'genero' => $genero,
                ]);

                $documento = null;
                if (
                    is_array($entradaArchivo)
                    && ($entradaArchivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE
                    && $tipoDocumentoId !== null
                ) {
                    $documento = $this->gestorDocumentos->subir(
                        $entradaArchivo,
                        $tipoDocumentoId,
                        (int) $empleadoId,
                        (int) $familiar->familia_id
                    );
                }

                return [
                    'familia_id' => (int) $familiar->familia_id,
                    'persona_id' => (int) $persona->persona_id,
                    'empleado_id' => (int) $familiar->empleado_id,
                    'documento_id' => $documento?->documentos_id,
                ];
            });

            $this->app->json([
                'ok' => true,
                'message' => 'Familiar registrado correctamente.',
                'data' => $resultado,
            ], 201);
        } catch (InvalidArgumentException $e) {
            $this->app->json(['ok' => false, 'message' => $e->getMessage()], $this->codigoHttp($e, 422));
        } catch (RuntimeException $e) {
            $this->app->json(['ok' => false, 'message' => $e->getMessage()], $this->codigoHttp($e, 500));
        } catch (Throwable) {
            $this->app->json(['ok' => false, 'message' => 'No se pudo registrar el familiar.'], 500);
        }
    }

    /**
     * [PUT|POST] Actualiza un familiar y sus datos personales (+ documento opcional).
     */
    public function update(string $id): void
    {
        if (!ctype_digit($id) || (int) $id <= 0) {
            $this->app->json(['ok' => false, 'message' => 'Identificador de familiar inválido.'], 400);
            return;
        }

        $familiar = FamiliaresModel::query()->with('persona')->find((int) $id);
        if (!$familiar instanceof FamiliaresModel || !$familiar->persona instanceof PersonasModel) {
            $this->app->json(['ok' => false, 'message' => 'Familiar no encontrado.'], 404);
            return;
        }

        $datos = $this->app->request()->data->getData();
        $archivos = $this->app->request()->files->getData();
        $personaActual = $familiar->persona;

        $empleadoId = (int) $familiar->empleado_id;
        $parentescoId = $this->extraerEntero($datos, 'parentesco_id') ?? (int) $familiar->parentesco_id;
        $estadoCivilId = $this->extraerEntero($datos, 'estado_civil_id') ?? ($personaActual->estado_civil_id !== null ? (int) $personaActual->estado_civil_id : null);
        $nacionalidad = strtoupper(trim((string) ($datos['nacionalidad'] ?? $personaActual->nacionalidad)));
        $cedulaRaw = trim((string) ($datos['cedula'] ?? $personaActual->cedula));
        $cedula = ctype_digit($cedulaRaw) ? (int) $cedulaRaw : 0;
        $primerNombre = trim((string) ($datos['primer_nombre'] ?? $personaActual->primer_nombre));
        $segundoNombre = trim((string) ($datos['segundo_nombre'] ?? $personaActual->segundo_nombre));
        $primerApellido = trim((string) ($datos['primer_apellido'] ?? $personaActual->primer_apellido));
        $segundoApellido = trim((string) ($datos['segundo_apellido'] ?? $personaActual->segundo_apellido));
        $fechaNacimiento = trim((string) ($datos['fecha_nacimiento'] ?? ($personaActual->fecha_nacimiento?->format('Y-m-d') ?? '')));
        $genero = trim((string) ($datos['genero'] ?? $personaActual->genero));
        $tomo = isset($datos['tomo']) && is_numeric($datos['tomo']) ? (int) $datos['tomo'] : (int) $familiar->tomo;
        $folio = isset($datos['folio']) && is_numeric($datos['folio']) ? (int) $datos['folio'] : (int) $familiar->folio;

        $errores = $this->validarDatos(
            $empleadoId,
            $parentescoId,
            $estadoCivilId,
            $nacionalidad,
            $cedula,
            $primerNombre,
            $primerApellido,
            $fechaNacimiento,
            $genero,
            $tomo,
            $folio,
            false
        );

        if ($errores !== []) {
            $this->app->json(['ok' => false, 'message' => 'Datos de actualización inválidos.', 'errors' => $errores], 422);
            return;
        }

        $duplicada = PersonasModel::query()
            ->where('nacionalidad', $nacionalidad)
            ->where('cedula', $cedula)
            ->where('persona_id', '!=', (int) $personaActual->persona_id)
            ->exists();

        if ($duplicada) {
            $this->app->json(['ok' => false, 'message' => 'Ya existe otra persona con esa nacionalidad y cédula.'], 422);
            return;
        }

        $entradaArchivo = $archivos['archivo'] ?? null;
        $tipoDocumentoId = $this->extraerEntero($datos, 'tipo_documento_id');
        $reemplazarDocumentoId = $this->extraerEntero($datos, 'reemplazar_documento_id');

        if (
            is_array($entradaArchivo)
            && ($entradaArchivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE
            && $reemplazarDocumentoId === null
            && $tipoDocumentoId === null
        ) {
            $this->app->json(['ok' => false, 'message' => 'Debe indicar tipo_documento_id o reemplazar_documento_id al adjuntar un archivo.'], 422);
            return;
        }

        $conexion = $familiar->getConnection();

        try {
            $documento = $conexion->transaction(function () use (
                $familiar,
                $personaActual,
                $parentescoId,
                $estadoCivilId,
                $nacionalidad,
                $cedula,
                $primerNombre,
                $segundoNombre,
                $primerApellido,
                $segundoApellido,
                $fechaNacimiento,
                $genero,
                $tomo,
                $folio,
                $entradaArchivo,
                $tipoDocumentoId,
                $reemplazarDocumentoId
            ) {
                $personaActual->fill([
                    'primer_nombre' => $primerNombre,
                    'segundo_nombre' => $segundoNombre,
                    'primer_apellido' => $primerApellido,
                    'segundo_apellido' => $segundoApellido,
                    'genero' => $genero,
                    'cedula' => $cedula,
                    'nacionalidad' => $nacionalidad,
                    'fecha_nacimiento' => $fechaNacimiento,
                    'estado_civil_id' => $estadoCivilId,
                ]);
                $personaActual->save();

                $familiar->fill([
                    'parentesco_id' => $parentescoId,
                    'tomo' => $tomo,
                    'folio' => $folio,
                    'genero' => $genero,
                ]);
                $familiar->save();

                if (is_array($entradaArchivo) && ($entradaArchivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                    if ($reemplazarDocumentoId !== null) {
                        return $this->gestorDocumentos->reemplazar(
                            $reemplazarDocumentoId,
                            $entradaArchivo,
                            $tipoDocumentoId,
                            (int) $familiar->empleado_id,
                            (int) $familiar->familia_id
                        );
                    }
                    if ($tipoDocumentoId !== null) {
                        return $this->gestorDocumentos->subir(
                            $entradaArchivo,
                            $tipoDocumentoId,
                            (int) $familiar->empleado_id,
                            (int) $familiar->familia_id
                        );
                    }
                }

                return null;
            });

            $this->app->json([
                'ok' => true,
                'message' => 'Familiar actualizado correctamente.',
                'data' => [
                    'familia_id' => (int) $familiar->familia_id,
                    'documento_id' => $documento?->documentos_id,
                ],
            ], 200);
        } catch (InvalidArgumentException $e) {
            $this->app->json(['ok' => false, 'message' => $e->getMessage()], $this->codigoHttp($e, 422));
        } catch (RuntimeException $e) {
            $this->app->json(['ok' => false, 'message' => $e->getMessage()], $this->codigoHttp($e, 500));
        } catch (Throwable) {
            $this->app->json(['ok' => false, 'message' => 'No se pudo actualizar el familiar.'], 500);
        }
    }

    /**
     * [DELETE] Elimina el vínculo familiar (`familiares`) preservando `personas`.
     */
    public function destroy(string $id): void
    {
        if (!ctype_digit($id) || (int) $id <= 0) {
            $this->app->json(['ok' => false, 'message' => 'Identificador de familiar inválido.'], 400);
            return;
        }

        $familiar = FamiliaresModel::query()->find((int) $id);
        if (!$familiar instanceof FamiliaresModel) {
            $this->app->json(['ok' => false, 'message' => 'Familiar no encontrado.'], 404);
            return;
        }

        $familiar->getConnection()->transaction(static function () use ($familiar): void {
            $familiar->delete();
        });

        $this->app->json([
            'ok' => true,
            'message' => 'Carga familiar eliminada correctamente.',
            'data' => ['familia_id' => (int) $id],
        ], 200);
    }

    private function codigoHttp(Throwable $e, int $defecto): int
    {
        $codigo = (int) $e->getCode();

        return $codigo >= 400 && $codigo < 600 ? $codigo : $defecto;
    }

    /**
     * @return array<string, string>
     */
    private function validarDatos(
        ?int $empleadoId,
        ?int $parentescoId,
        ?int $estadoCivilId,
        string $nacionalidad,
        int $cedula,
        string $primerNombre,
        string $primerApellido,
        string $fechaNacimiento,
        string $genero,
        int $tomo,
        int $folio,
        bool $esCreacion
    ): array {
        $errores = [];

        if ($esCreacion && ($empleadoId === null || !EmpleadosModel::query()->whereKey($empleadoId)->exists())) {
            $errores['empleado_id'] = 'El trabajador titular indicado no existe.';
        }
        if ($parentescoId === null || !TipoParentescoModel::query()->whereKey($parentescoId)->exists()) {
            $errores['parentesco_id'] = 'El parentesco indicado no existe.';
        }
        if ($estadoCivilId !== null && !EstadoCivilModel::query()->whereKey($estadoCivilId)->exists()) {
            $errores['estado_civil_id'] = 'El estado civil indicado no existe.';
        }
        if (!in_array($nacionalidad, ['V', 'E'], true)) {
            $errores['nacionalidad'] = 'La nacionalidad debe ser V o E.';
        }
        if ($cedula <= 0) {
            $errores['cedula'] = 'La cédula del familiar es obligatoria.';
        }
        if ($primerNombre === '') {
            $errores['primer_nombre'] = 'El primer nombre es obligatorio.';
        }
        if ($primerApellido === '') {
            $errores['primer_apellido'] = 'El primer apellido es obligatorio.';
        }
        if ($genero === '') {
            $errores['genero'] = 'El género es obligatorio.';
        }
        if ($tomo < 0) {
            $errores['tomo'] = 'El tomo del acta es obligatorio y debe ser numérico.';
        }
        if ($folio < 0) {
            $errores['folio'] = 'El folio del acta es obligatorio y debe ser numérico.';
        }

        $dt = DateTimeImmutable::createFromFormat('Y-m-d', $fechaNacimiento);
        if (!$dt instanceof DateTimeImmutable || $dt->format('Y-m-d') !== $fechaNacimiento) {
            $errores['fecha_nacimiento'] = 'La fecha de nacimiento es inválida.';
        }

        return $errores;
    }

    /**
     * @param array<string, mixed> $datos
     */
    private function extraerEntero(array $datos, string $clave): ?int
    {
        if (!isset($datos[$clave]) || $datos[$clave] === '' || !is_numeric($datos[$clave])) {
            return null;
        }

        $valor = (int) $datos[$clave];

        return $valor > 0 ? $valor : null;
    }
}
