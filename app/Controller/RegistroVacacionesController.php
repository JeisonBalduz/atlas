<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\LimpiarInputs;
use App\Model\Vacaciones\SolicitudVacacionesModel;
use App\Services\CatalogosService;
use App\Services\VacacionesService;
use App\Utils\Config;
use flight\Engine;
use Throwable;

/**
 * Controlador de solicitudes, saldo e historial de vacaciones conectado a PostgreSQL.
 */
class RegistroVacacionesController extends BaseController
{
    private VacacionesService $vacacionesService;

    public function __construct(
        Engine $app,
        Config $config,
        private CatalogosService $catalogos,
        ?VacacionesService $vacacionesService = null,
    ) {
        parent::__construct($app, $config);
        $this->vacacionesService = $vacacionesService ?? new VacacionesService();
    }

    public function render(): void
    {
        $this->renderVista('registro_vacaciones', [
            'empleados' => [],
            'total_empleados_activos' => $this->catalogos->contarEmpleadosActivos(),
        ]);
    }

    /**
     * [GET] Lista las solicitudes reales desde `public.vw_solicitudes_vacaciones`.
     */
    public function listar(): void
    {
        try {
            $filas = $this->vacacionesService->listarSolicitudesVacaciones(['limite' => 250]);
            /** @var array<int, SolicitudVacacionesModel> $mapaMeta */
            $mapaMeta = [];
            foreach (SolicitudVacacionesModel::query()->select(['solicitud_vacaciones_id', 'estatus', 'gerencia'])->get() as $mod) {
                $mapaMeta[(int) $mod->solicitud_vacaciones_id] = $mod;
            }

            $datos = [];
            foreach ($filas as $filaObj) {
                $fila = (array) $filaObj;
                $id = (int) ($fila['solicitud_vacaciones_id'] ?? 0);
                $meta = $mapaMeta[$id] ?? null;

                $datos[] = [
                    'solicitud_vacaciones_id' => $id,
                    'empleado_id' => (int) ($fila['empleado_id'] ?? 0),
                    'cedula' => (string) ($fila['cedula'] ?? '—'),
                    'nombre_completo' => (string) ($fila['nombre_completo'] ?? '—'),
                    'dias_solicitado' => (int) ($fila['dias_solicitado'] ?? 0),
                    'dias_afectados' => (int) ($fila['dias_afectados'] ?? 0),
                    'fecha_inicio' => (string) ($fila['fecha_inicio'] ?? ''),
                    'fecha_final' => (string) ($fila['fecha_final'] ?? ''),
                    'estatus' => $meta !== null ? (int) $meta->estatus : null,
                    'requiere_gerencia' => $meta !== null ? (bool) $meta->gerencia : false,
                    'estatus_descripcion' => (string) ($fila['estatus_descripcion'] ?? 'Pendiente'),
                ];
            }

            $this->jsonOk($datos);
        } catch (Throwable) {
            $this->jsonError('No se pudieron consultar las solicitudes de vacaciones.', 500);
        }
    }

    /**
     * [GET] Consulta el saldo real de días pendientes de un trabajador (`vw_resumen_vacaciones` + `vw_balance_vacaciones`).
     */
    public function saldo(string $empleadoId): void
    {
        $id = $this->idPositivo($empleadoId, 'empleado');
        if ($id === null) {
            return;
        }

        try {
            $resumenObj = $this->vacacionesService->listarResumenVacaciones($id)[0] ?? null;
            $resumen = $resumenObj !== null ? (array) $resumenObj : [];
            $periodosAbiertos = $this->vacacionesService->listarBalanceVacaciones($id, 'Abiertas');

            $etiquetasPeriodos = [];
            foreach ($periodosAbiertos as $perObj) {
                $per = (array) $perObj;
                $etiquetasPeriodos[] = sprintf('%s (%d d)', (string) ($per['periodo'] ?? ''), (int) ($per['dias_pendientes'] ?? 0));
            }

            $diasPendientes = (int) ($resumen['total_dias_pendientes'] ?? 0);

            $this->jsonOk([
                'empleado_id' => $id,
                'dias_pendientes' => $diasPendientes,
                'periodos_pendientes' => (int) ($resumen['total_periodos_pendientes'] ?? 0),
                'resumen_periodos' => $etiquetasPeriodos !== []
                    ? 'Períodos: ' . implode(', ', $etiquetasPeriodos)
                    : 'Sin períodos vacacionales abiertos',
            ]);
        } catch (Throwable) {
            $this->jsonError('No se pudo consultar el saldo vacacional del trabajador.', 500);
        }
    }

