<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Database\Capsule\Manager as DB;

/**
 * Servicio para consultas de la vista de sanciones disciplinarias (`public.vw_sanciones_empleados`).
 */
final class SancionesService
{
    /**
     * Consulta la vista de solo lectura `public.vw_sanciones_empleados`.
     *
     * @param array<string, mixed> $filtros
     * @return list<object>
     */
    public function listarSanciones(array $filtros = []): array
    {
        $query = DB::table('public.vw_sanciones_empleados');

        if (isset($filtros['sanciones_disciplinaria_id']) && $filtros['sanciones_disciplinaria_id'] !== '') {
            $query->where('sanciones_disciplinaria_id', (int) $filtros['sanciones_disciplinaria_id']);
        }

        if (isset($filtros['empleado_id']) && $filtros['empleado_id'] !== '') {
            $query->where('empleado_id', (int) $filtros['empleado_id']);
        }

        if (isset($filtros['cedula']) && $filtros['cedula'] !== '') {
            $query->where('cedula', (int) $filtros['cedula']);
        }

        if (!empty($filtros['codigo_empleado'])) {
            $query->where('codigo_empleado', 'ilike', '%' . (string) $filtros['codigo_empleado'] . '%');
        }

        if (!empty($filtros['nombre_completo'])) {
            $query->where('nombre_completo', 'ilike', '%' . (string) $filtros['nombre_completo'] . '%');
        }

        if (!empty($filtros['tipo_sancion'])) {
            $query->where('tipo_sancion', 'ilike', '%' . (string) $filtros['tipo_sancion'] . '%');
        }

        if (!empty($filtros['num_memorandum'])) {
            $query->where('num_memorandum', 'ilike', '%' . (string) $filtros['num_memorandum'] . '%');
        }

        $ordenPor = !empty($filtros['orden_por']) ? (string) $filtros['orden_por'] : 'sanciones_disciplinaria_id';
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
}
