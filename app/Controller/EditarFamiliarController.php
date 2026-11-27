<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\LimpiarInputs;
use App\Model\Beneficios\DetalleDiscapacidadModel;
use App\Model\Beneficios\GradoDiscapacidadModel;
use App\Model\Catalogos\TipoParentescoModel;
use App\Model\Personas\EmpleadosModel;
use App\Model\Personas\FamiliaresModel;
use App\Model\Personas\PersonasModel;
use App\Services\CatalogosService;
use App\Services\GestorDocumentos;
use App\Services\UtilidadesService;
use App\Utils\Config;
use DateTimeImmutable;
use flight\Engine;
use Throwable;

/**
 * Controlador general para la vista y actualización de carga familiar.
 */
class EditarFamiliarController extends BaseController
{
    private UtilidadesService $utilidades;

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

    public function render(?string $id = null): void
    {
        $idRaw = $id ?? ($this->app->request()->query['id'] ?? null);
        if (!is_numeric($idRaw) || (int) $idRaw <= 0) {
            $this->app->redirect('/empleado/registros/familiar');
            return;
        }

        $familiar = FamiliaresModel::query()
            ->with([
                'persona.detallesDiscapacidad.gradoDiscapacidad',
                'empleado.persona',
                'empleado.cargo',
                'empleado.departamento',
            ])
            ->find((int) $idRaw);

        if (!$familiar instanceof FamiliaresModel || !$familiar->persona instanceof PersonasModel) {
            $this->app->halt(404, 'Familiar no encontrado');
            return;
        }

        $titular = $familiar->empleado;
        $titularPersona = $titular instanceof EmpleadosModel ? $titular->persona : null;
        $titularNombre = CatalogosService::nombreCompletoPersona($titularPersona);
        $titularCedula = $titularPersona instanceof PersonasModel
            ? strtoupper((string) $titularPersona->nacionalidad) . '-' . (string) $titularPersona->cedula
            : '';
        $titularCargo = trim(
            ($titular?->cargo?->cargo ?? '')
            . ($titular?->departamento?->departamento !== null ? ' · ' . $titular->departamento->departamento : '')
        );

        $generoActual = (string) ($familiar->persona->genero !== '' ? $familiar->persona->genero : ($familiar->genero ?? ''));
        $detallesDisc = $familiar->persona->getRelationValue('detallesDiscapacidad');
        $discapacidadActual = $detallesDisc instanceof \Illuminate\Database\Eloquent\Collection
            ? $detallesDisc->sortByDesc('dt_discapacidad_id')->first()
            : null;
        if (!$discapacidadActual instanceof DetalleDiscapacidadModel) {
            $discapacidadActual = null;
        }

        $tiposDiscMap = [];
        foreach ($this->catalogos->tiposDiscapacidad() as $nombreTipo) {
            $tiposDiscMap[(string) $nombreTipo] = (string) $nombreTipo;
        }

        $recaudosBase = $this->catalogos->recaudosParaFamiliar(
            (int) $familiar->parentesco_id,
            (int) $familiar->persona_id,
            (int) ($titularPersona?->persona_id ?? 0)
        );
        $recaudoDiscapacidad = $this->catalogos->recaudoDiscapacidadUnificado((int) $familiar->persona_id);

        // Si el familiar no posee discapacidad registrada, se oculta por completo el recaudo ID 10
        $recaudosVisibles = array_values(array_filter(
            $recaudosBase,
            static fn (array $rec): bool => (int) $rec['tipo_documento_id'] !== 10
        ));
        if ($discapacidadActual !== null && $recaudoDiscapacidad !== null) {
            $recaudosVisibles[] = $recaudoDiscapacidad;
        }

        $fechaNacIso = $familiar->persona->fecha_nacimiento !== null
            ? $familiar->persona->fecha_nacimiento->format('Y-m-d')
            : null;
        $edadCalculada = $this->utilidades->calcularEdad($fechaNacIso);

        $this->renderVista('editar_familiar', [
            'familiar' => $familiar,
            'persona' => $familiar->persona,
            'edadCalculada' => $edadCalculada,
            'titular' => (object) [
                'empleado_id' => $titular?->empleado_id,
                'nombre' => $titularNombre,
                'cedula' => $titularCedula,
                'cargo' => $titularCargo,
            ],
            'empleados_preseleccionados' => $this->catalogos->empleadoParaSelectPorId((int) $familiar->empleado_id),
            'parentescos' => $this->catalogos->tiposParentesco(),
            'estados_civiles' => $this->catalogos->estadosCiviles(),
            'recaudos' => $recaudosVisibles,
            'recaudo_discapacidad' => $recaudoDiscapacidad,
            'discapacidad' => $discapacidadActual,
            'tipos_discapacidad' => $tiposDiscMap,
            'grados_discapacidad' => $this->catalogos->gradosDiscapacidad(),
            'es_parentesco_hijo' => $this->esParentescoHijo((int) $familiar->parentesco_id),
            'generos' => $this->catalogos->generos($generoActual),
            'genero_actual' => $generoActual,
        ]);
    }

