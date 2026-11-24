<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\LimpiarInputs;
use App\Model\Personas\DireccionModel;
use App\Model\Personas\EmpleadosModel;
use App\Model\Personas\PersonasModel;
use App\Model\Personas\TelefonoPersonasModel;
use App\Services\CatalogosService;
use App\Services\EmpleadosService;
use App\Services\GestorDocumentos;
use App\Utils\Config;
use flight\Engine;
use InvalidArgumentException;
use Throwable;

/**
 * Controlador para la vista y actualización integral de empleados:
 * - Catálogos activos (`activo = true`)
 * - Evaluación dinámica de recaudos vigentes (`Vigente` / `No vigente`)
 * - Gestión de múltiples teléfonos con máximo un (1) teléfono de emergencia
 */
final class EditarEmpleadoController extends BaseController
{
    private EmpleadosService $empleadosService;

    public function __construct(
        Engine $app,
        Config $config,
        private CatalogosService $catalogos,
        private ?GestorDocumentos $gestorDocumentos = null,
        ?EmpleadosService $empleadosService = null,
    ) {
        parent::__construct($app, $config);
        $this->empleadosService = $empleadosService ?? new EmpleadosService();
    }

    public function render(?string $id = null): void
    {
        $idRaw = $id ?? ($this->app->request()->query['id'] ?? null);
        if (!is_numeric($idRaw) || (int) $idRaw <= 0) {
            $this->app->redirect('/empleado/registros');
            return;
        }

        $empleadoId = (int) $idRaw;
        $empleado = EmpleadosModel::query()
            ->with([
                'persona.telefonos',
                'direccion.parroquia.municipio',
            ])
            ->find($empleadoId);

        $persona = $empleado instanceof EmpleadosModel ? $empleado->getRelationValue('persona') : null;
        if (!$empleado instanceof EmpleadosModel || !$persona instanceof PersonasModel) {
            $this->app->halt(404, 'Empleado no encontrado');
            return;
        }

        $telefonos = $this->obtenerTelefonosNormalizados($empleadoId, $persona);
        $telPrincipal = '';
        $telEmergencia = '';
        foreach ($telefonos as $tel) {
            if ($tel['emergencia'] && $telEmergencia === '') {
                $telEmergencia = $tel['numero_telefono'];
            } elseif (!$tel['emergencia'] && $telPrincipal === '') {
                $telPrincipal = $tel['numero_telefono'];
            }
        }

        $direccion = $empleado->getRelationValue('direccion');
        $parroquia = $direccion instanceof DireccionModel ? $direccion->getRelationValue('parroquia') : null;
        $municipio = $parroquia instanceof \App\Model\Ubicacion\ParroquiasModel ? $parroquia->getRelationValue('municipio') : null;
        $municipioActualId = $parroquia instanceof \App\Model\Ubicacion\ParroquiasModel ? (int) $parroquia->municipio_id : 0;
        $estadoActualId = $municipio instanceof \App\Model\Ubicacion\MunicipiosModel ? (int) $municipio->estado_id : 0;

        $recaudos = $this->catalogos->recaudosParaEmpleado(
            (int) ($persona->estado_civil_id ?? 0),
            (int) $empleado->nivel_academico_id,
            (int) $persona->persona_id,
            (int) $empleado->cargo_id
        );
        $resumenRecaudos = $this->catalogos->resumenRecaudos($recaudos);

        $this->renderVista('editar_empleado', [
            'empleado' => $empleado,
            'persona' => $persona,
            'direccion' => $direccion,
            'telefonos' => $telefonos,
            'telefono_principal' => $telPrincipal,
            'telefono_emergencia' => $telEmergencia,
            'estados_civiles' => $this->catalogos->estadosCiviles(),
            'dependencias' => $this->catalogos->dependencias(true),
            'departamentos' => $this->catalogos->departamentos(true),
            'cargos' => $this->catalogos->cargos(true),
            'estatus' => $this->catalogos->estatus(true),
            'niveles_academicos' => $this->catalogos->nivelesAcademicos(true),
            'carreras' => $this->catalogos->carreras(true),
            'estados' => $this->catalogos->estados(true),
            'municipios' => $estadoActualId > 0 ? $this->catalogos->municipiosPorEstado($estadoActualId, true) : [],
            'parroquias' => $municipioActualId > 0 ? $this->catalogos->parroquiasPorMunicipio($municipioActualId, true) : [],
            'estado_seleccionado' => $estadoActualId > 0 ? (string) $estadoActualId : '',
            'municipio_seleccionado' => $municipioActualId > 0 ? (string) $municipioActualId : '',
            'tipos_documentos' => $this->catalogos->tiposDocumentos(),
            'recaudos' => $recaudos,
            'resumen_recaudos' => $resumenRecaudos,
            'generos' => $this->catalogos->generos((string) $persona->genero),
        ]);
    }

