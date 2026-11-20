<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Database\Capsule\Manager as DB;

/**
 * Servicio para consultas de vistas y funciones PL/pgSQL del módulo de Usuarios y Permisos.
 */
final class UsuariosService
{
    /**
     * Consulta la vista de solo lectura `public.vw_usuarios`.
     *
     * @param array<string, mixed> $filtros
     * @return list<object>
     */
    public function listarUsuarios(array $filtros = []): array
    {
        $query = DB::table('public.vw_usuarios');

        if (isset($filtros['usuario_id']) && $filtros['usuario_id'] !== '') {
            $query->where('usuario_id', (int) $filtros['usuario_id']);
        }

        if (isset($filtros['empleado_id']) && $filtros['empleado_id'] !== '') {
            $query->where('empleado_id', (int) $filtros['empleado_id']);
        }

        if (!empty($filtros['nombre_usuario'])) {
            $query->where('nombre_usuario', 'ilike', '%' . (string) $filtros['nombre_usuario'] . '%');
        }

        if (!empty($filtros['nombre_completo'])) {
            $query->where('nombre_completo', 'ilike', '%' . (string) $filtros['nombre_completo'] . '%');
        }

        if (isset($filtros['rol_id']) && $filtros['rol_id'] !== '') {
            $query->where('rol_id', (int) $filtros['rol_id']);
        }

        if (!empty($filtros['estatus_texto'])) {
            $query->where('estatus_texto', '=', (string) $filtros['estatus_texto']);
        }

        $ordenPor = !empty($filtros['orden_por']) ? (string) $filtros['orden_por'] : 'usuario_id';
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
     * Consulta la vista de solo lectura `public.vw_usuarios_permisos`.
     *
     * @return list<object>
     */
    public function listarPermisosUsuarios(?int $usuarioId = null): array
    {
        $query = DB::table('public.vw_usuarios_permisos');

        if ($usuarioId !== null) {
            $query->where('usuario_id', $usuarioId);
        }

        return $query->orderBy('usuario_id', 'asc')->orderBy('permisos_id', 'asc')->get()->all();
    }

    /**
     * Ejecuta `public.fn_obtener_datos_usuario(u_usuario_id)`.
     *
     * @return array<string, mixed>
     */
    public function obtenerDatosCompletosUsuario(int $usuarioId): array
    {
        $row = DB::selectOne(
            'SELECT public.fn_obtener_datos_usuario(?) AS resultado',
            [$usuarioId]
        );

        $raw = isset($row->resultado) && is_string($row->resultado) && $row->resultado !== ''
            ? $row->resultado
            : '{}';
        $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

        return is_array($decoded) ? $decoded : [];
    }
}