    /**
     * [GET] Recalcula los recaudos del familiar al cambiar parentesco o titular preservando vigencia.
     * Recibe el ID del familiar y lee `parentesco_id` y `empleado_id` por query string.
     */
    public function recaudos(string $id): void
    {
        $familiaId = $this->idPositivo($id, 'familiar');
        if ($familiaId === null) {
            return;
        }

        $familiar = FamiliaresModel::query()->with('empleado')->find($familiaId);
        if (!$familiar instanceof FamiliaresModel) {
            $this->jsonError('Familiar no encontrado.', 404);
            return;
        }

        $query = $this->app->request()->query;
        $parentescoId = is_numeric($query['parentesco_id'] ?? null)
            ? (int) $query['parentesco_id']
            : (int) $familiar->parentesco_id;
        $empleadoId = is_numeric($query['empleado_id'] ?? null) && (int) $query['empleado_id'] > 0
            ? (int) $query['empleado_id']
            : (int) $familiar->empleado_id;

        $personaTitularId = (int) (EmpleadosModel::query()->whereKey($empleadoId)->value('persona_id') ?? 0);

        $this->jsonOk([
            'recaudos' => $this->catalogos->recaudosParaFamiliar($parentescoId, (int) $familiar->persona_id, $personaTitularId),
            'recaudo_discapacidad' => $this->catalogos->recaudoDiscapacidadUnificado((int) $familiar->persona_id),
        ]);
    }