    /**
     * [GET] Recalcula dinámicamente los recaudos requeridos y cargados vigentes al cambiar cargo, nivel académico o estado civil.
     */
    public function recaudos(string $id): void
    {
        $empleadoId = $this->idPositivo($id, 'empleado');
        if ($empleadoId === null) {
            return;
        }

        $empleado = EmpleadosModel::query()->with('persona')->find($empleadoId);
        $persona = $empleado instanceof EmpleadosModel ? $empleado->getRelationValue('persona') : null;
        if (!$empleado instanceof EmpleadosModel || !$persona instanceof PersonasModel) {
            $this->jsonError('Trabajador no encontrado.', 404);
            return;
        }

        $query = $this->app->request()->query;
        $estadoCivilId = is_numeric($query['estado_civil_id'] ?? null)
            ? (int) $query['estado_civil_id']
            : (int) ($persona->estado_civil_id ?? 0);
        $nivelAcademicoId = is_numeric($query['nivel_academico_id'] ?? null)
            ? (int) $query['nivel_academico_id']
            : (int) $empleado->nivel_academico_id;
        $cargoId = is_numeric($query['cargo_id'] ?? null)
            ? (int) $query['cargo_id']
            : (int) $empleado->cargo_id;

        $recaudos = $this->catalogos->recaudosParaEmpleado(
            $estadoCivilId,
            $nivelAcademicoId,
            (int) $empleado->persona_id,
            $cargoId
        );

        $this->jsonOk([
            'recaudos' => $recaudos,
            'resumen' => $this->catalogos->resumenRecaudos($recaudos),
        ]);
    }

