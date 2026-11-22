<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\LimpiarInputs;
use App\Model\Catalogos\CargoModel;
use App\Model\Catalogos\DepartamentosModel;
use App\Model\Catalogos\DependenciasModel;
use App\Model\Catalogos\EstatusModel;
use App\Model\Personas\DocumentosModel;
use App\Model\Personas\EmpleadosModel;
use App\Model\Personas\FamiliaresModel;
use App\Model\Personas\PersonasModel;
use App\Services\CatalogosService;
use App\Services\ContextoSesion;
use App\Services\DataTableEloquent;
use App\Services\GestorDocumentos;
use App\Interfaces\RenderInterfaces;
use App\Utils\Config;
use DateTimeImmutable;
use finfo;
use flight\Engine;
use Illuminate\Database\Eloquent\Builder;
use Throwable;

/**
 * Controlador general para el listado, consultas, DataTables, baja lógica y documentos de empleados.
 */
class RegistrosEmpleadosController implements RenderInterfaces
{
    /**
     * @param Engine<object> $app
     */
    public function __construct(
        private Engine $app,
        private Config $config,
        private CatalogosService $catalogos,
        private ?GestorDocumentos $gestorDocumentos = null,
    ) {
    }

    /**
     * [GET] Renderiza la vista principal del directorio de empleados.
     */
    public function render(): void
    {
        $totales = EmpleadosModel::query()
            ->selectRaw('COUNT(*) AS total, COALESCE(SUM(CASE WHEN estatus_actual = 1 THEN 1 ELSE 0 END), 0) AS activos')
            ->toBase()
            ->first();

        $total = (int) ($totales->total ?? 0);
        $activos = (int) ($totales->activos ?? 0);
        $inactivos = max(0, $total - $activos);

        $this->app->render('registros_empleados', [
            'env' => $this->config->env(),
            'debug' => $this->config->isDebug(),
            'usuario' => (object) ContextoSesion::todo(),
            'estatus_catalogo' => $this->catalogos->estatus(false),
            'dependencias' => $this->catalogos->dependencias(true),
            'departamentos' => $this->catalogos->departamentos(true),
            'tipos_documentos' => $this->catalogos->tiposDocumentos(),
            'resumen' => [
                'total' => $total,
                'activos' => $activos,
                'inactivos' => $inactivos,
            ],
        ]);
    }

    /**
     * [GET] Búsqueda paginada de trabajadores activos para selects remotos.
     */
    public function buscarParaSelect(): void
    {
        $query = $this->app->request()->query;
        $busqueda = trim((string) ($query['q'] ?? ''));
        $limite = is_numeric($query['limit'] ?? null) ? (int) $query['limit'] : 30;

        $this->app->json([
            'ok' => true,
            'data' => $this->catalogos->buscarEmpleadosActivosParaSelect($busqueda, $limite),
        ], 200);
    }

