<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\LimpiarInputs;
use App\Model\Beneficios\DetalleDiscapacidadModel;
use App\Model\Beneficios\GradoDiscapacidadModel;
use App\Model\Personas\DireccionModel;
use App\Model\Personas\EmpleadosModel;
use App\Model\Personas\PersonasModel;
use App\Model\Personas\TelefonoPersonasModel;
use App\Services\CatalogosService;
use App\Services\EmpleadosService;
use App\Services\GestorDocumentos;
use App\Services\UtilidadesService;
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
    /** @var list<string> */
    private const OPERADORAS_TELEFONO = ['0412', '0416', '0424', '0422', '0414', '0243'];

    /** @var list<string> */
    private const TIPOS_VIVIENDA = ['Casa', 'Departamento'];

    private EmpleadosService $empleadosService;
    private UtilidadesService $utilidades;

    public function __construct(
        Engine $app,
        Config $config,
        private CatalogosService $catalogos,
        private ?GestorDocumentos $gestorDocumentos = null,
        ?EmpleadosService $empleadosService = null,
        ?UtilidadesService $utilidades = null,
    ) {
        parent::__construct($app, $config);
        $this->empleadosService = $empleadosService ?? new EmpleadosService();
        $this->utilidades = $utilidades ?? new UtilidadesService();
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
                'persona.detallesDiscapacidad.gradoDiscapacidad',
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

        $detallesDisc = $persona->getRelationValue('detallesDiscapacidad');
        $discapacidadActual = $detallesDisc instanceof \Illuminate\Database\Eloquent\Collection
            ? $detallesDisc->sortByDesc('dt_discapacidad_id')->first()
            : null;
        if (!$discapacidadActual instanceof DetalleDiscapacidadModel) {
            $discapacidadActual = null;
        }

        $tiposDiscapacidadMap = [];
        foreach ($this->catalogos->tiposDiscapacidad() as $nombreTipo) {
            $tiposDiscapacidadMap[(string) $nombreTipo] = (string) $nombreTipo;
        }
        $operadorasMap = array_combine(self::OPERADORAS_TELEFONO, self::OPERADORAS_TELEFONO);
        $viviendaSeleccionada = $this->normalizarTipoVivienda((string) ($direccion instanceof DireccionModel ? $direccion->vivienda : ''));
        $fechaNacIso = $persona->fecha_nacimiento !== null
            ? $persona->fecha_nacimiento->format('Y-m-d')
            : null;
        $edadCalculada = $this->utilidades->calcularEdad($fechaNacIso);

        $this->renderVista('editar_empleado', [
            'empleado' => $empleado,
            'persona' => $persona,
            'edadCalculada' => $edadCalculada,
            'direccion' => $direccion,
            'vivienda_seleccionada' => $viviendaSeleccionada,
            'telefonos' => $telefonos,
            'operadoras_telefono' => $operadorasMap,
            'telefono_principal' => $telPrincipal,
            'telefono_emergencia' => $telEmergencia,
            'discapacidad' => $discapacidadActual,
            'tipos_discapacidad' => $tiposDiscapacidadMap,
            'grados_discapacidad' => $this->catalogos->gradosDiscapacidad(),
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

        $codigoRaw = trim((string) $input->get('codigo_empleado', (string) ($empleado->codigo_empleado ?? $cedula)));
        $codigoEmpleado = substr($codigoRaw !== '' ? $codigoRaw : (string) $cedula, 0, 10);
        if ($codigoEmpleado === '') {
            $this->jsonError('El código de empleado es obligatorio.', 422);
            return;
        }

        $codigoDuplicado = EmpleadosModel::query()
            ->where('codigo_empleado', $codigoEmpleado)
            ->where('empleado_id', '!=', $empleadoId)
            ->exists();

        if ($codigoDuplicado) {
            $this->jsonError('El código de empleado ya está asignado a otro trabajador.', 422);
            return;
        }

        $estatusActualRaw = $input->get('estatus_actual', $empleado->estatus_actual);
        $estatusActual = in_array((int) $estatusActualRaw, [0, 1, 3], true)
            ? (int) $estatusActualRaw
            : (int) $empleado->estatus_actual;

        $documentosPorTipo = CatalogosService::normalizarArchivosPorTipo($this->app->request()->files->getData());

        $correoRaw = (string) $input->get('correo', (string) ($empleado->correo ?? ''));
        $correo = $correoRaw !== '' ? substr($correoRaw, 0, 255) : null;

        $parroquiaRaw = $input->get('parroquia_id', $dirActual?->parroquia_id);
        $parroquiaId = is_numeric($parroquiaRaw) && (int) $parroquiaRaw > 0 ? (int) $parroquiaRaw : null;

        $direccionValidada = $this->validarDireccionEntrada($input, $dirActual);
        if (isset($direccionValidada['error'])) {
            $this->jsonError($direccionValidada['error'], 422);
            return;
        }

        $discapacidadValidada = $this->validarDiscapacidadEntrada($input);
        if (isset($discapacidadValidada['error'])) {
            $this->jsonError($discapacidadValidada['error'], 422);
            return;
        }

        try {
            $resultado = $empleado->getConnection()->transaction(function () use (
                $persona,
                $empleado,
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
                $direccionValidada,
                $discapacidadValidada,
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

                if ($parroquiaId !== null && isset($direccionValidada['datos'])) {
                    DireccionModel::query()->updateOrCreate(
                        ['empleado_id' => (int) $empleado->empleado_id],
                        array_merge(['parroquia_id' => $parroquiaId], $direccionValidada['datos'])
                    );
                }

                if (isset($discapacidadValidada['datos'])) {
                    $this->guardarDiscapacidadPersona((int) $persona->persona_id, $discapacidadValidada['datos']);
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
     * Obtiene los teléfonos del trabajador y los divide en operadora y número de 7 dígitos.
     * Recibe el ID del empleado y el modelo Persona; retorna la lista normalizada para la vista.
     *
     * @return list<array{telefono_id: int|null, numero_telefono: string, operadora: string, digitos: string, emergencia: bool}>
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
                    $partes = $this->dividirTelefono($numero);
                    $lista[] = [
                        'telefono_id' => isset($fila['telefono_id']) ? (int) $fila['telefono_id'] : null,
                        'numero_telefono' => $numero,
                        'operadora' => $partes['operadora'],
                        'digitos' => $partes['digitos'],
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
                        $partes = $this->dividirTelefono((string) $tel->numero_telefono);
                        $lista[] = [
                            'telefono_id' => (int) $tel->telefono_id,
                            'numero_telefono' => (string) $tel->numero_telefono,
                            'operadora' => $partes['operadora'],
                            'digitos' => $partes['digitos'],
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
                'operadora' => '',
                'digitos' => '',
                'emergencia' => false,
            ];
        }

        return $lista;
    }

    /**
     * Extrae y valida teléfonos desde operadora+dígitos o `telefonos_numero[]` concatenando con guion.
     * Recibe LimpiarInputs y retorna el arreglo validado o un mensaje de error.
     *
     * @return array{modo: 'lista'|'legacy'|'ninguno', items?: list<array{numero_telefono: string, emergencia: bool}>, principal?: string, emergencia?: string}|array{error: string}
     */
    private function extraerTelefonosDeSolicitud(LimpiarInputs $input): array
    {
        $todo = $input->todo();
        $datosReq = $this->app->request()->data->getData();

        $operadorasRaw = $todo['telefonos_operadora'] ?? $datosReq['telefonos_operadora'] ?? null;
        $digitosRaw = $todo['telefonos_digitos'] ?? $datosReq['telefonos_digitos'] ?? null;
        $numerosRaw = $todo['telefonos_numero'] ?? $datosReq['telefonos_numero'] ?? null;
        $emergenciasRaw = $todo['telefonos_emergencia'] ?? $datosReq['telefonos_emergencia'] ?? [];

        if (is_array($operadorasRaw) && is_array($digitosRaw)) {
            $items = [];
            $totalEmergencia = 0;

            foreach ($digitosRaw as $idx => $digRaw) {
                $operadora = trim((string) ($operadorasRaw[$idx] ?? ''));
                $digitos = trim((string) $digRaw);

                if ($operadora === '' && $digitos === '') {
                    continue;
                }
                if (!in_array($operadora, self::OPERADORAS_TELEFONO, true)) {
                    return ['error' => 'Selecciona una operadora válida (0412, 0416, 0424, 0422, 0414 o 0243).'];
                }
                if (preg_match('/^\d{7}$/', $digitos) !== 1) {
                    return ['error' => 'Cada número telefónico debe contener exactamente 7 dígitos numéricos.'];
                }

                $valorEmg = is_array($emergenciasRaw) ? ($emergenciasRaw[$idx] ?? false) : false;
                $esEmergencia = $valorEmg === '1' || $valorEmg === 1 || $valorEmg === true || $valorEmg === 'true';
                if ($esEmergencia) {
                    $totalEmergencia++;
                }

                $items[] = [
                    'numero_telefono' => "{$operadora}-{$digitos}",
                    'emergencia' => $esEmergencia,
                ];
            }

            if ($totalEmergencia > 1) {
                return ['error' => 'Solo se permite registrar un (1) teléfono de emergencia por trabajador.'];
            }

            return ['modo' => 'lista', 'items' => $items];
        }

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

    /**
     * Normaliza el tipo de vivienda almacenado a las opciones válidas `Casa` o `Departamento`.
     * Recibe el texto actual de vivienda y retorna el valor normalizado para el selector.
     */
    private function normalizarTipoVivienda(string $vivienda): string
    {
        $limpio = strtolower(trim($vivienda));
        if ($limpio === 'departamento' || $limpio === 'apartamento') {
            return 'Departamento';
        }
        if ($limpio === 'casa') {
            return 'Casa';
        }

        return '';
    }

    /**
     * Divide un teléfono con guion (ej. `0412-3456789`) en operadora y 7 dígitos.
     * Recibe el número completo y retorna un arreglo con `operadora` y `digitos`.
     *
     * @return array{operadora: string, digitos: string}
     */
    private function dividirTelefono(string $numero): array
    {
        $limpio = trim($numero);
        if (str_contains($limpio, '-')) {
            [$op, $dig] = explode('-', $limpio, 2);
            $opLimpia = preg_replace('/\D/', '', $op) ?? '';
            $digLimpios = substr(preg_replace('/\D/', '', $dig) ?? '', 0, 7);

            return [
                'operadora' => in_array($opLimpia, self::OPERADORAS_TELEFONO, true) ? $opLimpia : '',
                'digitos' => $digLimpios,
            ];
        }

        $soloDigitos = preg_replace('/\D/', '', $limpio) ?? '';
        if (strlen($soloDigitos) >= 11) {
            $op = substr($soloDigitos, 0, 4);

            return [
                'operadora' => in_array($op, self::OPERADORAS_TELEFONO, true) ? $op : '',
                'digitos' => substr($soloDigitos, 4, 7),
            ];
        }

        return ['operadora' => '', 'digitos' => substr($soloDigitos, 0, 7)];
    }

    /**
     * Valida los campos de dirección según el tipo de vivienda (`Casa` o `Departamento`).
     * Recibe LimpiarInputs y la dirección actual; retorna datos listos para BD o error 422.
     *
     * @return array{datos?: array<string, mixed>, error?: string}
     */
    private function validarDireccionEntrada(LimpiarInputs $input, ?DireccionModel $dirActual): array
    {
        $hayCamposDireccion = $input->get('vivienda') !== null
            || $input->get('calle') !== null
            || $input->get('nombre_urb') !== null
            || $input->get('piso_edificio') !== null
            || $input->get('numero_vivienda') !== null
            || $input->get('descripcion') !== null;

        if (!$hayCamposDireccion) {
            if ($dirActual === null) {
                return [];
            }

            return [
                'datos' => [
                    'vivienda' => substr((string) $dirActual->vivienda, 0, 30),
                    'calle' => substr((string) $dirActual->calle, 0, 30),
                    'nombre_urb' => substr((string) $dirActual->nombre_urb, 0, 100),
                    'piso_edificio' => substr((string) $dirActual->piso_edificio, 0, 10),
                    'numero_vivienda' => substr((string) $dirActual->numero_vivienda, 0, 10),
                    'descripcion' => $dirActual->descripcion !== null ? substr((string) $dirActual->descripcion, 0, 255) : null,
                ],
            ];
        }

        $vivienda = trim((string) $input->get('vivienda', $dirActual?->vivienda ?? ''));
        if (!in_array($vivienda, self::TIPOS_VIVIENDA, true)) {
            return ['error' => 'El tipo de vivienda debe ser Casa o Departamento.'];
        }

        $regexSoloTexto = '/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/u';
        $regexTextoNumeros = '/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ0-9\s]+$/u';
        $regexDireccionBasica = '/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ0-9\s\/]+$/u';

        $calle = trim((string) $input->get('calle', $dirActual?->calle ?? ''));
        if ($calle === '' || preg_match($regexTextoNumeros, $calle) !== 1) {
            return ['error' => 'La Calle / Avenida es obligatoria y solo permite texto y números.'];
        }

        $numeroVivienda = trim((string) $input->get('numero_vivienda', $dirActual?->numero_vivienda ?? ''));
        if ($numeroVivienda === '' || preg_match($regexDireccionBasica, $numeroVivienda) !== 1) {
            return ['error' => 'El Número de vivienda es obligatorio y solo permite texto y números.'];
        }

        $esDepartamento = $vivienda === 'Departamento';
        $nombreUrb = trim((string) $input->get('nombre_urb', $esDepartamento ? ($dirActual?->nombre_urb ?? '') : ''));
        $pisoEdificio = trim((string) $input->get('piso_edificio', $esDepartamento ? ($dirActual?->piso_edificio ?? '') : ''));

        if ($esDepartamento) {
            if ($nombreUrb === '' || preg_match($regexSoloTexto, $nombreUrb) !== 1) {
                return ['error' => 'El Nombre de la urbanización es obligatorio para Departamento y solo permite texto.'];
            }
            if ($pisoEdificio === '' || preg_match($regexTextoNumeros, $pisoEdificio) !== 1) {
                return ['error' => 'El Piso / Edificio es obligatorio para Departamento y solo permite texto y números.'];
            }
        } else {
            if ($nombreUrb !== '' && strtoupper($nombreUrb) !== 'N/A' && preg_match($regexSoloTexto, $nombreUrb) !== 1) {
                return ['error' => 'La Urbanización / Sector solo permite texto.'];
            }
            $nombreUrb = $nombreUrb !== '' ? $nombreUrb : 'N/A';
            $pisoEdificio = 'N/A';
        }

        $descripcion = trim((string) $input->get('descripcion', (string) ($dirActual?->descripcion ?? '')));
        if ($descripcion !== '' && preg_match($regexTextoNumeros, $descripcion) !== 1) {
            return ['error' => 'El Punto de referencia / Descripción solo permite texto.'];
        }

        return [
            'datos' => [
                'vivienda' => substr($vivienda, 0, 30),
                'calle' => substr($calle, 0, 30),
                'nombre_urb' => substr($nombreUrb, 0, 100),
                'piso_edificio' => substr($pisoEdificio, 0, 10),
                'numero_vivienda' => substr($numeroVivienda, 0, 10),
                'descripcion' => $descripcion !== '' ? substr($descripcion, 0, 255) : null,
            ],
        ];
    }

    /**
     * Valida los datos de la card de discapacidad cuando está activa en la edición.
     * Recibe LimpiarInputs y retorna el payload validado o un error 422.
     *
     * @return array{datos?: array<string, mixed>, error?: string}
     */
    private function validarDiscapacidadEntrada(LimpiarInputs $input): array
    {
        $activa = (string) $input->get('discapacidad_activa', '0') === '1';
        if (!$activa) {
            return [];
        }

        $gradoRaw = $input->get('grado_discapacidad_id');
        $gradoId = is_numeric($gradoRaw) && (int) $gradoRaw > 0 ? (int) $gradoRaw : null;
        $tipoNombre = trim((string) $input->get('tipo_discapacidad', ''));
        $numeroCarnet = trim((string) $input->get('numero_carnet', ''));
        $entidadEmisora = trim((string) $input->get('entidad_emisora', ''));
        $descripcion = trim((string) $input->get('descripcion_discapacidad', ''));
        $fechaEmision = trim((string) $input->get('fecha_emision_carnet', ''));
        $fechaVencimiento = trim((string) $input->get('fecha_vencimiento_carnet', ''));

        if ($gradoId === null || !GradoDiscapacidadModel::query()->whereKey($gradoId)->exists()) {
            return ['error' => 'Debe seleccionar un grado de discapacidad válido.'];
        }
        if ($numeroCarnet === '') {
            return ['error' => 'El número de carnet CONAPDIS es obligatorio.'];
        }
        if ($entidadEmisora === '') {
            return ['error' => 'La entidad emisora del carnet de discapacidad es obligatoria.'];
        }
        if ($fechaEmision !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaEmision) !== 1) {
            return ['error' => 'La fecha de emisión del carnet no tiene un formato válido.'];
        }
        if ($fechaVencimiento !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaVencimiento) !== 1) {
            return ['error' => 'La fecha de vencimiento del carnet no tiene un formato válido.'];
        }
        if ($fechaEmision !== '' && $fechaVencimiento !== '' && $fechaVencimiento < $fechaEmision) {
            return ['error' => 'La fecha de vencimiento del carnet no puede ser menor que la fecha de emisión.'];
        }

        return [
            'datos' => [
                'dt_discapacidad_id' => is_numeric($input->get('dt_discapacidad_id')) ? (int) $input->get('dt_discapacidad_id') : 0,
                'grado_discapacidad_id' => $gradoId,
                'numero_carnet' => substr($numeroCarnet, 0, 30),
                'entidad_emisora' => substr($entidadEmisora, 0, 100),
                'descripcion' => $descripcion !== '' ? $descripcion : null,
                'dirigido' => $tipoNombre !== '' ? substr($tipoNombre, 0, 30) : 'Trabajador',
                'fecha_emision_carnet' => $fechaEmision !== '' ? $fechaEmision : date('Y-m-d'),
                'fecha_vencimiento_carnet' => $fechaVencimiento !== '' ? $fechaVencimiento : date('Y-m-d', strtotime('+5 years')),
            ],
        ];
    }

    /**
     * Actualiza o crea el registro de discapacidad de una persona en `detalle_discapacidad`.
     * Recibe el ID de persona y el arreglo validado de discapacidad.
     *
     * @param array<string, mixed> $datos
     */
    private function guardarDiscapacidadPersona(int $personaId, array $datos): void
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
