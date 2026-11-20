<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Database\Capsule\Manager as DB;

/**
 * Servicio para consultas de vistas y funciones PL/pgSQL de Ausencias, Permisos y Reposos Médicos.
 */
final class AusenciasRepososService
{
    /**
     * Consulta la vista de solo lectura `public.vw_ausencias_justificadas`.
     *
     * @param array<string, mixed> $filtros
     * @return list<object>
     */
    public function listarAusenciasJustificadas(array $filtros = []): array
    {
        $query = DB::table('public.vw_ausencias_justificadas');

        if (isset($filtros['ausencia_id']) && $filtros['ausencia_id'] !== '') {
            $query->where('ausencia_id', (int) $filtros['ausencia_id']);
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

        if (!empty($filtros['tipo_ausencia_descripcion'])) {
            $query->where('tipo_ausencia_descripcion', '=', (string) $filtros['tipo_ausencia_descripcion']);
        }

        $ordenPor = !empty($filtros['orden_por']) ? (string) $filtros['orden_por'] : 'ausencia_id';
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
     * Consulta la vista de solo lectura `public.vw_detalle_permisos`.
     *
     * @param array<string, mixed> $filtros
     * @return list<object>
     */
    public function listarDetallePermisos(array $filtros = []): array
    {
        $query = DB::table('public.vw_detalle_permisos');

        if (isset($filtros['dtpermiso_id']) && $filtros['dtpermiso_id'] !== '') {
            $query->where('dtpermiso_id', (int) $filtros['dtpermiso_id']);
        }

        if (isset($filtros['ausencia_id']) && $filtros['ausencia_id'] !== '') {
            $query->where('ausencia_id', (int) $filtros['ausencia_id']);
        }

        if (isset($filtros['empleado_id']) && $filtros['empleado_id'] !== '') {
            $query->where('empleado_id', (int) $filtros['empleado_id']);
        }

        if (!empty($filtros['cedula'])) {
            $query->where('cedula', 'ilike', '%' . (string) $filtros['cedula'] . '%');
        }

        if (!empty($filtros['permiso'])) {
            $query->where('permiso', 'ilike', '%' . (string) $filtros['permiso'] . '%');
        }

        if (!empty($filtros['remunerable_descripcion'])) {
            $query->where('remunerable_descripcion', '=', (string) $filtros['remunerable_descripcion']);
        }

        $ordenPor = !empty($filtros['orden_por']) ? (string) $filtros['orden_por'] : 'dtpermiso_id';
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
     * Consulta la vista de solo lectura `public.vw_detalle_reposos`.
     *
     * @param array<string, mixed> $filtros
     * @return list<object>
     */
    public function listarDetalleReposos(array $filtros = []): array
    {
        $query = DB::table('public.vw_detalle_reposos');

        if (isset($filtros['dtreposo_id']) && $filtros['dtreposo_id'] !== '') {
            $query->where('dtreposo_id', (int) $filtros['dtreposo_id']);
        }

        if (isset($filtros['ausencia_id']) && $filtros['ausencia_id'] !== '') {
            $query->where('ausencia_id', (int) $filtros['ausencia_id']);
        }

        if (isset($filtros['empleado_id']) && $filtros['empleado_id'] !== '') {
            $query->where('empleado_id', (int) $filtros['empleado_id']);
        }

        if (!empty($filtros['cedula'])) {
            $query->where('cedula', 'ilike', '%' . (string) $filtros['cedula'] . '%');
        }

        if (!empty($filtros['tipo_reposo'])) {
            $query->where('tipo_reposo', 'ilike', '%' . (string) $filtros['tipo_reposo'] . '%');
        }

        if (!empty($filtros['institucion_emisora'])) {
            $query->where('institucion_emisora', 'ilike', '%' . (string) $filtros['institucion_emisora'] . '%');
        }

        $ordenPor = !empty($filtros['orden_por']) ? (string) $filtros['orden_por'] : 'dtreposo_id';
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
     * Ejecuta `public.fn_obtener_reposo_validado(p_dtreposo_id)`.
     *
     * @return array<string, mixed>
     */
    public function obtenerReposoValidado(int $detalleReposoId): array
    {
        $row = DB::selectOne(
            'SELECT public.fn_obtener_reposo_validado(?) AS resultado',
            [$detalleReposoId]
        );

        $raw = isset($row->resultado) && is_string($row->resultado) && $row->resultado !== ''
            ? $row->resultado
            : '{}';
        $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Ejecuta `public.fn_registrar_ausencia_justificada(...)` y retorna el `ausencia_id` generado.
     */
    public function registrarAusenciaJustificada(
        int $empleadoId,
        string $fechaInicio,
        string $fechaFin,
        int $diasAusencia,
        int $tipoAusencia
    ): int {
        $row = DB::selectOne(
            'SELECT public.fn_registrar_ausencia_justificada(?, ?, ?, ?, ?) AS resultado',
            [$empleadoId, $fechaInicio, $fechaFin, $diasAusencia, $tipoAusencia]
        );

        return (int) ($row->resultado ?? 0);
    }

    /**
     * Ejecuta `public.fn_registrar_detalle_permiso(...)`.
     */
    public function registrarDetallePermiso(
        int $ausenciaId,
        int $tipoPermisoId,
        string $motivo,
        bool $remunerable,
        string $fechaSolicitud
    ): string {
        $row = DB::selectOne(
            'SELECT public.fn_registrar_detalle_permiso(?, ?, ?, ?, ?) AS resultado',
            [$ausenciaId, $tipoPermisoId, $motivo, $remunerable, $fechaSolicitud]
        );

        return (string) ($row->resultado ?? '');
    }

    /**
     * Ejecuta `public.fn_registrar_detalle_reposo(...)` y retorna el `dtreposo_id` generado.
     */
    public function registrarDetalleReposo(
        int $ausenciaId,
        int $tipoReposoId,
        string $descripcion,
        ?int $tipoInstitucionEmisoraId,
        string $fechaSolicitud,
        ?string $doctor
    ): int {
        $row = DB::selectOne(
            'SELECT public.fn_registrar_detalle_reposo(?, ?, ?, ?, ?, ?) AS resultado',
            [$ausenciaId, $tipoReposoId, $descripcion, $tipoInstitucionEmisoraId, $fechaSolicitud, $doctor]
        );

        return (int) ($row->resultado ?? 0);
    }

    /**
     * Ejecuta `public.fn_registrar_detalle_reposo_valido(...)` y retorna el `detalle_reposo_validado_id` generado.
     */
    public function registrarDetalleReposoValido(
        int $detalleReposoId,
        int $tipoInstitucionEmisoraId,
        string $codigo,
        int $numAseguradora,
        string $fechaExpide,
        string $medico,
        int $clave
    ): int {
        $row = DB::selectOne(
            'SELECT public.fn_registrar_detalle_reposo_valido(?, ?, ?, ?, ?, ?, ?) AS resultado',
            [$detalleReposoId, $tipoInstitucionEmisoraId, $codigo, $numAseguradora, $fechaExpide, $medico, $clave]
        );

        return (int) ($row->resultado ?? 0);
    }
}