    /**
     * [POST|GET] Listado Server-Side para DataTables 3.1.5 usando DataTableEloquent.
     */
    public function datatable(): void
    {
        $input = new LimpiarInputs();
        $entrada = $input->todo();
        if ($entrada === []) {
            $request = $this->app->request();
            $entrada = $request->method === 'GET' ? $request->query->getData() : $request->data->getData();
        }

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
                        'nacionalidad' => $persona instanceof PersonasModel ? strtoupper((string) $persona->nacionalidad) : '',
                        'cedula_numero' => $persona instanceof PersonasModel ? (int) $persona->cedula : null,
                        'cedula' => $persona instanceof PersonasModel
                            ? strtoupper((string) $persona->nacionalidad) . '-' . (string) $persona->cedula
                            : '',
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
     * [GET] Detalle de un trabajador con sus relaciones.
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
     * [GET] Lista los familiares asociados al trabajador para la modal.
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

    /**
     * [POST|PUT] Activa nuevamente a un trabajador desactivado (`estatus_actual = 1`).
     */
    public function activar(?string $id = null): void
    {
        $input = new LimpiarInputs();

        if ($id === null) {
            $error = $input->validar(['empleado_id']);
            if ($error !== null) {
                $this->app->json(['ok' => false, 'message' => $error], 422);
                return;
            }
            if (!$input->validarNumericos('empleado_id')) {
                $this->app->json(['ok' => false, 'message' => 'Identificador de empleado inválido.'], 400);
                return;
            }
            $id = (string) $input->get('empleado_id');
        }

        if (!ctype_digit($id) || (int) $id <= 0) {
            $this->app->json(['ok' => false, 'message' => 'Identificador de empleado inválido.'], 400);
            return;
        }

        $empleado = EmpleadosModel::query()->find((int) $id);
        if (!$empleado instanceof EmpleadosModel) {
            $this->app->json(['ok' => false, 'message' => 'Trabajador no encontrado.'], 404);
            return;
        }

        $empleado->estatus_actual = 1;
        $empleado->save();

        $this->app->json([
            'ok' => true,
            'message' => 'Trabajador activado correctamente.',
            'data' => ['empleado_id' => (int) $empleado->empleado_id, 'estatus_actual' => 1],
        ], 200);
    }

    /**
     * [GET] Lista los documentos personales y familiares de un trabajador ordenados del más viejo al más nuevo.
     */
    public function listarDocumentos(string $id): void
    {
        if (!ctype_digit($id) || (int) $id <= 0) {
            $this->app->json(['ok' => false, 'message' => 'Identificador de empleado inválido.'], 400);
            return;
        }

        $empleado = EmpleadosModel::query()
            ->with([
                'persona.documentos.tipoDocumento',
                'familiares.persona.documentos.tipoDocumento',
                'familiares.tipoParentesco',
            ])
            ->find((int) $id);

        if (!$empleado instanceof EmpleadosModel || !$empleado->persona instanceof PersonasModel) {
            $this->app->json(['ok' => false, 'message' => 'Trabajador no encontrado.'], 404);
            return;
        }

        $personales = $empleado->persona->documentos
            ->sortBy('documentos_id')
            ->map(static fn (DocumentosModel $doc): array => self::formatearDocumento($doc))
            ->values()
            ->all();

        $familiaresDocs = [];
        foreach ($empleado->familiares as $familiar) {
            if (!$familiar instanceof FamiliaresModel || !$familiar->persona instanceof PersonasModel) {
                continue;
            }
            foreach ($familiar->persona->documentos->sortBy('documentos_id') as $doc) {
                $item = self::formatearDocumento($doc);
                $item['familia_id'] = (int) $familiar->familia_id;
                $item['parentesco'] = (string) ($familiar->tipoParentesco?->parentesco ?? '');
                $item['propietario_nombre'] = CatalogosService::nombreCompletoPersona($familiar->persona);
                $familiaresDocs[] = $item;
            }
        }

        $this->app->json([
            'ok' => true,
            'empleado_id' => (int) $empleado->empleado_id,
            'personales' => $personales,
            'familiares' => $familiaresDocs,
        ], 200);
    }

    /**
     * [POST] Sube un documento para un trabajador o familiar usando LimpiarInputs.
     */
    public function subirDocumento(): void
    {
        if ($this->gestorDocumentos === null) {
            $this->app->json(['ok' => false, 'message' => 'Servicio documental no disponible.'], 500);
            return;
        }

        $input = new LimpiarInputs();
        $error = $input->validar(['tipo_documento_id', 'empleado_id']);
        if ($error !== null) {
            $this->app->json(['ok' => false, 'message' => $error], 422);
            return;
        }

        $tipoDocumentoId = (int) $input->get('tipo_documento_id');
        $empleadoId = (int) $input->get('empleado_id');
        $familiaRaw = $input->get('familia_id');
        $familiaId = is_numeric($familiaRaw) && (int) $familiaRaw > 0 ? (int) $familiaRaw : null;

        $archivos = $this->app->request()->files->getData();
        $entradaArchivo = $archivos['archivo'] ?? null;
        if (!is_array($entradaArchivo)) {
            $this->app->json(['ok' => false, 'message' => 'Debe adjuntar un archivo en el campo "archivo".'], 422);
            return;
        }

        try {
            $documento = $this->gestorDocumentos->subir($entradaArchivo, $tipoDocumentoId, $empleadoId, $familiaId);
            $this->app->json([
                'ok' => true,
                'message' => 'Documento cargado correctamente.',
                'data' => self::formatearDocumento($documento),
            ], 201);
        } catch (Throwable $e) {
            $codigo = (int) $e->getCode();
            $this->app->json(['ok' => false, 'message' => $e->getMessage()], $codigo >= 400 && $codigo < 600 ? $codigo : 500);
        }
    }

    /**
     * [POST] Registra una nueva versión del documento conservando el historial anterior.
     */
    public function reemplazarDocumento(string $id): void
    {
        if ($this->gestorDocumentos === null) {
            $this->app->json(['ok' => false, 'message' => 'Servicio documental no disponible.'], 500);
            return;
        }

        if (!ctype_digit($id) || (int) $id <= 0) {
            $this->app->json(['ok' => false, 'message' => 'Identificador de documento inválido.'], 400);
            return;
        }

        $input = new LimpiarInputs();
        $archivos = $this->app->request()->files->getData();
        $entradaArchivo = $archivos['archivo'] ?? null;

        if (!is_array($entradaArchivo)) {
            $this->app->json(['ok' => false, 'message' => 'Debe adjuntar el nuevo archivo en el campo "archivo".'], 422);
            return;
        }

        $tipoRaw = $input->get('tipo_documento_id');
        $nuevoTipoId = is_numeric($tipoRaw) && (int) $tipoRaw > 0 ? (int) $tipoRaw : null;
        $empRaw = $input->get('empleado_id');
        $empleadoId = is_numeric($empRaw) && (int) $empRaw > 0 ? (int) $empRaw : null;
        $famRaw = $input->get('familia_id');
        $familiaId = is_numeric($famRaw) && (int) $famRaw > 0 ? (int) $famRaw : null;

        try {
            $documento = $this->gestorDocumentos->reemplazar((int) $id, $entradaArchivo, $nuevoTipoId, $empleadoId, $familiaId);
            $this->app->json([
                'ok' => true,
                'message' => 'Documento reemplazado correctamente.',
                'data' => self::formatearDocumento($documento),
            ], 200);
        } catch (Throwable $e) {
            $codigo = (int) $e->getCode();
            $this->app->json(['ok' => false, 'message' => $e->getMessage()], $codigo >= 400 && $codigo < 600 ? $codigo : 500);
        }
    }

    /**
     * [GET] Sirve un documento protegido por sesión desde DocumentosInces.
     */
    public function descargarDocumento(string $id): void
    {
        if ($this->gestorDocumentos === null) {
            $this->app->halt(500, 'Servicio documental no disponible.');
            return;
        }

        $documento = ctype_digit($id) && (int) $id > 0 ? DocumentosModel::query()->find((int) $id) : null;
        if (!$documento instanceof DocumentosModel) {
            $this->app->halt(404, 'Documento no encontrado.');
            return;
        }

        try {
            $rutaAbsoluta = $this->gestorDocumentos->rutaAbsoluta($documento);
        } catch (Throwable $e) {
            $codigo = (int) $e->getCode();
            $this->app->halt($codigo >= 400 && $codigo < 600 ? $codigo : 404, $e->getMessage());
            return;
        }

        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($rutaAbsoluta);
        $disposicion = ($this->app->request()->query['descargar'] ?? '') === '1' ? 'attachment' : 'inline';
        $nombreSeguro = str_replace(["\r", "\n", '"'], '', (string) $documento->nombre_original) ?: basename($rutaAbsoluta);

        header('X-Content-Type-Options: nosniff');
        header('Content-Type: ' . (is_string($mime) && $mime !== '' ? $mime : 'application/octet-stream'));
        header('Content-Disposition: ' . $disposicion . '; filename="' . $nombreSeguro . '"');
        if (($tamano = filesize($rutaAbsoluta)) !== false) {
            header('Content-Length: ' . (string) $tamano);
        }

        readfile($rutaAbsoluta);
        if (empty(getenv('PHPUNIT_TEST')) && PHP_SAPI !== 'cli') {
            exit;
        }
    }

    /**
     * @return array<string, mixed>
     */
    private static function formatearDocumento(DocumentosModel $documento): array
    {
        return [
            'documentos_id' => (int) $documento->documentos_id,
            'tipo_documento_id' => (int) $documento->tipo_documento_id,
            'tipo_documento' => (string) ($documento->tipoDocumento?->tipo_documentos ?? ''),
            'persona_id' => (int) $documento->persona_id,
            'codigo' => (string) ($documento->codigo ?? ''),
            'ruta' => (string) $documento->ruta,
            'estatus_documento' => (string) ($documento->estatus_documento ?? 'Vigente'),
            'nombre_original' => (string) $documento->nombre_original,
            'tamano' => (int) $documento->tamano,
            'formato' => (string) $documento->formato,
            'fecha_subida' => $documento->fecha_subida?->format('Y-m-d H:i:s'),
            'url_descarga' => '/documentos/' . (int) $documento->documentos_id,
        ];
    }
}