    /**
     * [PUT|POST] Actualiza datos personales, laborales, ubicación, múltiples teléfonos y documentos vigentes.
     */
    public function update(string $id): void
    {
        $empleadoId = $this->idPositivo($id, 'empleado');
        if ($empleadoId === null) {
            return;
        }

        $empleado = EmpleadosModel::query()->with(['persona.telefonos', 'direccion'])->find($empleadoId);
        $persona = $empleado instanceof EmpleadosModel ? $empleado->getRelationValue('persona') : null;
        if (!$empleado instanceof EmpleadosModel || !$persona instanceof PersonasModel) {
            $this->jsonError('Trabajador no encontrado.', 404);
            return;
        }

        $input = new LimpiarInputs();
        $dirRel = $empleado->getRelationValue('direccion');
        $dirActual = $dirRel instanceof DireccionModel ? $dirRel : null;

        $nacionalidad = substr(strtoupper((string) $input->get('nacionalidad', $persona->nacionalidad)), 0, 1);
        $cedula = (int) $input->get('cedula', (string) $persona->cedula);

        if (!in_array($nacionalidad, ['V', 'E'], true) || $cedula <= 0) {
            $this->jsonError('Nacionalidad o cédula inválida.', 422);
            return;
        }

        $duplicada = PersonasModel::query()
            ->where('nacionalidad', $nacionalidad)
            ->where('cedula', $cedula)
            ->where('persona_id', '!=', (int) $empleado->persona_id)
            ->exists();

        if ($duplicada) {
            $this->jsonError('Ya existe otra persona registrada con esa nacionalidad y cédula.', 422);
            return;
        }

        $telefonosEntrada = $this->extraerTelefonosDeSolicitud($input);
        if (!isset($telefonosEntrada['modo'])) {
            $this->jsonError($telefonosEntrada['error'] ?? 'Teléfonos inválidos.', 422);
            return;
        }

        $primerNombre = substr((string) $input->get('primer_nombre', $persona->primer_nombre), 0, 100);
        $segundoNombre = substr((string) $input->get('segundo_nombre', $persona->segundo_nombre), 0, 100);
        $primerApellido = substr((string) $input->get('primer_apellido', $persona->primer_apellido), 0, 100);
        $segundoApellido = substr((string) $input->get('segundo_apellido', $persona->segundo_apellido), 0, 100);
        $genero = (string) $input->get('genero', $persona->genero);
        $fechaNacDefecto = $persona->fecha_nacimiento instanceof \DateTimeInterface
            ? $persona->fecha_nacimiento->format('Y-m-d')
            : '';
        $fechaNacimiento = (string) $input->get('fecha_nacimiento', $fechaNacDefecto);
        $estadoCivilId = (int) $input->get('estado_civil_id', (string) ($persona->estado_civil_id ?? 0));

        $dependenciaId = (int) $input->get('dependencia_id', (string) $empleado->dependencia_id);
        $departamentoId = (int) $input->get('departamento_id', (string) $empleado->departamento_id);
        $cargoId = (int) $input->get('cargo_id', (string) $empleado->cargo_id);
        $estatusId = (int) $input->get('estatus_id', (string) $empleado->estatus_id);
        $nivelAcademicoId = (int) $input->get('nivel_academico_id', (string) $empleado->nivel_academico_id);
        $carreraRaw = $input->get('carrera_id', $empleado->carrera_id);
        $carreraId = is_numeric($carreraRaw) && (int) $carreraRaw > 0 ? (int) $carreraRaw : null;

        $codigoRaw = (string) $input->get('codigo_empleado', (string) ($empleado->codigo_empleado ?? $cedula));
        $codigoEmpleado = substr($codigoRaw !== '' ? $codigoRaw : (string) $cedula, 0, 10);

        $estatusActualRaw = $input->get('estatus_actual', $empleado->estatus_actual);
        $estatusActual = in_array((int) $estatusActualRaw, [0, 1, 3], true)
            ? (int) $estatusActualRaw
            : (int) $empleado->estatus_actual;

        $documentosPorTipo = CatalogosService::normalizarArchivosPorTipo($this->app->request()->files->getData());

        $correoRaw = (string) $input->get('correo', (string) ($empleado->correo ?? ''));
        $correo = $correoRaw !== '' ? substr($correoRaw, 0, 255) : null;

        $parroquiaRaw = $input->get('parroquia_id', $dirActual?->parroquia_id);
        $parroquiaId = is_numeric($parroquiaRaw) && (int) $parroquiaRaw > 0 ? (int) $parroquiaRaw : null;

        try {
            $resultado = $empleado->getConnection()->transaction(function () use (
                $persona,
                $empleado,
                $dirActual,
                $primerNombre,
                $segundoNombre,
                $primerApellido,
                $segundoApellido,
                $genero,
                $cedula,
                $nacionalidad,
                $fechaNacimiento,
                $estadoCivilId,
                $dependenciaId,
                $departamentoId,
                $estatusId,
                $cargoId,
                $nivelAcademicoId,
                $carreraId,
                $codigoEmpleado,
                $estatusActual,
                $correo,
                $parroquiaId,
                $input,
                $telefonosEntrada,
                $documentosPorTipo
            ): array {
                $persona->fill([
                    'primer_nombre' => $primerNombre,
                    'segundo_nombre' => $segundoNombre,
                    'primer_apellido' => $primerApellido,
                    'segundo_apellido' => $segundoApellido,
                    'genero' => $genero,
                    'cedula' => $cedula,
                    'nacionalidad' => $nacionalidad,
                    'fecha_nacimiento' => $fechaNacimiento,
                    'estado_civil_id' => $estadoCivilId > 0 ? $estadoCivilId : null,
                ]);
                $persona->save();

                $empleado->fill([
                    'dependencia_id' => $dependenciaId,
                    'departamento_id' => $departamentoId,
                    'estatus_id' => $estatusId,
                    'cargo_id' => $cargoId,
                    'nivel_academico_id' => $nivelAcademicoId,
                    'carrera_id' => $carreraId,
                    'codigo_empleado' => $codigoEmpleado,
                    'estatus_actual' => $estatusActual,
                    'correo' => $correo,
                ]);
                $empleado->save();

                if ($parroquiaId !== null) {
                    DireccionModel::query()->updateOrCreate(
                        ['empleado_id' => (int) $empleado->empleado_id],
                        [
                            'parroquia_id' => $parroquiaId,
                            'vivienda' => substr((string) $input->get('vivienda', $dirActual?->vivienda ?? 'Casa'), 0, 30),
                            'calle' => substr((string) $input->get('calle', $dirActual?->calle ?? 'S/N'), 0, 30),
                            'nombre_urb' => substr((string) $input->get('nombre_urb', $dirActual?->nombre_urb ?? 'N/A'), 0, 100),
                            'piso_edificio' => substr((string) $input->get('piso_edificio', $dirActual?->piso_edificio ?? 'N/A'), 0, 10),
                            'numero_vivienda' => substr((string) $input->get('numero_vivienda', $dirActual?->numero_vivienda ?? 'S/N'), 0, 10),
                            'descripcion' => $input->get('descripcion', $dirActual?->descripcion)
                                ? substr((string) $input->get('descripcion', $dirActual?->descripcion), 0, 255)
                                : null,
                        ]
                    );
                }

                $this->sincronizarTelefonosPersona((int) $persona->persona_id, $telefonosEntrada);

                $documentosProcesados = [];
                if ($this->gestorDocumentos !== null) {
                    foreach ($documentosPorTipo as $tipoDocId => $archivoDoc) {
                        $nuevo = $this->gestorDocumentos->subir($archivoDoc, (int) $tipoDocId, (int) $empleado->empleado_id);
                        $documentosProcesados[] = (int) $nuevo->documentos_id;
                    }
                }

                $activadoAutomaticamente = false;
                if (
                    (int) $empleado->estatus_actual === 3
                    && $this->catalogos->recaudosObligatoriosCompletosEmpleado(
                        $estadoCivilId,
                        $nivelAcademicoId,
                        (int) $persona->persona_id,
                        $cargoId
                    )
                ) {
                    $empleado->estatus_actual = 1;
                    $empleado->save();
                    $activadoAutomaticamente = true;
                }

                return [
                    'empleado_id' => (int) $empleado->empleado_id,
                    'estatus_actual' => (int) $empleado->estatus_actual,
                    'activado_automaticamente' => $activadoAutomaticamente,
                    'documentos_ids' => $documentosProcesados,
                ];
            });

            $mensaje = $resultado['activado_automaticamente']
                ? 'Expediente actualizado y documentos obligatorios completos. El trabajador pasó automáticamente a estado Activo.'
                : 'Datos del trabajador actualizados correctamente.';

            $this->jsonOk($resultado, $mensaje);
        } catch (Throwable $e) {
            $codigo = (int) $e->getCode();
            $this->jsonError(
                $codigo >= 400 && $codigo < 500 ? $e->getMessage() : 'No se pudo actualizar el trabajador.',
                $codigo >= 400 && $codigo < 600 ? $codigo : 500
            );
        }
    }