    /**
     * Actualiza datos personales, titular, tomo/folio condicional, cédula < 9 años, discapacidad y documentos.
     * Recibe el ID del familiar por ruta y responde JSON 200 o error 404/422/500.
     */
    public function update(string $id): void
    {
        $familiaId = $this->idPositivo($id, 'familiar');
        if ($familiaId === null) {
            return;
        }

        $familiar = FamiliaresModel::query()->with('persona')->find($familiaId);
        if (!$familiar instanceof FamiliaresModel || !$familiar->persona instanceof PersonasModel) {
            $this->jsonError('Familiar no encontrado.', 404);
            return;
        }

        $input = new LimpiarInputs();
        $personaActual = $familiar->persona;

        $nuevoEmpleadoRaw = $input->get('empleado_id', $familiar->empleado_id);
        $nuevoEmpleadoId = is_numeric($nuevoEmpleadoRaw) && (int) $nuevoEmpleadoRaw > 0
            ? (int) $nuevoEmpleadoRaw
            : (int) $familiar->empleado_id;

        if ($nuevoEmpleadoId !== (int) $familiar->empleado_id) {
            if (!EmpleadosModel::query()->whereKey($nuevoEmpleadoId)->exists()) {
                $this->jsonError('El empleado titular seleccionado no existe.', 422);
                return;
            }
            $vinculoDuplicado = FamiliaresModel::query()
                ->where('empleado_id', $nuevoEmpleadoId)
                ->where('persona_id', (int) $personaActual->persona_id)
                ->where('familia_id', '!=', $familiaId)
                ->exists();
            if ($vinculoDuplicado) {
                $this->jsonError('Este familiar ya está asociado al trabajador titular seleccionado.', 422);
                return;
            }
        }

        $parentescoId = (int) $input->get('parentesco_id', (string) $familiar->parentesco_id);
        $requiereTomoFolio = $this->esParentescoHijo($parentescoId);

        $tomoEnviado = $input->get('tomo');
        $folioEnviado = $input->get('folio');
        if ($requiereTomoFolio) {
            $tomoStr = trim((string) ($tomoEnviado ?? $familiar->tomo));
            $folioStr = trim((string) ($folioEnviado ?? $familiar->folio));
            if ($tomoStr === '' || $folioStr === '' || !ctype_digit($tomoStr) || !ctype_digit($folioStr) || (int) $tomoStr <= 0 || (int) $folioStr <= 0) {
                $this->jsonError('El tomo y el folio del acta deben ser números positivos para hijos e hijas.', 422);
                return;
            }
            $tomo = (int) $tomoStr;
            $folio = (int) $folioStr;
        } else {
            $tomoStr = trim((string) ($tomoEnviado ?? ''));
            $folioStr = trim((string) ($folioEnviado ?? ''));
            if (($tomoStr !== '' && !ctype_digit($tomoStr)) || ($folioStr !== '' && !ctype_digit($folioStr))) {
                $this->jsonError('El tomo y el folio deben contener solo números.', 422);
                return;
            }
            $tomo = $tomoStr !== '' ? (int) $tomoStr : ($tomoEnviado === null ? (int) $familiar->tomo : 0);
            $folio = $folioStr !== '' ? (int) $folioStr : ($folioEnviado === null ? (int) $familiar->folio : 0);
        }

        $nacionalidad = substr(strtoupper((string) $input->get('nacionalidad', $personaActual->nacionalidad)), 0, 1);
        if (!in_array($nacionalidad, ['V', 'E'], true)) {
            $this->jsonError('Nacionalidad del familiar inválida.', 422);
            return;
        }

        $fechaDefecto = $personaActual->fecha_nacimiento instanceof \DateTimeInterface
            ? $personaActual->fecha_nacimiento->format('Y-m-d')
            : '';
        $fechaNacimiento = trim((string) $input->get('fecha_nacimiento', $fechaDefecto));
        $cedulaEnviada = $input->get('cedula');
        $cedulaRaw = $cedulaEnviada !== null ? trim((string) $cedulaEnviada) : (string) $personaActual->cedula;

        if ($cedulaRaw === '') {
            if (!$this->esMenorDeNueve($fechaNacimiento)) {
                $this->jsonError('La cédula del familiar es obligatoria para personas de 9 años o más.', 422);
                return;
            }
            $cedula = $this->generarCedulaTemporalUnica($nacionalidad, $tomo, $folio, (int) $personaActual->persona_id);
        } else {
            if (!ctype_digit($cedulaRaw) || (int) $cedulaRaw <= 0) {
                $this->jsonError('Nacionalidad o cédula del familiar inválida.', 422);
                return;
            }
            $cedula = (int) $cedulaRaw;
        }

        $duplicada = PersonasModel::query()
            ->where('nacionalidad', $nacionalidad)
            ->where('cedula', $cedula)
            ->where('persona_id', '!=', (int) $personaActual->persona_id)
            ->exists();

        if ($duplicada) {
            $this->jsonError('Ya existe otra persona con esa nacionalidad y cédula.', 422);
            return;
        }

        $estadoCivilRaw = $input->get('estado_civil_id', $personaActual->estado_civil_id);
        $estadoCivilId = is_numeric($estadoCivilRaw) && (int) $estadoCivilRaw > 0 ? (int) $estadoCivilRaw : null;

        $primerNombre = substr((string) $input->get('primer_nombre', $personaActual->primer_nombre), 0, 100);
        $segundoNombre = substr((string) $input->get('segundo_nombre', $personaActual->segundo_nombre), 0, 100);
        $primerApellido = substr((string) $input->get('primer_apellido', $personaActual->primer_apellido), 0, 100);
        $segundoApellido = substr((string) $input->get('segundo_apellido', $personaActual->segundo_apellido), 0, 100);
        $genero = substr((string) $input->get('genero', $personaActual->genero), 0, 20);

        $discapacidadValidada = $this->validarDiscapacidadOpcional($input);
        if (isset($discapacidadValidada['error'])) {
            $this->jsonError($discapacidadValidada['error'], 422);
            return;
        }

        $documentosPorTipo = CatalogosService::normalizarArchivosPorTipo($this->app->request()->files->getData());

        try {
            $documentosIds = $familiar->getConnection()->transaction(function () use (
                $familiar,
                $personaActual,
                $nuevoEmpleadoId,
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
                $discapacidadValidada,
                $documentosPorTipo
            ): array {
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
                    'empleado_id' => $nuevoEmpleadoId,
                    'parentesco_id' => $parentescoId,
                    'tomo' => $tomo,
                    'folio' => $folio,
                    'genero' => $genero,
                ]);
                $familiar->save();

                if (isset($discapacidadValidada['datos'])) {
                    $this->guardarDiscapacidadFamiliar((int) $personaActual->persona_id, $discapacidadValidada['datos']);
                }

                $procesados = [];
                if ($this->gestorDocumentos !== null) {
                    foreach ($documentosPorTipo as $tipoDocId => $archivoDoc) {
                        $nuevo = $this->gestorDocumentos->subir(
                            $archivoDoc,
                            (int) $tipoDocId,
                            $nuevoEmpleadoId,
                            (int) $familiar->familia_id
                        );
                        $procesados[] = (int) $nuevo->documentos_id;
                    }
                }

                return $procesados;
            });

