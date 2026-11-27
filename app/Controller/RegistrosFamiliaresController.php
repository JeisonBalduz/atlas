<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\LimpiarInputs;
use App\Model\Beneficios\DetalleDiscapacidadModel;
use App\Model\Beneficios\GradoDiscapacidadModel;
use App\Model\Catalogos\TipoParentescoModel;
use App\Model\Personas\DocumentosModel;
use App\Model\Personas\FamiliaresModel;
use App\Model\Personas\PersonasModel;
use App\Services\CatalogosService;
use App\Services\DataTableEloquent;
use App\Services\GestorDocumentos;
use App\Services\UtilidadesService;
use App\Utils\Config;
use DateTimeImmutable;
use flight\Engine;
use Illuminate\Database\Eloquent\Builder;
use Throwable;

/**
 * Controlador general para la vista, DataTables, consulta y eliminación de vínculos familiares.
 */
class RegistrosFamiliaresController extends BaseController
{
    private UtilidadesService $utilidades;

    /**
     * Recibe Engine, Config, CatalogosService y GestorDocumentos opcional para la gestión de familiares.
     */
    public function __construct(
        Engine $app,
        Config $config,
        private CatalogosService $catalogos,
        private ?GestorDocumentos $gestorDocumentos = null,
        ?UtilidadesService $utilidades = null,
    ) {
        parent::__construct($app, $config);
        $this->utilidades = $utilidades ?? new UtilidadesService();
    }

    public function render(): void
    {
        $this->renderVista('registros_familiares', [
            'parentescos' => $this->catalogos->tiposParentesco(),
        ]);
    }

