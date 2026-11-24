<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\LimpiarInputs;
use App\Services\AusenciasRepososService;
use App\Services\CatalogosService;
use App\Services\VacacionesService;
use App\Utils\Config;
use flight\Engine;
use Illuminate\Database\Capsule\Manager as DB;
use Throwable;

/**
 * Controlador de ausencias justificadas (permisos y reposos médicos) conectado a PostgreSQL.
 */
class RegistroAusenciaController extends BaseController
{
    private AusenciasRepososService $ausenciasService;
    private VacacionesService $vacacionesService;

    public function __construct(
        Engine $app,
        Config $config,
        private CatalogosService $catalogos,
        ?AusenciasRepososService $ausenciasService = null,
        ?VacacionesService $vacacionesService = null,
    ) {
        parent::__construct($app, $config);
        $this->ausenciasService = $ausenciasService ?? new AusenciasRepososService();
        $this->vacacionesService = $vacacionesService ?? new VacacionesService();
    }

    public function render(): void
    {
        $this->renderVista('registro_ausencia', [
            'empleados' => [],
            'tipos_permiso' => $this->catalogos->tiposPermiso(),
            'tipos_reposo' => $this->catalogos->tiposReposo(),
            'tipos_institucion' => $this->catalogos->tiposInstitucionEmisora(),
            'tipos_documentos' => $this->catalogos->tiposDocumentos(),
            'resumen_ausencias' => $this->ausenciasService->resumenConteos(),
        ]);
    }

    /**
     * [GET] Lista ausencias justificadas reales cruzando `vw_ausencias_justificadas`, `vw_detalle_permisos` y `vw_detalle_reposos`.
     */
    public function listar(): void
    {
        try {
            $ausencias = $this->ausenciasService->listarAusenciasJustificadas(['limite' => 250]);

            $permisosPorAusencia = [];
            foreach ($this->ausenciasService->listarDetallePermisos(['limite' => 250]) as $permObj) {
                $perm = (array) $permObj;
                $permisosPorAusencia[(int) ($perm['ausencia_id'] ?? 0)] = $perm;
            }

            $repososPorAusencia = [];
            foreach ($this->ausenciasService->listarDetalleReposos(['limite' => 250]) as $repObj) {
                $rep = (array) $repObj;
                $repososPorAusencia[(int) ($rep['ausencia_id'] ?? 0)] = $rep;
            }

            $datos = [];
            foreach ($ausencias as $filaObj) {
                $fila = (array) $filaObj;
                $id = (int) ($fila['ausencia_id'] ?? 0);
                $tipoTexto = (string) ($fila['tipo_ausencia_descripcion'] ?? 'Permiso');
                $detalle = '—';

                if ($tipoTexto === 'Permiso' && isset($permisosPorAusencia[$id])) {
                    $p = $permisosPorAusencia[$id];
                    $detalle = implode(' · ', array_filter([(string) ($p['permiso'] ?? ''), (string) ($p['motivo'] ?? '')]));
                } elseif (isset($repososPorAusencia[$id])) {
                    $r = $repososPorAusencia[$id];
                    $detalle = implode(' · ', array_filter([(string) ($r['tipo_reposo'] ?? ''), (string) ($r['motivo_reposo'] ?? '')]));
                }

                $datos[] = [
                    'ausencia_id' => $id,
                    'empleado_id' => (int) ($fila['empleado_id'] ?? 0),
                    'cedula' => (string) ($fila['cedula'] ?? '—'),
                    'nombre_completo' => (string) ($fila['nombre_completo'] ?? '—'),
                    'tipo_texto' => $tipoTexto,
                    'detalle' => $detalle !== '' ? $detalle : $tipoTexto,
                    'fecha_inicio' => (string) ($fila['fecha_inicio'] ?? ''),
                    'fecha_fin' => (string) ($fila['fecha_fin'] ?? ''),
                    'dias_ausencia' => (int) ($fila['dias_ausencia'] ?? 0),
                ];
            }

            $this->app->json([
                'ok' => true,
                'data' => $datos,
                'resumen' => $this->ausenciasService->resumenConteos(),
            ], 200);
        } catch (Throwable) {
            $this->jsonError('No se pudieron consultar las ausencias justificadas.', 500);
        }
    }

