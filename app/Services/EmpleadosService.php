<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Database\Capsule\Manager as DB;

/**
 * Servicio para consultas de vistas y funciones PL/pgSQL del módulo de Empleados.
 */
final class EmpleadosService
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

        if (isset($filtros['empleado_id']) && $filtros['empleado_id'] !== '') {
            $query->where('empleado_id', (int) $filtros['empleado_id']);
        }

        if (isset($filtros['persona_id']) && $filtros['persona_id'] !== '') {
            $query->where('persona_id', (int) $filtros['persona_id']);
        }

        if (!empty($filtros['cedula'])) {
            $query->where('cedula', 'ilike', '%' . (string) $filtros['cedula'] . '%');
        }

        if (!empty($filtros['nombre_completo'])) {
            $query->where('nombre_completo', 'ilike', '%' . (string) $filtros['nombre_completo'] . '%');
        }

        if (!empty($filtros['departamento'])) {
            $query->where('departamento', 'ilike', '%' . (string) $filtros['departamento'] . '%');
        }

        if (!empty($filtros['dependencia'])) {
            $query->where('dependencia', 'ilike', '%' . (string) $filtros['dependencia'] . '%');
        }

        if (!empty($filtros['cargo'])) {
            $query->where('cargo', 'ilike', '%' . (string) $filtros['cargo'] . '%');
        }

        if (!empty($filtros['estatus'])) {
            $query->where('estatus', '=', (string) $filtros['estatus']);
        }

        $ordenPor = !empty($filtros['orden_por']) ? (string) $filtros['orden_por'] : 'empleado_id';
        $direccion = (!empty($filtros['direccion']) && strtolower((string) $filtros['direccion']) === 'desc') ? 'desc' : 'asc';
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
        $row = DB::selectOne(
            'SELECT public.fn_obtener_ficha_tecnica_empleado(?) AS resultado',
            [$empleadoId]
        );

        return $this->decodificarJson($row->resultado ?? null);
    }

    /**
     * Ejecuta `public.fn_obtener_direccion_empleado(p_empleado_id)`.
     *
     * @return array<string, mixed>
     */
    public function obtenerDireccion(int $empleadoId): array
    {
        $row = DB::selectOne(
            'SELECT public.fn_obtener_direccion_empleado(?) AS resultado',
            [$empleadoId]
        );

        return $this->decodificarJson($row->resultado ?? null);
    }

    /**
     * Ejecuta `public.fn_obtener_familiares_empleado(p_empleado_id)`.
     *
     * @return array<int|string, mixed>
     */
    public function obtenerFamiliares(int $empleadoId): array
    {
        $row = DB::selectOne(
            'SELECT public.fn_obtener_familiares_empleado(?) AS resultado',
            [$empleadoId]
        );

        return $this->decodificarJson($row->resultado ?? null);
    }

    /**
     * Ejecuta `public.fn_obtener_telefonos_empleado(p_empleado_id)`.
     *
     * @return array<int|string, mixed>
     */
    public function obtenerTelefonos(int $empleadoId): array
    {
        $row = DB::selectOne(
            'SELECT public.fn_obtener_telefonos_empleado(?) AS resultado',
            [$empleadoId]
        );

        return $this->decodificarJson($row->resultado ?? null);
    }

    /**
     * Ejecuta `public.fn_obtener_tallas_persona(p_persona_id)`.
     *
     * @return array<string, mixed>
     */
    public function obtenerTallasPersona(int $personaId): array
    {
        $row = DB::selectOne(
            'SELECT public.fn_obtener_tallas_persona(?) AS resultado',
            [$personaId]
        );

        return $this->decodificarJson($row->resultado ?? null);
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