    public function datatable(): void
    {
        $entrada = $this->entradaDataTable();
        $hoy = new DateTimeImmutable('today');

        try {
            $query = FamiliaresModel::query()
                ->select('familiares.*')
                ->addSelect([
                    'edad_texto' => PersonasModel::selectRaw(
                        $this->utilidades->expresionSql('personas.fecha_nacimiento')
                    )
                        ->whereColumn('personas.persona_id', 'familiares.persona_id')
                        ->limit(1),
                ])
                ->with([
                    'persona.detallesDiscapacidad.gradoDiscapacidad',
                    'persona.documentos',
                    'tipoParentesco',
                    'empleado.persona',
                    'empleado.cargo',
                ]);

            $ordenarPorEdad = static fn (Builder $q, string $dir): Builder => $q->orderBy(
                PersonasModel::select('fecha_nacimiento')->whereColumn('personas.persona_id', 'familiares.persona_id')->limit(1),
                $dir === 'asc' ? 'desc' : 'asc'
            );

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
                'edad' => $ordenarPorEdad,
                'edad_texto' => $ordenarPorEdad,
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
                    $edadTexto = null;
                    $fechaNac = null;
                    if ($personaFam instanceof PersonasModel && $personaFam->fecha_nacimiento !== null) {
                        $fechaNac = $personaFam->fecha_nacimiento->format('Y-m-d');
                        $edad = $personaFam->fecha_nacimiento->diff($hoy)->y;
                        $textoSql = trim((string) ($familiar->getAttribute('edad_texto') ?? ''));
                        $edadTexto = $textoSql !== '' ? $textoSql : null;
                    }

                    $genero = $personaFam instanceof PersonasModel && $personaFam->genero !== ''
                        ? (string) $personaFam->genero
                        : (string) ($familiar->genero ?? '');

                    $detalleDisc = $personaFam instanceof PersonasModel
                        ? $personaFam->detallesDiscapacidad->sortByDesc('dt_discapacidad_id')->first()
                        : null;
                    $docDiscVigente = $personaFam instanceof PersonasModel
                        ? $personaFam->documentos
                            ->filter(static fn (DocumentosModel $d): bool => (int) $d->tipo_documento_id === 10 && strcasecmp((string) $d->estatus_documento, 'Vigente') === 0)
                            ->sortByDesc('documentos_id')
                            ->first()
                        : null;

                    $discapacidad = null;
                    if ($detalleDisc instanceof DetalleDiscapacidadModel) {
                        $gradoNombre = (string) ($detalleDisc->gradoDiscapacidad?->nombre_gradodis ?? '');
                        $tipoNombre = trim((string) ($detalleDisc->dirigido ?? ''));
                        $discapacidad = [
                            'dt_discapacidad_id' => (int) $detalleDisc->dt_discapacidad_id,
                            'grado_discapacidad_id' => (int) $detalleDisc->grado_discapacidad_id,
                            'grado' => $gradoNombre,
                            'tipo' => $tipoNombre,
                            'numero_carnet' => (string) $detalleDisc->numero_carnet,
                            'entidad_emisora' => (string) $detalleDisc->entidad_emisora,
                            'descripcion' => (string) ($detalleDisc->descripcion ?? ''),
                            'fecha_emision_carnet' => $detalleDisc->fecha_emision_carnet->format('Y-m-d'),
                            'fecha_vencimiento_carnet' => $detalleDisc->fecha_vencimiento_carnet->format('Y-m-d'),
                            'documento' => $docDiscVigente instanceof DocumentosModel
                                ? [
                                    'documentos_id' => (int) $docDiscVigente->documentos_id,
                                    'nombre_original' => (string) $docDiscVigente->nombre_original,
                                    'url_descarga' => '/documentos/' . (int) $docDiscVigente->documentos_id,
                                ]
                                : null,
                        ];
                    }

                    $tomo = (int) $familiar->tomo;
                    $folio = (int) $familiar->folio;
                    $tomoFolio = ($tomo === 0 && $folio === 0)
                        ? '—'
                        : 'T-' . (string) $tomo . ' / F-' . (string) $folio;

                    return [
                        'familia_id' => (int) $familiar->familia_id,
                        'empleado_id' => (int) $familiar->empleado_id,
                        'persona_id' => (int) $familiar->persona_id,
                        'cedula_titular' => $personaTit instanceof PersonasModel
                            ? strtoupper((string) $personaTit->nacionalidad) . '-' . (string) $personaTit->cedula
                            : '',
                        'empleado_titular' => CatalogosService::nombreCompletoPersona($personaTit),
                        'nacionalidad_familiar' => $personaFam instanceof PersonasModel ? strtoupper((string) $personaFam->nacionalidad) : '',
                        'cedula_familiar_numero' => $personaFam instanceof PersonasModel ? (int) $personaFam->cedula : null,
                        'cedula_familiar' => $personaFam instanceof PersonasModel
                            ? strtoupper((string) $personaFam->nacionalidad) . '-' . (string) $personaFam->cedula
                            : '',
                        'nombre_familiar' => CatalogosService::nombreCompletoPersona($personaFam),
                        'parentesco_id' => (int) $familiar->parentesco_id,
                        'parentesco' => (string) ($familiar->tipoParentesco?->parentesco ?? ''),
                        'edad' => $edad,
                        'edad_texto' => $edadTexto,
                        'fecha_nacimiento' => $fechaNac,
                        'genero' => $genero,
                        'tomo' => $tomo,
                        'folio' => $folio,
                        'tomo_folio' => $tomoFolio,
                        'discapacidad' => $discapacidad,
                    ];
                },
                aplicarFiltros: static function (Builder $q, array $filtros): void {
                    if (isset($filtros['empleado_id']) && is_numeric($filtros['empleado_id'])) {
                        $q->where('empleado_id', (int) $filtros['empleado_id']);
                    }
                    if (isset($filtros['parentesco_id']) && is_numeric($filtros['parentesco_id'])) {
                        $q->where('parentesco_id', (int) $filtros['parentesco_id']);
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

    public function show(string $id): void
    {
        $familiaId = $this->idPositivo($id, 'familiar');
        if ($familiaId === null) {
            return;
        }

        $familiar = FamiliaresModel::query()
            ->with(['persona.estadoCivil', 'persona.documentos.tipoDocumento', 'tipoParentesco', 'empleado.persona', 'empleado.cargo'])
            ->find($familiaId);

        if (!$familiar instanceof FamiliaresModel || !$familiar->persona instanceof PersonasModel) {
            $this->jsonError('Familiar no encontrado.', 404);
            return;
        }

        $persona = $familiar->persona;
        $titularPersona = $familiar->empleado?->persona;

        $this->jsonOk([
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
        ]);
    }

    public function destroy(string $id): void
    {
        $familiaId = $this->idPositivo($id, 'familiar');
        if ($familiaId === null) {
            return;
        }

        $familiar = FamiliaresModel::query()->find($familiaId);
        if (!$familiar instanceof FamiliaresModel) {
            $this->jsonError('Familiar no encontrado.', 404);
            return;
        }

        $personaId = (int) $familiar->persona_id;
        $relacionesExternas = $this->verificarRelacionesPersonaExternas($familiar, $personaId);
        if ($relacionesExternas !== []) {
            $this->jsonError(
                'No se puede eliminar el familiar porque posee registros asociados en: ' . implode(', ', $relacionesExternas) . '.',
                409
            );
            return;
        }

        try {
            $conn = $familiar->getConnection();
            $conn->transaction(static function () use ($familiar, $personaId): void {
                $familiar->delete();

                $otrosVinculos = FamiliaresModel::query()
                    ->where('persona_id', $personaId)
                    ->exists();

                if (!$otrosVinculos) {
                    PersonasModel::query()->where('persona_id', $personaId)->delete();
                }
            });

            $this->jsonOk(['familia_id' => $familiaId], 'Carga familiar eliminada correctamente.');
        } catch (Throwable) {
            $this->jsonError('No se puede eliminar el familiar porque tiene registros vinculados en el sistema.', 409);
        }
    }

    /**
     * Recibe el ID del familiar y los datos CONAPDIS; crea o actualiza su discapacidad y adjunta soporte opcional.
     */
    public function asignarDiscapacidad(string $id): void
    {
        $familiaId = $this->idPositivo($id, 'familiar');
        if ($familiaId === null) {
            return;
        }

        $familiar = FamiliaresModel::query()->with('persona')->find($familiaId);
        $personaRel = $familiar instanceof FamiliaresModel ? $familiar->getRelationValue('persona') : null;
        if (!$familiar instanceof FamiliaresModel || !$personaRel instanceof PersonasModel) {
            $this->jsonError('Familiar no encontrado.', 404);
            return;
        }

        $input = new LimpiarInputs();
        $gradoRaw = $input->get('grado_discapacidad_id');
        $gradoId = is_numeric($gradoRaw) && (int) $gradoRaw > 0 ? (int) $gradoRaw : null;
        $tipoNombre = trim((string) $input->get('tipo_discapacidad', ''));
        $numeroCarnet = trim((string) $input->get('numero_carnet', ''));
        $entidadEmisora = trim((string) $input->get('entidad_emisora', 'CONAPDIS')) ?: 'CONAPDIS';
        $descripcion = trim((string) $input->get('descripcion', ''));
        $emisionRaw = trim((string) $input->get('fecha_emision_carnet', ''));
        $vencimientoRaw = trim((string) $input->get('fecha_vencimiento_carnet', ''));
        $fechaEmision = preg_match('/^\d{4}-\d{2}-\d{2}$/', $emisionRaw) === 1 ? $emisionRaw : date('Y-m-d');
        $fechaVencimiento = preg_match('/^\d{4}-\d{2}-\d{2}$/', $vencimientoRaw) === 1
            ? $vencimientoRaw
            : date('Y-m-d', strtotime('+5 years'));

        if ($gradoId === null || !GradoDiscapacidadModel::query()->whereKey($gradoId)->exists()) {
            $this->jsonError('Debe seleccionar un grado de discapacidad válido.', 422);
            return;
        }
        if ($numeroCarnet === '') {
            $this->jsonError('El número de carnet CONAPDIS es obligatorio.', 422);
            return;
        }
        if ($fechaVencimiento < $fechaEmision) {
            $this->jsonError('La fecha de vencimiento del carnet no puede ser menor que la fecha de emisión.', 422);
            return;
        }

        try {
            $datosDisc = [
                'persona_id' => (int) $familiar->persona_id,
                'grado_discapacidad_id' => $gradoId,
                'numero_carnet' => substr($numeroCarnet, 0, 30),
                'entidad_emisora' => substr($entidadEmisora, 0, 100),
                'descripcion' => $descripcion !== '' ? $descripcion : null,
                'dirigido' => $tipoNombre !== '' ? substr($tipoNombre, 0, 30) : 'Familiar',
                'fecha_emision_carnet' => $fechaEmision,
                'fecha_vencimiento_carnet' => $fechaVencimiento,
            ];

            $existente = DetalleDiscapacidadModel::query()
                ->where('persona_id', (int) $familiar->persona_id)
                ->orderByDesc('dt_discapacidad_id')
                ->first();

            if ($existente instanceof DetalleDiscapacidadModel) {
                $existente->fill($datosDisc);
                $existente->save();
                $detalle = $existente;
                $codigoHttp = 200;
                $mensaje = 'Discapacidad actualizada correctamente para el familiar.';
            } else {
                $detalle = DetalleDiscapacidadModel::query()->create($datosDisc);
                $codigoHttp = 201;
                $mensaje = 'Discapacidad asignada correctamente al familiar.';
            }

            $archivo = $this->app->request()->files->getData()['archivo'] ?? null;
            $documentoId = null;
            if (
                $this->gestorDocumentos !== null
                && is_array($archivo)
                && ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE
            ) {
                $doc = $this->gestorDocumentos->subir($archivo, 10, (int) $familiar->empleado_id, $familiaId);
                $documentoId = (int) $doc->documentos_id;
            }

            $this->jsonOk([
                'dt_discapacidad_id' => (int) $detalle->dt_discapacidad_id,
                'persona_id' => (int) $familiar->persona_id,
                'familia_id' => $familiaId,
                'documento_id' => $documentoId,
            ], $mensaje, $codigoHttp);
        } catch (Throwable $e) {
            $codigo = (int) $e->getCode();
            $this->jsonError(
                $codigo >= 400 && $codigo < 500 ? $e->getMessage() : 'No se pudo registrar la discapacidad del familiar.',
                $codigo >= 400 && $codigo < 600 ? $codigo : 500
            );
        }
    }

    /**
     * Verifica si `persona_id` está referenciado en otras tablas del esquema distintas de `familiares`.
     *
     * @return list<string>
     */
    private function verificarRelacionesPersonaExternas(FamiliaresModel $familiar, int $personaId): array
    {
        if ($personaId <= 0) {
            return [];
        }

        $conn = $familiar->getConnection();
        $tablasAVerificar = [
            'documentos' => 'Expediente documental',
            'detalle_beneficio' => 'Beneficios asignados',
            'detalle_hcm' => 'Póliza HCM',
            'detalle_discapacidad' => 'Registro de discapacidad',
            'empleados' => 'Ficha de empleado titular',
            'talla_personas' => 'Dotación de tallas',
            'telefono_personas' => 'Teléfonos registrados',
            'postulacion' => 'Postulaciones',
            'tramite' => 'Trámites administrativos',
        ];

        $vinculosDetectados = [];
        foreach ($tablasAVerificar as $tabla => $etiqueta) {
            try {
                if ($conn->table($tabla)->where('persona_id', $personaId)->exists()) {
                    $vinculosDetectados[] = $etiqueta;
                }
            } catch (Throwable) {
                // En caso de tabla opcional no presente en entorno de pruebas SQLite, se ignora sin romper
            }
        }

        return $vinculosDetectados;
    }
}
