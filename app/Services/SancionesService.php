<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Database\Capsule\Manager as DB;

/**
 * Servicio para consultas de la vista de sanciones disciplinarias (`public.vw_sanciones_empleados`).
 */
final class SancionesService extends BaseVistaService
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
        $this->aplicarFiltros(
            $query,
            $filtros,
            camposTexto: ['codigo_empleado', 'nombre_completo', 'tipo_sancion', 'num_memorandum'],
            camposEnteros: ['sanciones_disciplinaria_id', 'empleado_id', 'cedula'],
        );
        $this->aplicarOrdenYLimites($query, $filtros, 'sanciones_disciplinaria_id', 'desc');

        return $query->get()->all();
    }
}
