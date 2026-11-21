<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Database\Capsule\Manager as DB;

/**
 * Servicio para consultas de la vista de asistencia de empleados (`public.vw_asistencia_empleados`).
 */
final class AsistenciaService extends BaseVistaService
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
        $this->aplicarFiltros(
            $query,
            $filtros,
            camposTexto: ['cedula', 'nombre_completo', 'departamento'],
            camposExactos: ['turno', 'fecha'],
            camposEnteros: ['empleado_id'],
        );
        $this->aplicarOrdenYLimites($query, $filtros, 'empleado_id');

        return $query->get()->all();
    }
}
