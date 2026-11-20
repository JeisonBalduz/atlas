<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Database\Capsule\Manager as DB;

/**
 * Servicio para consultas de vistas y funciones PL/pgSQL de Vacaciones e Interrupciones por Reposo.
 *
 * Nota: el descuento de días por aprobación lo ejecuta automáticamente el trigger
 * `public.tg_procesar_descuento_vacaciones()` en PostgreSQL al actualizar `estatus` en `solicitud_vacaciones`.
 */
final class VacacionesService
{
    /**
     * Consulta la vista de solo lectura `public.vw_balance_vacaciones`.
     *
     * @return list<object>
     */
    public function listarBalanceVacaciones(?int $empleadoId = null, ?string $estadoVacaciones = null): array
    {
        $query = DB::table('public.vw_balance_vacaciones');

        if ($empleadoId !== null) {
            $query->where('empleado_id', $empleadoId);
        }

        if ($estadoVacaciones !== null && $estadoVacaciones !== '') {
            $query->where('estado_vacaciones', '=', $estadoVacaciones);
        }

        return $query->orderBy('empleado_id', 'asc')->orderBy('periodo', 'asc')->get()->all();
    }

    /**
     * Consulta la vista de solo lectura `public.vw_resumen_vacaciones`.
     *
     * @return list<object>
     */
    public function listarResumenVacaciones(?int $empleadoId = null): array
    {
        $query = DB::table('public.vw_resumen_vacaciones');

        if ($empleadoId !== null) {
            $query->where('empleado_id', $empleadoId);
        }

        return $query->orderBy('empleado_id', 'asc')->get()->all();
    }

    /**
     * Consulta la vista de solo lectura `public.vw_solicitudes_vacaciones`.
     *
     * @param array<string, mixed> $filtros
     * @return list<object>
     */
    public function listarSolicitudesVacaciones(array $filtros = []): array
    {
        $query = DB::table('public.vw_solicitudes_vacaciones');

        if (isset($filtros['solicitud_vacaciones_id']) && $filtros['solicitud_vacaciones_id'] !== '') {
            $query->where('solicitud_vacaciones_id', (int) $filtros['solicitud_vacaciones_id']);
        }

        if (isset($filtros['empleado_id']) && $filtros['empleado_id'] !== '') {
            $query->where('empleado_id', (int) $filtros['empleado_id']);
        }

        if (!empty($filtros['cedula'])) {
            $query->where('cedula', 'ilike', '%' . (string) $filtros['cedula'] . '%');
        }

        if (!empty($filtros['nombre_completo'])) {
            $query->where('nombre_completo', 'ilike', '%' . (string) $filtros['nombre_completo'] . '%');
        }

        if (!empty($filtros['estatus_descripcion'])) {
            $query->where('estatus_descripcion', '=', (string) $filtros['estatus_descripcion']);
        }

        $ordenPor = !empty($filtros['orden_por']) ? (string) $filtros['orden_por'] : 'solicitud_vacaciones_id';
        $direccion = (!empty($filtros['direccion']) && strtolower((string) $filtros['direccion']) === 'asc') ? 'asc' : 'desc';
        $query->orderBy($ordenPor, $direccion);

        if (isset($filtros['limite']) && (int) $filtros['limite'] > 0) {
            $query->limit((int) $filtros['limite']);
        }

        if (isset($filtros['offset']) && (int) $filtros['offset'] >= 0) {
            $query->offset((int) $filtros['offset']);
        }

        return $query->get()->all();
    }

    /**
     * Ejecuta `public.fn_obtener_detalle_vacacion(p_solicitud_id)`.
     *
     * @return array<int|string, mixed>
     */
    public function obtenerDetalleVacacion(int $solicitudId): array
    {
        $row = DB::selectOne(
            'SELECT public.fn_obtener_detalle_vacacion(?) AS resultado',
            [$solicitudId]
        );

        return $this->decodificarJson($row->resultado ?? null);
    }

    /**
     * Ejecuta `public.fn_obtener_historial_vacacion(p_solicitud_id)`.
     *
     * @return array<int|string, mixed>
     */
    public function obtenerHistorialVacacion(int $solicitudId): array
    {
        $row = DB::selectOne(
            'SELECT public.fn_obtener_historial_vacacion(?) AS resultado',
            [$solicitudId]
        );

        return $this->decodificarJson($row->resultado ?? null);
    }

    /**
     * Ejecuta `public.fn_obtener_detalle_solicitud_reposo(p_solicitud_id)`.
     *
     * @return array<int|string, mixed>
     */
    public function obtenerDetalleSolicitudReposo(int $solicitudId): array
    {
        $row = DB::selectOne(
            'SELECT public.fn_obtener_detalle_solicitud_reposo(?) AS resultado',
            [$solicitudId]
        );

        return $this->decodificarJson($row->resultado ?? null);
    }

    /**
     * Ejecuta `public.fn_registrar_solicitud_vacaciones(...)` que retorna `TABLE(descripcion, disponibles, solicitados)`.
     *
     * @return array{descripcion: string, disponibles: int, solicitados: int}
     */
    public function registrarSolicitudVacaciones(
        int $empleadoId,
        int $diasSolicitados,
        string $fechaInicio,
        string $fechaFinal,
        bool $gerencia
    ): array {
        $row = DB::selectOne(
            'SELECT * FROM public.fn_registrar_solicitud_vacaciones(?, ?, ?, ?, ?)',
            [$empleadoId, $diasSolicitados, $fechaInicio, $fechaFinal, $gerencia]
        );

        return [
            'descripcion' => (string) ($row->descripcion ?? ''),
            'disponibles' => (int) ($row->disponibles ?? 0),
            'solicitados' => (int) ($row->solicitados ?? 0),
        ];
    }

    /**
     * Ejecuta `public.fn_registrar_detalle_solicitud_reposo(...)`.
     */
    public function registrarDetalleSolicitudReposo(
        int $solicitudVacacionesId,
        int $detalleReposoId,
        int $diasAfectados
    ): string {
        $row = DB::selectOne(
            'SELECT public.fn_registrar_detalle_solicitud_reposo(?, ?, ?) AS resultado',
            [$solicitudVacacionesId, $detalleReposoId, $diasAfectados]
        );

        return (string) ($row->resultado ?? '');
    }

    /**
     * Ejecuta `public.fn_reintegrar_vacaciones_por_reposo(...)`.
     */
    public function reintegrarVacacionesPorReposo(
        int $empleadoId,
        string $fechaInicioReposo,
        string $fechaFinReposo,
        int $detalleReposoId
    ): string {
        $row = DB::selectOne(
            'SELECT public.fn_reintegrar_vacaciones_por_reposo(?, ?, ?, ?) AS resultado',
            [$empleadoId, $fechaInicioReposo, $fechaFinReposo, $detalleReposoId]
        );

        return (string) ($row->resultado ?? '');
    }

    /**
     * @return array<int|string, mixed>
     */
    private function decodificarJson(mixed $json): array
    {
        $raw = is_string($json) && $json !== '' ? $json : '{}';
        $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

        return is_array($decoded) ? $decoded : [];
    }
}
