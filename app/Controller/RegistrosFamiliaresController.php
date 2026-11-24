<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\Catalogos\TipoParentescoModel;
use App\Model\Personas\FamiliaresModel;
use App\Model\Personas\PersonasModel;
use App\Services\CatalogosService;
use App\Services\DataTableEloquent;
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
    public function __construct(
        Engine $app,
        Config $config,
        private CatalogosService $catalogos,
    ) {
        parent::__construct($app, $config);
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
                        'nacionalidad_familiar' => $personaFam instanceof PersonasModel ? strtoupper((string) $personaFam->nacionalidad) : '',
                        'cedula_familiar_numero' => $personaFam instanceof PersonasModel ? (int) $personaFam->cedula : null,
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
