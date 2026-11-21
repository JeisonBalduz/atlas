<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Database\Capsule\Manager as DB;

/**
 * Servicio para consultas de vistas y funciones PL/pgSQL del módulo de Usuarios y Permisos.
 */
final class UsuariosService extends BaseVistaService
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
        $this->aplicarFiltros(
            $query,
            $filtros,
            camposTexto: ['nombre_usuario', 'nombre_completo'],
            camposExactos: ['estatus_texto'],
            camposEnteros: ['usuario_id', 'empleado_id', 'rol_id'],
        );
        $this->aplicarOrdenYLimites($query, $filtros, 'usuario_id', 'desc');

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
        /** @var array<string, mixed> */
        return $this->ejecutarFuncionJson('public.fn_obtener_datos_usuario', $usuarioId);
    }
}