    /**
     * [GET] Detalle de una ausencia justificada incluyendo convalidación IVSS (`fn_obtener_reposo_validado`).
     */
    public function show(string $id): void
    {
        $ausenciaId = $this->idPositivo($id, 'ausencia');
        if ($ausenciaId === null) {
            return;
        }

        try {
            $lista = $this->ausenciasService->listarAusenciasJustificadas(['ausencia_id' => $ausenciaId, 'limite' => 1]);
            $baseObj = $lista[0] ?? null;
            if ($baseObj === null) {
                $this->jsonError('Ausencia justificada no encontrada.', 404);
                return;
            }
            $base = (array) $baseObj;
            $empId = (int) ($base['empleado_id'] ?? 0);

            $departamento = (string) (DB::table('empleados')
                ->leftJoin('departamentos', 'departamentos.departamento_id', '=', 'empleados.departamento_id')
                ->where('empleados.empleado_id', $empId)
                ->value('departamentos.departamento') ?? '');

            $tipoTexto = (string) ($base['tipo_ausencia_descripcion'] ?? 'Permiso');
            $respuesta = [
                'ausencia_id' => $ausenciaId,
                'empleado_id' => $empId,
                'cedula' => (string) ($base['cedula'] ?? ''),
                'nombre_completo' => (string) ($base['nombre_completo'] ?? ''),
                'departamento' => $departamento,
                'tipo_texto' => $tipoTexto,
                'fecha_inicio' => (string) ($base['fecha_inicio'] ?? ''),
                'fecha_fin' => (string) ($base['fecha_fin'] ?? ''),
                'dias_ausencia' => (int) ($base['dias_ausencia'] ?? 0),
            ];

            if ($tipoTexto === 'Permiso') {
                $permObj = $this->ausenciasService->listarDetallePermisos(['ausencia_id' => $ausenciaId, 'limite' => 1])[0] ?? null;
                $perm = $permObj !== null ? (array) $permObj : [];
                $tpPermisoRow = DB::table('detalle_permiso')->where('ausencia_id', $ausenciaId)->first(['tppermiso_id']);
                $respuesta['tppermiso_id'] = $tpPermisoRow !== null ? (int) $tpPermisoRow->tppermiso_id : null;
                $respuesta['tipo_catalogo'] = (string) ($perm['permiso'] ?? 'Permiso');
                $respuesta['motivo'] = (string) ($perm['motivo'] ?? '');
                $respuesta['remunerable'] = ((string) ($perm['remunerable_descripcion'] ?? 'No')) === 'Sí';
                $respuesta['fecha_solicitud'] = (string) ($perm['fecha_solicitud'] ?? '');
            } else {
                $repObj = $this->ausenciasService->listarDetalleReposos(['ausencia_id' => $ausenciaId, 'limite' => 1])[0] ?? null;
                $rep = $repObj !== null ? (array) $repObj : [];
                $dtReposoId = isset($rep['dtreposo_id']) ? (int) $rep['dtreposo_id'] : 0;
                $tpReposoRow = DB::table('detalle_reposo')->where('ausencia_id', $ausenciaId)->first(['tpreposo_id']);
                $respuesta['dtreposo_id'] = $dtReposoId > 0 ? $dtReposoId : null;
                $respuesta['tpreposo_id'] = $tpReposoRow !== null ? (int) $tpReposoRow->tpreposo_id : null;
                $respuesta['tipo_catalogo'] = (string) ($rep['tipo_reposo'] ?? 'Reposo médico');
                $respuesta['institucion'] = (string) ($rep['institucion_emisora'] ?? 'No especificada');
                $respuesta['doctor'] = (string) ($rep['doctor'] ?? 'No especificado');
                $respuesta['descripcion'] = (string) ($rep['motivo_reposo'] ?? '');
                $respuesta['fecha_solicitud'] = (string) ($rep['fecha_solicitud'] ?? '');
                $respuesta['reposo_validado'] = null;

                if ($dtReposoId > 0) {
                    $val = $this->ausenciasService->obtenerReposoValidado($dtReposoId);
                    if (!empty($val['detalle_reposo_validado_id'])) {
                        $respuesta['reposo_validado'] = [
                            'detalle_reposo_validado_id' => (int) $val['detalle_reposo_validado_id'],
                            'institucion_emisora' => (string) ($val['institucion_validadora'] ?? ''),
                            'codigo' => (string) ($val['codigo'] ?? ''),
                            'num_aseguradora' => (int) ($val['num_aseguradora'] ?? 0),
                            'fecha_expide' => (string) ($val['fecha_expide'] ?? ''),
                            'medico' => (string) ($val['medico'] ?? ''),
                            'clave' => (int) ($val['clave'] ?? 0),
                        ];
                    }
                }
            }

            $this->jsonOk($respuesta);
        } catch (Throwable) {
            $this->jsonError('Error al consultar el detalle de la ausencia.', 500);
        }
    }