            $this->jsonOk([
                'familia_id' => (int) $familiar->familia_id,
                'empleado_id' => $nuevoEmpleadoId,
                'cedula' => $cedula,
                'documentos_ids' => $documentosIds,
            ], 'Familiar actualizado correctamente.');
        } catch (Throwable $e) {
            $codigo = (int) $e->getCode();
            $this->jsonError(
                $codigo >= 400 && $codigo < 500 ? $e->getMessage() : 'No se pudo actualizar el familiar.',
                $codigo >= 400 && $codigo < 600 ? $codigo : 500
            );
        }
    }

    /**
     * Determina si un ID de parentesco corresponde a Hijo o Hija.
     * Recibe el ID de parentesco y retorna true cuando aplica tomo y folio.
     */
    private function esParentescoHijo(int $parentescoId): bool
    {
        $nombre = (string) (TipoParentescoModel::query()->whereKey($parentescoId)->value('parentesco') ?? '');
        if ($nombre !== '') {
            return preg_match('/^hij[oa]\b/iu', trim($nombre)) === 1;
        }

        return in_array($parentescoId, [3, 4], true);
    }

    /**
     * Evalúa si la fecha de nacimiento corresponde a una persona menor de 9 años.
     * Recibe fecha ISO (YYYY-MM-DD) y retorna true cuando edad < 9.
     */
    private function esMenorDeNueve(string $fechaNacimiento): bool
    {
        $fecha = DateTimeImmutable::createFromFormat('!Y-m-d', $fechaNacimiento);
        if (!$fecha instanceof DateTimeImmutable) {
            return false;
        }

        $hoy = new DateTimeImmutable('today');
        return $fecha <= $hoy && $fecha->diff($hoy)->y < 9;
    }

    /**
     * Genera una cédula numérica temporal (900000000-999999999) con tomo y folio sin colisiones en BD.
     * Recibe nacionalidad, tomo, folio y persona_id a excluir; retorna un entero único.
     */
    private function generarCedulaTemporalUnica(string $nacionalidad, int $tomo, int $folio, int $personaIdExcluir): int
    {
        $tomoBloque = str_pad((string) (abs($tomo) % 10000), 4, '0', STR_PAD_LEFT);
        $folioBloque = str_pad((string) (abs($folio) % 10000), 4, '0', STR_PAD_LEFT);
        $candidata = (int) ('9' . $tomoBloque . $folioBloque);

        if ($candidata < 900000000 || $candidata > 999999999) {
            $candidata = 900000001;
        }

        $intentos = 0;
        while (
            $intentos < 500
            && PersonasModel::query()
                ->where('nacionalidad', $nacionalidad)
                ->where('cedula', $candidata)
                ->where('persona_id', '!=', $personaIdExcluir)
                ->exists()
        ) {
            $candidata = $candidata >= 999999999 ? 900000001 : $candidata + 1;
            $intentos++;
        }

        return $candidata;
    }

    /**
     * Valida el bloque de discapacidad del familiar cuando `tiene_discapacidad = 1`.
     * Recibe LimpiarInputs y retorna el arreglo validado o un error 422.
     *
     * @return array{datos?: array<string, mixed>, error?: string}
     */
    private function validarDiscapacidadOpcional(LimpiarInputs $input): array
    {
        if ((string) $input->get('tiene_discapacidad', '0') !== '1') {
            return [];
        }

        $gradoRaw = $input->get('grado_discapacidad_id');
        $gradoId = is_numeric($gradoRaw) && (int) $gradoRaw > 0 ? (int) $gradoRaw : null;
        $tipoNombre = trim((string) $input->get('tipo_discapacidad', ''));
        $numeroCarnet = trim((string) $input->get('numero_carnet', ''));
        $entidadEmisora = trim((string) $input->get('entidad_emisora', 'CONAPDIS')) ?: 'CONAPDIS';
        $descripcion = trim((string) $input->get('descripcion_discapacidad', ''));
        $emisionRaw = trim((string) $input->get('fecha_emision_carnet', ''));
        $vencimientoRaw = trim((string) $input->get('fecha_vencimiento_carnet', ''));

        if ($gradoId === null || !GradoDiscapacidadModel::query()->whereKey($gradoId)->exists()) {
            return ['error' => 'Debe seleccionar un grado de discapacidad válido.'];
        }
        if ($numeroCarnet === '') {
            return ['error' => 'El número de carnet CONAPDIS es obligatorio al asignar discapacidad.'];
        }
        if ($emisionRaw !== '' && $vencimientoRaw !== '' && $vencimientoRaw < $emisionRaw) {
            return ['error' => 'La fecha de vencimiento del carnet no puede ser menor que la fecha de emisión.'];
        }

        return [
            'datos' => [
                'dt_discapacidad_id' => is_numeric($input->get('dt_discapacidad_id')) ? (int) $input->get('dt_discapacidad_id') : 0,
                'grado_discapacidad_id' => $gradoId,
                'numero_carnet' => substr($numeroCarnet, 0, 30),
                'entidad_emisora' => substr($entidadEmisora, 0, 100),
                'descripcion' => $descripcion !== '' ? $descripcion : null,
                'dirigido' => $tipoNombre !== '' ? substr($tipoNombre, 0, 30) : 'Familiar',
                'fecha_emision_carnet' => preg_match('/^\d{4}-\d{2}-\d{2}$/', $emisionRaw) === 1 ? $emisionRaw : date('Y-m-d'),
                'fecha_vencimiento_carnet' => preg_match('/^\d{4}-\d{2}-\d{2}$/', $vencimientoRaw) === 1
                    ? $vencimientoRaw
                    : date('Y-m-d', strtotime('+5 years')),
            ],
        ];
    }

    /**
     * Crea o actualiza el registro de discapacidad del familiar en `detalle_discapacidad`.
     * Recibe el ID de persona y el arreglo validado de discapacidad.
     *
     * @param array<string, mixed> $datos
     */
    private function guardarDiscapacidadFamiliar(int $personaId, array $datos): void
    {
        $dtId = (int) ($datos['dt_discapacidad_id'] ?? 0);
        unset($datos['dt_discapacidad_id']);

        $existente = $dtId > 0
            ? DetalleDiscapacidadModel::query()->where('dt_discapacidad_id', $dtId)->where('persona_id', $personaId)->first()
            : DetalleDiscapacidadModel::query()->where('persona_id', $personaId)->orderByDesc('dt_discapacidad_id')->first();

        if ($existente instanceof DetalleDiscapacidadModel) {
            $existente->fill($datos);
            $existente->save();
            return;
        }

        DetalleDiscapacidadModel::query()->create(array_merge(['persona_id' => $personaId], $datos));
    }
}