    /**
     * [GET] Detalle de solicitud combinando `fn_obtener_detalle_vacacion`, `fn_obtener_historial_vacacion`
     * y `fn_obtener_detalle_solicitud_reposo`.
     */
    public function show(string $id): void
    {
        $solicitudId = $this->idPositivo($id, 'solicitud');
        if ($solicitudId === null) {
            return;
        }

        try {
            $detalleRaw = $this->vacacionesService->obtenerDetalleVacacion($solicitudId);
            $historialRaw = $this->vacacionesService->obtenerHistorialVacacion($solicitudId);
            $repososRaw = $this->vacacionesService->obtenerDetalleSolicitudReposo($solicitudId);

            $periodos = [];
            $listaDetalle = is_array($detalleRaw['detalle_solicitud'] ?? null) ? $detalleRaw['detalle_solicitud'] : [];
            foreach ($listaDetalle as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $periodos[] = [
                    'periodo' => $item['periodo_anio'] ?? $item['periodo'] ?? '—',
                    'dias_descontados' => (int) ($item['dias_descontados'] ?? 0),
                ];
            }

            $historial = [];
            $listaHistorial = is_array($historialRaw['historial_solicitud'] ?? null) ? $historialRaw['historial_solicitud'] : [];
            foreach ($listaHistorial as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $fechaHora = trim(((string) ($item['fecha'] ?? '')) . ' ' . ((string) ($item['hora'] ?? '')));
                $historial[] = [
                    'estatus_descripcion' => (string) ($item['estatus_texto'] ?? 'Movimiento'),
                    'fecha_registro' => $fechaHora,
                    'observacion' => (string) ($item['observacion'] ?? ''),
                ];
            }

            $reposos = [];
            $listaReposos = is_array($repososRaw['detalle_solicitud_reposo'] ?? null) ? $repososRaw['detalle_solicitud_reposo'] : [];
            foreach ($listaReposos as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $repInfo = is_array($item['reposo'] ?? null) ? $item['reposo'] : [];
                $reposos[] = [
                    'dias_afectados' => (int) ($item['dias_afectados'] ?? 0),
                    'tipo_reposo' => (string) ($repInfo['descripcion'] ?? 'Reposo médico'),
                    'fecha_inicio_reposo' => (string) ($item['fecha_registro'] ?? ''),
                    'fecha_fin_reposo' => (string) ($item['estatus_validado_texto'] ?? ''),
                ];
            }

            $this->jsonOk([
                'solicitud_vacaciones_id' => $solicitudId,
                'periodos_descontados' => $periodos,
                'historial' => $historial,
                'reposos_afectados' => $reposos,
            ]);
        } catch (Throwable) {
            $this->jsonError('No se pudo consultar el detalle de la solicitud de vacaciones.', 500);
        }
    }

    /**
     * [POST] Registra una solicitud de vacaciones usando `fn_registrar_solicitud_vacaciones`.
     */
    public function store(): void
    {
        $input = new LimpiarInputs();
        $error = $input->validar(['empleado_id', 'dias_solicitados', 'fecha_inicio', 'fecha_final']);
        if ($error !== null) {
            $this->jsonError($error, 422);
            return;
        }

        $empleadoId = (int) $input->get('empleado_id');
        $dias = (int) $input->get('dias_solicitados');
        $fechaInicio = trim((string) $input->get('fecha_inicio'));
        $fechaFinal = trim((string) $input->get('fecha_final'));
        $gerencia = filter_var($input->get('gerencia', false), FILTER_VALIDATE_BOOLEAN);

        if ($empleadoId <= 0 || $dias <= 0) {
            $this->jsonError('Empleado o cantidad de días inválida.', 422);
            return;
        }

        try {
            $resultado = $this->vacacionesService->registrarSolicitudVacaciones(
                $empleadoId,
                $dias,
                $fechaInicio,
                $fechaFinal,
                $gerencia
            );

            $desc = $resultado['descripcion'];
            if (
                str_contains(strtolower($desc), 'insuficientes')
                || str_contains(strtolower($desc), 'no posee')
                || str_contains(strtolower($desc), 'error')
                || $resultado['disponibles'] < $dias && !str_contains(strtolower($desc), 'exitos') && !str_contains(strtolower($desc), 'registrad')
            ) {
                $this->jsonError($desc !== '' ? $desc : 'El trabajador no dispone de días suficientes.', 422);
                return;
            }

            $this->jsonOk($resultado, $desc !== '' ? $desc : 'Solicitud de vacaciones registrada correctamente.', 201);
        } catch (Throwable $e) {
            $this->jsonError('No se pudo registrar la solicitud: ' . $e->getMessage(), 422);
        }
    }

    /**
     * [POST] Actualiza el estatus de la solicitud disparando `tg_procesar_descuento_vacaciones` en PostgreSQL.
     */
    public function cambiarEstatus(string $id): void
    {
        $solicitudId = $this->idPositivo($id, 'solicitud');
        if ($solicitudId === null) {
            return;
        }

        $solicitud = SolicitudVacacionesModel::query()->find($solicitudId);
        if (!$solicitud instanceof SolicitudVacacionesModel) {
            $this->jsonError('Solicitud de vacaciones no encontrada.', 404);
            return;
        }

        $input = new LimpiarInputs();
        $nuevoEstatus = (int) $input->get('estatus', $solicitud->gerencia ? 4 : 2);
        if (!in_array($nuevoEstatus, [0, 1, 2, 3, 4, 5, 6], true)) {
            $this->jsonError('Estatus de vacaciones inválido.', 422);
            return;
        }

        try {
            $solicitud->estatus = $nuevoEstatus;
            $solicitud->save();

            $this->jsonOk([
                'solicitud_vacaciones_id' => $solicitudId,
                'estatus' => $nuevoEstatus,
            ], 'Estatus de la solicitud actualizado correctamente.');
        } catch (Throwable $e) {
            $this->jsonError('No se pudo actualizar el estatus: ' . $e->getMessage(), 422);
        }
    }
}