    /**
     * [POST] Registra permiso o reposo médico usando las funciones PL/pgSQL y reintegra vacaciones si hay solapamiento.
     */
    public function store(): void
    {
        $input = new LimpiarInputs();
        $error = $input->validar(['empleado_id', 'fecha_inicio', 'fecha_fin', 'dias_ausencia', 'tipo_ausencia']);
        if ($error !== null) {
            $this->jsonError($error, 422);
            return;
        }

        $empleadoId = (int) $input->get('empleado_id');
        $fechaInicio = trim((string) $input->get('fecha_inicio'));
        $fechaFin = trim((string) $input->get('fecha_fin'));
        $dias = (int) $input->get('dias_ausencia');
        $tipo = (int) $input->get('tipo_ausencia');

        if ($fechaFin < $fechaInicio) {
            $this->jsonError('La fecha fin no puede ser menor que la fecha de inicio.', 422);
            return;
        }

        if ($empleadoId <= 0 || $dias <= 0 || !in_array($tipo, [1, 2], true)) {
            $this->jsonError('Datos de ausencia inválidos.', 422);
            return;
        }

        $ahora = date('Y-m-d H:i:s');

        try {
            $resultado = DB::connection()->transaction(function () use (
                $empleadoId,
                $fechaInicio,
                $fechaFin,
                $dias,
                $tipo,
                $ahora,
                $input
            ): array {
                $ausenciaId = $this->ausenciasService->registrarAusenciaJustificada(
                    $empleadoId,
                    $fechaInicio,
                    $fechaFin,
                    $dias,
                    $tipo
                );

                if ($tipo === 1) {
                    $tpPermisoId = (int) $input->get('tppermiso_id');
                    $motivo = trim((string) $input->get('motivo', ''));
                    $remunerable = filter_var($input->get('remunerable', true), FILTER_VALIDATE_BOOLEAN);

                    if ($tpPermisoId <= 0 || $motivo === '') {
                        throw new \InvalidArgumentException('Debe indicar el tipo de permiso y el motivo.', 422);
                    }

                    $this->ausenciasService->registrarDetallePermiso(
                        $ausenciaId,
                        $tpPermisoId,
                        substr($motivo, 0, 255),
                        $remunerable,
                        $ahora
                    );

                    return ['ausencia_id' => $ausenciaId, 'mensaje' => 'Permiso registrado correctamente.'];
                }

                $tpReposoId = (int) $input->get('tpreposo_id');
                $descripcion = trim((string) $input->get('descripcion', ''));
                $instRaw = $input->get('tipo_institucion_emisora_id');
                $instId = is_numeric($instRaw) && (int) $instRaw > 0 ? (int) $instRaw : null;
                $doctorRaw = trim((string) $input->get('doctor', ''));
                $doctor = $doctorRaw !== '' ? substr($doctorRaw, 0, 100) : null;

                if ($tpReposoId <= 0 || $descripcion === '') {
                    throw new \InvalidArgumentException('Debe indicar el tipo de reposo y la descripción médica.', 422);
                }

                $dtReposoId = $this->ausenciasService->registrarDetalleReposo(
                    $ausenciaId,
                    $tpReposoId,
                    substr($descripcion, 0, 255),
                    $instId,
                    $ahora,
                    $doctor
                );

                $reintegroMsg = '';
                if ($dtReposoId > 0) {
                    $reintegroMsg = $this->vacacionesService->reintegrarVacacionesPorReposo(
                        $empleadoId,
                        $fechaInicio,
                        $fechaFin,
                        $dtReposoId
                    );
                }

                $mensaje = 'Reposo médico registrado correctamente.';
                if ($reintegroMsg !== '' && !str_contains(strtolower($reintegroMsg), 'no se encontr')) {
                    $mensaje .= ' ' . $reintegroMsg;
                }

                return [
                    'ausencia_id' => $ausenciaId,
                    'dtreposo_id' => $dtReposoId,
                    'mensaje' => $mensaje,
                ];
            });

            $this->jsonOk($resultado, $resultado['mensaje'], 201);
        } catch (Throwable $e) {
            $codigo = (int) $e->getCode();
            $this->jsonError(
                $codigo >= 400 && $codigo < 500 ? $e->getMessage() : 'No se pudo registrar la ausencia justificada.',
                $codigo >= 400 && $codigo < 600 ? $codigo : 500
            );
        }
    }

