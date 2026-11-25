<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\LimpiarInputs;
use App\Model\Vacaciones\SolicitudVacacionesModel;
use App\Services\CatalogosService;
use App\Services\ContextoSesion;
use App\Services\VacacionesService;
use App\Utils\Config;
use flight\Engine;
use Throwable;

/**
 * Controlador de solicitudes, saldo, edición, suspensión y aprobaciones de vacaciones conectado a PostgreSQL.
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
        $rolActual = strtolower(trim((string) (ContextoSesion::role() ?? '')));
        $esAdmin = $rolActual === 'administrador';

        $this->renderVista('registro_vacaciones', [
            'empleados' => [],
            'total_empleados_activos' => $this->catalogos->contarEmpleadosActivos(),
            'resumen_vacaciones' => $this->vacacionesService->resumenConteos(),
            'es_administrador' => $esAdmin,
        ]);
    }

    /**
     * [GET] Lista las solicitudes reales desde `public.vw_solicitudes_vacaciones` junto con métricas reales.
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

            $this->app->json([
                'ok' => true,
                'data' => $datos,
                'resumen' => $this->vacacionesService->resumenConteos(),
            ], 200);
        } catch (Throwable) {
            $this->jsonError('No se pudieron consultar las solicitudes de vacaciones.', 500);
        }
    }

    /**
     * [GET] Lista los días feriados aplicables (nacionales, contractuales INCES y móviles) desde PostgreSQL.
     */
    public function feriados(): void
    {
        $query = $this->app->request()->query;
        $anioActual = (int) date('Y');
        $anioInicioRaw = $query['anio_inicio'] ?? $anioActual;
        $anioFinRaw = $query['anio_fin'] ?? ($anioActual + 1);

        $anioInicio = is_numeric($anioInicioRaw) ? (int) $anioInicioRaw : $anioActual;
        $anioFin = is_numeric($anioFinRaw) ? (int) $anioFinRaw : ($anioActual + 1);

        try {
            $catalogo = $this->vacacionesService->obtenerCatalogoFeriados($anioInicio, $anioFin);
            $this->jsonOk($catalogo);
        } catch (Throwable) {
            $this->jsonError('No se pudieron consultar los días feriados.', 500);
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
     * [POST] Registra una solicitud de vacaciones validando rango, días hábiles INCES y saldo acumulado.
     */
    public function store(): void
    {
        $input = new LimpiarInputs();
        $error = $input->validar(['empleado_id', 'fecha_inicio', 'fecha_final']);
        if ($error !== null) {
            $this->jsonError($error, 422);
            return;
        }

        $empleadoId = (int) $input->get('empleado_id');
        $fechaInicio = trim((string) $input->get('fecha_inicio'));
        $fechaFinal = trim((string) $input->get('fecha_final'));
        $gerencia = filter_var($input->get('gerencia', false), FILTER_VALIDATE_BOOLEAN);

        if ($fechaFinal < $fechaInicio) {
            $this->jsonError('La fecha final no puede ser menor que la fecha de inicio.', 422);
            return;
        }

        $diasHabiles = $this->vacacionesService->calcularDiasHabilesInces($fechaInicio, $fechaFinal);
        $dias = $diasHabiles > 0 ? $diasHabiles : (int) $input->get('dias_solicitados', 0);

        if ($empleadoId <= 0 || $dias <= 0) {
            $this->jsonError('El rango seleccionado no contiene días hábiles laborables según la normativa INCES.', 422);
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
                || str_contains(strtolower($desc), 'no tiene suficientes')
                || str_contains(strtolower($desc), 'error')
                || ($resultado['disponibles'] < $dias && !str_contains(strtolower($desc), 'éxito') && !str_contains(strtolower($desc), 'registrad'))
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
     * [PUT/POST] Edita fechas y días hábiles de una solicitud de vacaciones.
     */
    public function update(string $id): void
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
        $error = $input->validar(['fecha_inicio', 'fecha_final']);
        if ($error !== null) {
            $this->jsonError($error, 422);
            return;
        }

        $fechaInicio = trim((string) $input->get('fecha_inicio'));
        $fechaFinal = trim((string) $input->get('fecha_final'));
        $gerencia = filter_var($input->get('gerencia', $solicitud->gerencia), FILTER_VALIDATE_BOOLEAN);

        if ($fechaFinal < $fechaInicio) {
            $this->jsonError('La fecha final no puede ser menor que la fecha de inicio.', 422);
            return;
        }

        $dias = $this->vacacionesService->calcularDiasHabilesInces($fechaInicio, $fechaFinal);
        if ($dias <= 0) {
            $this->jsonError('El nuevo rango de fechas debe contener al menos un (1) día hábil laborable.', 422);
            return;
        }

        try {
            $actualizada = $this->vacacionesService->actualizarSolicitud(
                $solicitud,
                $fechaInicio,
                $fechaFinal,
                $dias,
                $gerencia
            );

            $this->jsonOk([
                'solicitud_vacaciones_id' => (int) $actualizada->solicitud_vacaciones_id,
                'dias_solicitado' => (int) $actualizada->dias_solicitado,
            ], 'Solicitud de vacaciones actualizada correctamente.');
        } catch (Throwable $e) {
            $codigo = (int) $e->getCode();
            $this->jsonError($e->getMessage(), $codigo >= 400 && $codigo < 600 ? $codigo : 422);
        }
    }

    /**
     * [POST] Actualiza el estatus de la solicitud (RRHH, Gerencia, Suspensión con devolución de días).
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

        $diasDisfrutados = max(0, (int) $input->get('dias_disfrutados', 0));
        $observacion = trim((string) $input->get('observacion', ''));

        try {
            $this->vacacionesService->procesarCambioEstatus($solicitud, $nuevoEstatus, $diasDisfrutados, $observacion);

            $mensaje = match ($nuevoEstatus) {
                2 => 'Solicitud aprobada por Recursos Humanos correctamente.',
                4 => 'Solicitud aprobada por Gerencia correctamente.',
                6 => 'Vacación suspendida y días pendientes reintegrados al saldo del trabajador.',
                default => 'Estatus de la solicitud actualizado correctamente.',
            };

            $this->jsonOk([
                'solicitud_vacaciones_id' => $solicitudId,
                'estatus' => $nuevoEstatus,
            ], $mensaje);
        } catch (Throwable $e) {
            $codigo = (int) $e->getCode();
            $this->jsonError($e->getMessage(), $codigo >= 400 && $codigo < 600 ? $codigo : 422);
        }
    }
}
