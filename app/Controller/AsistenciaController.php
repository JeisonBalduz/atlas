<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\LimpiarInputs;
use App\Services\AsistenciaService;
use App\Utils\Config;
use flight\Engine;
use Throwable;

/**
 * Controlador del módulo de Asistencia Diaria (Entrada y Salida) con bloqueo automático por reposo médico.
 */
final class AsistenciaController extends BaseController
{
    private AsistenciaService $asistenciaService;

    public function __construct(
        Engine $app,
        Config $config,
        ?AsistenciaService $asistenciaService = null,
    ) {
        parent::__construct($app, $config);
        $this->asistenciaService = $asistenciaService ?? new AsistenciaService();
    }

    public function render(): void
    {
        $hoy = date('Y-m-d');

        $this->renderVista('registro_asistencia', [
            'fecha_actual' => $hoy,
            'resumen_asistencia' => $this->asistenciaService->resumenDia($hoy),
        ]);
    }

    /**
     * [GET] Lista registros de asistencia y resumen real del día.
     */
    public function listar(): void
    {
        $query = $this->app->request()->query;
        $fecha = isset($query['fecha']) && is_string($query['fecha']) ? trim($query['fecha']) : null;

        try {
            $datos = $this->asistenciaService->listarRegistrosDetallados($fecha !== '' ? $fecha : null);
            $resumen = $this->asistenciaService->resumenDia($fecha !== null && $fecha !== '' ? $fecha : date('Y-m-d'));

            $this->app->json([
                'ok' => true,
                'data' => $datos,
                'resumen' => $resumen,
            ], 200);
        } catch (Throwable) {
            $this->jsonError('No se pudieron consultar los registros de asistencia.', 500);
        }
    }

    /**
     * [GET] Verifica en tiempo real la ficha del empleado, su asistencia del día y si tiene reposo médico activo.
     */
    public function estadoEmpleado(string $empleadoId): void
    {
        $id = $this->idPositivo($empleadoId, 'empleado');
        if ($id === null) {
            return;
        }

        $query = $this->app->request()->query;
        $fecha = isset($query['fecha']) && is_string($query['fecha']) && trim($query['fecha']) !== ''
            ? trim($query['fecha'])
            : date('Y-m-d');

        try {
            $this->jsonOk($this->asistenciaService->consultarEstadoEmpleado($id, $fecha));
        } catch (Throwable $e) {
            $codigo = (int) $e->getCode();
            $this->jsonError($e->getMessage(), $codigo >= 400 && $codigo < 600 ? $codigo : 500);
        }
    }

    /**
     * [POST] Registra la Entrada de un trabajador validando que NO se encuentre de reposo médico.
     */
    public function entrada(): void
    {
        $input = new LimpiarInputs();
        $error = $input->validar(['empleado_id']);
        if ($error !== null) {
            $this->jsonError($error, 422);
            return;
        }

        $empleadoId = (int) $input->get('empleado_id');
        $turno = trim((string) $input->get('turno', 'Diurno'));
        $fecha = trim((string) $input->get('fecha', date('Y-m-d')));
        $horaRaw = trim((string) $input->get('hora', ''));
        $hora = $horaRaw !== '' ? $horaRaw : date('H:i:s');

        try {
            $registro = $this->asistenciaService->registrarEntrada($empleadoId, $turno, $fecha, $hora);
            $this->jsonOk([
                'dtasistencia_id' => (int) $registro->dtasistencia_id,
                'hora_entrada' => substr((string) $registro->hora_entrada, 0, 5),
            ], 'Entrada de asistencia registrada correctamente.', 201);
        } catch (Throwable $e) {
            $codigo = (int) $e->getCode();
            $this->jsonError(
                $codigo >= 400 && $codigo < 500 ? $e->getMessage() : 'No se pudo registrar la entrada de asistencia.',
                $codigo >= 400 && $codigo < 600 ? $codigo : 500
            );
        }
    }

    /**
     * [POST] Registra la Salida de la jornada abierta del trabajador.
     */
    public function salida(): void
    {
        $input = new LimpiarInputs();
        $error = $input->validar(['empleado_id']);
        if ($error !== null) {
            $this->jsonError($error, 422);
            return;
        }

        $empleadoId = (int) $input->get('empleado_id');
        $fecha = trim((string) $input->get('fecha', date('Y-m-d')));
        $horaRaw = trim((string) $input->get('hora', ''));
        $hora = $horaRaw !== '' ? $horaRaw : date('H:i:s');

        try {
            $registro = $this->asistenciaService->registrarSalida($empleadoId, $fecha, $hora);
            $this->jsonOk([
                'dtasistencia_id' => (int) $registro->dtasistencia_id,
                'hora_salida' => substr((string) $registro->hora_salida, 0, 5),
            ], 'Salida de asistencia registrada correctamente.');
        } catch (Throwable $e) {
            $codigo = (int) $e->getCode();
            $this->jsonError(
                $codigo >= 400 && $codigo < 500 ? $e->getMessage() : 'No se pudo registrar la salida de asistencia.',
                $codigo >= 400 && $codigo < 600 ? $codigo : 500
            );
        }
    }
}