    /**
     * [PUT/POST] Edita fechas, días y motivo/descripción de una ausencia justificada.
     */
    public function update(string $id): void
    {
        $ausenciaId = $this->idPositivo($id, 'ausencia');
        if ($ausenciaId === null) {
            return;
        }

        $input = new LimpiarInputs();
        $error = $input->validar(['fecha_inicio', 'fecha_fin', 'dias_ausencia']);
        if ($error !== null) {
            $this->jsonError($error, 422);
            return;
        }

        $fechaInicio = trim((string) $input->get('fecha_inicio'));
        $fechaFin = trim((string) $input->get('fecha_fin'));
        $dias = (int) $input->get('dias_ausencia');

        if ($fechaFin < $fechaInicio || $dias <= 0) {
            $this->jsonError('La fecha fin debe ser mayor o igual a la fecha de inicio.', 422);
            return;
        }

        try {
            $detalle = [
                'tppermiso_id' => $input->get('tppermiso_id'),
                'motivo' => $input->get('motivo'),
                'remunerable' => filter_var($input->get('remunerable', true), FILTER_VALIDATE_BOOLEAN),
                'tpreposo_id' => $input->get('tpreposo_id'),
                'descripcion' => $input->get('descripcion'),
                'doctor' => $input->get('doctor'),
            ];

            $actualizada = $this->ausenciasService->actualizarAusencia(
                $ausenciaId,
                $fechaInicio,
                $fechaFin,
                $dias,
                $detalle,
                $this->vacacionesService
            );

            $this->jsonOk(['ausencia_id' => (int) $actualizada->ausencia_id], 'Ausencia justificada actualizada correctamente.');
        } catch (Throwable $e) {
            $codigo = (int) $e->getCode();
            $this->jsonError($e->getMessage(), $codigo >= 400 && $codigo < 600 ? $codigo : 422);
        }
    }

    /**
     * [POST] Reanuda anticipadamente una ausencia/vacación cortada devolviendo los días no disfrutados.
     */
    public function reanudar(string $id): void
    {
        $ausenciaId = $this->idPositivo($id, 'ausencia');
        if ($ausenciaId === null) {
            return;
        }

        $input = new LimpiarInputs();
        $error = $input->validar(['fecha_reincorporacion']);
        if ($error !== null) {
            $this->jsonError($error, 422);
            return;
        }

        try {
            $resultado = $this->ausenciasService->reanudarAusencia(
                $ausenciaId,
                trim((string) $input->get('fecha_reincorporacion')),
                $this->vacacionesService
            );

            $this->jsonOk($resultado, $resultado['mensaje']);
        } catch (Throwable $e) {
            $codigo = (int) $e->getCode();
            $this->jsonError($e->getMessage(), $codigo >= 400 && $codigo < 600 ? $codigo : 422);
        }
    }

    /**
     * [POST] Convalida un reposo médico mediante `fn_registrar_detalle_reposo_valido`.
     */
    public function validarReposo(string $id): void
    {
        $dtReposoId = $this->idPositivo($id, 'reposo');
        if ($dtReposoId === null) {
            return;
        }

        $input = new LimpiarInputs();
        $error = $input->validar(['tpinstitucion_emisora_id', 'codigo', 'num_aseguradora', 'fecha_expide', 'medico', 'clave']);
        if ($error !== null) {
            $this->jsonError($error, 422);
            return;
        }

        try {
            $validadoId = $this->ausenciasService->registrarDetalleReposoValido(
                $dtReposoId,
                (int) $input->get('tpinstitucion_emisora_id'),
                substr(trim((string) $input->get('codigo')), 0, 50),
                (int) $input->get('num_aseguradora'),
                trim((string) $input->get('fecha_expide')),
                substr(trim((string) $input->get('medico')), 0, 100),
                (int) $input->get('clave')
            );

            $this->jsonOk(['detalle_reposo_validado_id' => $validadoId], 'Reposo médico convalidado correctamente.', 201);
        } catch (Throwable $e) {
            $this->jsonError('No se pudo convalidar el reposo: ' . $e->getMessage(), 422);
        }
    }
}