    /**
     * Obtiene la lista de teléfonos del trabajador integrando `fn_obtener_telefonos_empleado` con respaldo Eloquent.
     *
     * @return list<array{telefono_id: int|null, numero_telefono: string, emergencia: bool}>
     */
    private function obtenerTelefonosNormalizados(int $empleadoId, PersonasModel $persona): array
    {
        $lista = [];

        try {
            $desdeFuncion = $this->empleadosService->obtenerTelefonos($empleadoId);
            $itemsFn = is_array($desdeFuncion['telefonos'] ?? null) ? $desdeFuncion['telefonos'] : $desdeFuncion;
            if (is_array($itemsFn) && isset($itemsFn[0]) && is_array($itemsFn[0])) {
                foreach ($itemsFn as $fila) {
                    if (!is_array($fila)) {
                        continue;
                    }
                    $numero = trim((string) ($fila['numero_telefono'] ?? $fila['telefono'] ?? ''));
                    if ($numero === '') {
                        continue;
                    }
                    $lista[] = [
                        'telefono_id' => isset($fila['telefono_id']) ? (int) $fila['telefono_id'] : null,
                        'numero_telefono' => $numero,
                        'emergencia' => (bool) ($fila['emergencia'] ?? false),
                    ];
                }
            }
        } catch (Throwable) {
            // Respaldo Eloquent (SQLite / entornos sin función PL/pgSQL)
        }

        if ($lista === []) {
            $telefonosCol = $persona->getRelationValue('telefonos');
            if ($telefonosCol instanceof \Illuminate\Database\Eloquent\Collection) {
                foreach ($telefonosCol->sortBy('telefono_id') as $tel) {
                    if ($tel instanceof TelefonoPersonasModel) {
                        $lista[] = [
                            'telefono_id' => (int) $tel->telefono_id,
                            'numero_telefono' => (string) $tel->numero_telefono,
                            'emergencia' => (bool) $tel->emergencia,
                        ];
                    }
                }
            }
        }

        if ($lista === []) {
            $lista[] = [
                'telefono_id' => null,
                'numero_telefono' => '',
                'emergencia' => false,
            ];
        }

        return $lista;
    }

