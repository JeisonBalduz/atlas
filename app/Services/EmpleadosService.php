<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Database\Capsule\Manager as DB;

/**
 * Servicio para consultas de vistas y funciones PL/pgSQL del módulo de Empleados.
 */
final class EmpleadosService extends BaseVistaService
{
    /**
     * Consulta la vista de solo lectura `public.vw_empleados`.
     *
     * @param array<string, mixed> $filtros
     * @return list<object>
     */
    public function listarEmpleados(array $filtros = []): array
    {
        $query = DB::table('public.vw_empleados');
        $this->aplicarFiltros(
            $query,
            $filtros,
            camposTexto: ['cedula', 'nombre_completo', 'departamento', 'dependencia', 'cargo'],
            camposExactos: ['estatus'],
            camposEnteros: ['empleado_id', 'persona_id'],
        );
        $this->aplicarOrdenYLimites($query, $filtros, 'empleado_id');

        return $query->get()->all();
    }

    /**
     * Obtiene un empleado por su ID desde la vista `public.vw_empleados`.
     */
    public function obtenerEmpleadoPorId(int $empleadoId): ?object
    {
        return DB::table('public.vw_empleados')
            ->where('empleado_id', $empleadoId)
            ->first();
    }

    /**
     * Consulta la vista de solo lectura `public.vw_direccion_empleados`.
     *
     * @return list<object>
     */
    public function listarDireccionesEmpleados(?int $empleadoId = null): array
    {
        $query = DB::table('public.vw_direccion_empleados');
        if ($empleadoId !== null) {
            $query->where('empleado_id', $empleadoId);
        }

        return $query->orderBy('empleado_id', 'asc')->get()->all();
    }

    /**
     * Consulta la vista de solo lectura `public.vw_familiares`.
     *
     * @return list<object>
     */
    public function listarFamiliares(?int $empleadoId = null): array
    {
        $query = DB::table('public.vw_familiares');
        if ($empleadoId !== null) {
            $query->where('empleado_id', $empleadoId);
        }

        return $query->orderBy('empleado_id', 'asc')->get()->all();
    }

    /**
     * Ejecuta `public.fn_obtener_ficha_tecnica_empleado(p_empleado_id)`.
     *
     * @return array<string, mixed>
     */
    public function obtenerFichaTecnica(int $empleadoId): array
    {
        /** @var array<string, mixed> */
        return $this->ejecutarFuncionJson('public.fn_obtener_ficha_tecnica_empleado', $empleadoId);
    }

    /**
     * Ejecuta `public.fn_obtener_direccion_empleado(p_empleado_id)`.
     *
     * @return array<string, mixed>
     */
    public function obtenerDireccion(int $empleadoId): array
    {
        /** @var array<string, mixed> */
        return $this->ejecutarFuncionJson('public.fn_obtener_direccion_empleado', $empleadoId);
    }

    /**
     * Ejecuta `public.fn_obtener_familiares_empleado(p_empleado_id)`.
     *
     * @return array<int|string, mixed>
     */
    public function obtenerFamiliares(int $empleadoId): array
    {
        return $this->ejecutarFuncionJson('public.fn_obtener_familiares_empleado', $empleadoId);
    }

    /**
     * Ejecuta `public.fn_obtener_telefonos_empleado(p_empleado_id)`.
     *
     * @return array<int|string, mixed>
     */
    public function obtenerTelefonos(int $empleadoId): array
    {
        return $this->ejecutarFuncionJson('public.fn_obtener_telefonos_empleado', $empleadoId);
    }

    /**
     * Ejecuta `public.fn_obtener_tallas_persona(p_persona_id)`.
     *
     * @return array<string, mixed>
     */
    public function obtenerTallasPersona(int $personaId): array
    {
        /** @var array<string, mixed> */
        return $this->ejecutarFuncionJson('public.fn_obtener_tallas_persona', $personaId);
    }
}
