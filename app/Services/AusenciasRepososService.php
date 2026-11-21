<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Database\Capsule\Manager as DB;

/**
 * Servicio para consultas de vistas y funciones PL/pgSQL de Ausencias, Permisos y Reposos Médicos.
 */
final class AusenciasRepososService extends BaseVistaService
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
        $this->aplicarFiltros(
            $query,
            $filtros,
            camposTexto: ['cedula', 'nombre_completo'],
            camposExactos: ['tipo_ausencia_descripcion'],
            camposEnteros: ['ausencia_id', 'empleado_id'],
        );
        $this->aplicarOrdenYLimites($query, $filtros, 'ausencia_id', 'desc');

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
        $this->aplicarFiltros(
            $query,
            $filtros,
            camposTexto: ['cedula', 'permiso'],
            camposExactos: ['remunerable_descripcion'],
            camposEnteros: ['dtpermiso_id', 'ausencia_id', 'empleado_id'],
        );
        $this->aplicarOrdenYLimites($query, $filtros, 'dtpermiso_id', 'desc');

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
        $this->aplicarFiltros(
            $query,
            $filtros,
            camposTexto: ['cedula', 'tipo_reposo', 'institucion_emisora'],
            camposEnteros: ['dtreposo_id', 'ausencia_id', 'empleado_id'],
        );
        $this->aplicarOrdenYLimites($query, $filtros, 'dtreposo_id', 'desc');

        return $query->get()->all();
    }

    /**
     * Ejecuta `public.fn_obtener_reposo_validado(p_dtreposo_id)`.
     *
     * @return array<string, mixed>
     */
    public function obtenerReposoValidado(int $detalleReposoId): array
    {
        /** @var array<string, mixed> */
        return $this->ejecutarFuncionJson('public.fn_obtener_reposo_validado', $detalleReposoId);
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
