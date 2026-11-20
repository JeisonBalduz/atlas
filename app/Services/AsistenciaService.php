<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Database\Capsule\Manager as DB;

/**
 * Servicio para consultas de la vista de asistencia de empleados (`public.vw_asistencia_empleados`).
 */
final class AsistenciaService
{
    /**
     * Consulta la vista de solo lectura `public.vw_asistencia_empleados`.
     *
     * @param array<string, mixed> $filtros
     * @return list<object>
     */
    public function listarAsistencias(array $filtros = []): array
    {
        $query = DB::table('public.vw_asistencia_empleados');

        if (isset($filtros['empleado_id']) && $filtros['empleado_id'] !== '') {
            $query->where('empleado_id', (int) $filtros['empleado_id']);
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

        if (!empty($filtros['turno'])) {
            $query->where('turno', '=', (string) $filtros['turno']);
        }

        if (!empty($filtros['fecha'])) {
            $query->where('fecha', '=', (string) $filtros['fecha']);
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
}