    /**
     * Extrae y valida la lista de teléfonos desde `telefonos_numero[]` o los campos simples de respaldo.
     * Garantiza estrictamente que como máximo un (1) teléfono tenga `emergencia = true`.
     *
     * @return array{modo: 'lista'|'legacy'|'ninguno', items?: list<array{numero_telefono: string, emergencia: bool}>, principal?: string, emergencia?: string}|array{error: string}
     */
    private function extraerTelefonosDeSolicitud(LimpiarInputs $input): array
    {
        $todo = $input->todo();
        $datosReq = $this->app->request()->data->getData();

        $numerosRaw = $todo['telefonos_numero'] ?? $datosReq['telefonos_numero'] ?? null;
        $emergenciasRaw = $todo['telefonos_emergencia'] ?? $datosReq['telefonos_emergencia'] ?? [];

        if (is_array($numerosRaw)) {
            $items = [];
            $totalEmergencia = 0;

            foreach ($numerosRaw as $idx => $numRaw) {
                $numero = substr(trim((string) $numRaw), 0, 20);
                if ($numero === '') {
                    continue;
                }

                $valorEmg = is_array($emergenciasRaw) ? ($emergenciasRaw[$idx] ?? false) : false;
                $esEmergencia = $valorEmg === '1' || $valorEmg === 1 || $valorEmg === true || $valorEmg === 'true';
                if ($esEmergencia) {
                    $totalEmergencia++;
                }

                $items[] = [
                    'numero_telefono' => $numero,
                    'emergencia' => $esEmergencia,
                ];
            }

            if ($totalEmergencia > 1) {
                return ['error' => 'Solo se permite registrar un (1) teléfono de emergencia por trabajador.'];
            }

            return ['modo' => 'lista', 'items' => $items];
        }

        $telPrincipal = substr(trim((string) $input->get('telefono_principal', '')), 0, 20);
        $telEmergencia = substr(trim((string) $input->get('telefono_emergencia', '')), 0, 20);

        if ($telPrincipal !== '' || $telEmergencia !== '') {
            return [
                'modo' => 'legacy',
                'principal' => $telPrincipal,
                'emergencia' => $telEmergencia,
            ];
        }

        return ['modo' => 'ninguno'];
    }

    /**
     * @param array{modo: 'lista'|'legacy'|'ninguno', items?: list<array{numero_telefono: string, emergencia: bool}>, principal?: string, emergencia?: string} $datosTelefonos
     */
    private function sincronizarTelefonosPersona(int $personaId, array $datosTelefonos): void
    {
        if ($datosTelefonos['modo'] === 'lista') {
            $items = $datosTelefonos['items'] ?? [];
            $conteoEmergencia = 0;

            TelefonoPersonasModel::query()->where('persona_id', $personaId)->delete();

            foreach ($items as $item) {
                $esEmergencia = $item['emergencia'] && $conteoEmergencia === 0;
                if ($esEmergencia) {
                    $conteoEmergencia++;
                }

                TelefonoPersonasModel::query()->create([
                    'persona_id' => $personaId,
                    'numero_telefono' => $item['numero_telefono'],
                    'emergencia' => $esEmergencia,
                ]);
            }

            return;
        }

        if ($datosTelefonos['modo'] === 'legacy') {
            $principal = $datosTelefonos['principal'] ?? '';
            $emergencia = $datosTelefonos['emergencia'] ?? '';

            if ($principal !== '') {
                TelefonoPersonasModel::query()->updateOrCreate(
                    ['persona_id' => $personaId, 'emergencia' => false],
                    ['numero_telefono' => $principal]
                );
            }

            if ($emergencia !== '') {
                TelefonoPersonasModel::query()->where('persona_id', $personaId)->where('emergencia', true)->delete();
                TelefonoPersonasModel::query()->create([
                    'persona_id' => $personaId,
                    'numero_telefono' => $emergencia,
                    'emergencia' => true,
                ]);
            }

            if (TelefonoPersonasModel::query()->where('persona_id', $personaId)->where('emergencia', true)->count() > 1) {
                throw new InvalidArgumentException('Solo puede existir un teléfono de emergencia.', 422);
            }
        }
    }
}
