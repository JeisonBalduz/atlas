<?php

declare(strict_types=1);

namespace App\Services;

use App\Model\Personas\EmpleadosModel;
use App\Model\Seguridad\PermisosModel;
use App\Model\Seguridad\RolModel;
use App\Model\Seguridad\RolPermisoModel;
use App\Model\Seguridad\UsuariosModel;
use Illuminate\Database\Capsule\Manager as DB;
use InvalidArgumentException;
use Throwable;

/**
 * Servicio para consultas, vistas PL/pgSQL y gestión integral de Usuarios, Roles y Permisos.
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

    /**
     * Métricas reales de seguridad para las cards de Usuarios y Roles.
     *
     * @return array{total_usuarios: int, activos: int, inactivos: int, total_roles: int, total_permisos: int}
     */
    public function resumenSeguridad(): array
    {
        try {
            return [
                'total_usuarios' => UsuariosModel::query()->count(),
                'activos' => UsuariosModel::query()->where('estatus', 1)->count(),
                'inactivos' => UsuariosModel::query()->where('estatus', '!=', 1)->count(),
                'total_roles' => RolModel::query()->count(),
                'total_permisos' => PermisosModel::query()->count(),
            ];
        } catch (Throwable) {
            return [
                'total_usuarios' => 0,
                'activos' => 0,
                'inactivos' => 0,
                'total_roles' => 0,
                'total_permisos' => 0,
            ];
        }
    }

    /**
     * @return array<int|string, string>
     */
    public function opcionesRoles(): array
    {
        /** @var array<int|string, string> */
        return RolModel::query()
            ->orderBy('rol_id')
            ->pluck('rol', 'rol_id')
            ->all();
    }

    /**
     * Devuelve los 63 permisos agrupados por módulo (`empleados`, `vacaciones`, `beneficios`, etc.).
     *
     * @return list<array{modulo: string, permisos: list<array{id: int, codigo: string, descripcion: string}>}>
     */
    public function catalogoPermisosAgrupados(): array
    {
        $grupos = [];
        $permisos = PermisosModel::query()->orderBy('permisos_id')->get();

        foreach ($permisos as $p) {
            $codigo = (string) $p->permiso;
            $partes = explode('.', $codigo, 2);
            $modulo = $partes[0] !== '' ? $partes[0] : 'general';

            $grupos[$modulo][] = [
                'id' => (int) $p->permisos_id,
                'codigo' => $codigo,
                'descripcion' => (string) ($p->descripcion ?? $codigo),
            ];
        }

        $salida = [];
        foreach ($grupos as $modulo => $items) {
            $salida[] = [
                'modulo' => $modulo,
                'permisos' => $items,
            ];
        }

        return $salida;
    }

    /**
     * Lista completa de usuarios enriquecida con su estatus numérico real y cantidad de permisos.
     *
     * @return list<array<string, mixed>>
     */
    public function listarUsuariosGestion(): array
    {
        $conteoPermisos = [];
        foreach (DB::table('usuario_permisos')->selectRaw('usuario_id, COUNT(*) AS total')->groupBy('usuario_id')->get() as $r) {
            $conteoPermisos[(int) $r->usuario_id] = (int) $r->total;
        }

        $metaUsuarios = [];
        foreach (UsuariosModel::query()->select(['usuario_id', 'estatus', 'intentos'])->get() as $u) {
            $metaUsuarios[(int) $u->usuario_id] = [
                'estatus' => (int) $u->estatus,
                'intentos' => (int) $u->intentos,
            ];
        }

        $salida = [];
        foreach ($this->listarUsuarios(['limite' => 300]) as $filaObj) {
            $f = (array) $filaObj;
            $id = (int) ($f['usuario_id'] ?? 0);
            $meta = $metaUsuarios[$id] ?? ['estatus' => 1, 'intentos' => 0];
            $activo = $meta['estatus'] === 1;

            $salida[] = [
                'usuario_id' => $id,
                'empleado_id' => (int) ($f['empleado_id'] ?? 0),
                'rol_id' => (int) ($f['rol_id'] ?? 0),
                'rol' => (string) ($f['rol'] ?? 'Sin rol'),
                'nombre_usuario' => (string) ($f['nombre_usuario'] ?? ''),
                'nombre_completo' => (string) ($f['nombre_completo'] ?? ''),
                'correo' => (string) ($f['correo'] ?? '—'),
                'estatus' => $meta['estatus'],
                'activo' => $activo,
                'estatus_texto' => $activo ? 'Activo' : 'Desactivado',
                'total_permisos' => $conteoPermisos[$id] ?? 0,
            ];
        }

        return $salida;
    }

    /**
     * Registra un nuevo usuario con hash Argon2id y le hereda los permisos base de su rol.
     */
    public function crearUsuario(
        int $empleadoId,
        int $rolId,
        string $nombreUsuario,
        string $clavePlana,
        ?int $pin = null
    ): UsuariosModel {
        $usuarioLimpio = strtolower(trim($nombreUsuario));
        if ($usuarioLimpio === '' || strlen($usuarioLimpio) < 3 || strlen($usuarioLimpio) > 30) {
            throw new InvalidArgumentException('El nombre de usuario debe tener entre 3 y 30 caracteres.', 422);
        }

        if (strlen($clavePlana) < 6) {
            throw new InvalidArgumentException('La contraseña debe tener al menos 6 caracteres.', 422);
        }

        if (!EmpleadosModel::query()->where('empleado_id', $empleadoId)->exists()) {
            throw new InvalidArgumentException('El trabajador seleccionado no existe.', 422);
        }

        if (!RolModel::query()->where('rol_id', $rolId)->exists()) {
            throw new InvalidArgumentException('El rol seleccionado no es válido.', 422);
        }

        if (UsuariosModel::query()->whereRaw('LOWER(nombre_usuario) = ?', [$usuarioLimpio])->exists()) {
            throw new InvalidArgumentException("El nombre de usuario '{$usuarioLimpio}' ya está en uso.", 409);
        }

        if (UsuariosModel::query()->where('empleado_id', $empleadoId)->exists()) {
            throw new InvalidArgumentException('El trabajador seleccionado ya posee una cuenta de usuario registrada.', 409);
        }

        return DB::connection()->transaction(static function () use ($empleadoId, $rolId, $usuarioLimpio, $clavePlana, $pin): UsuariosModel {
            /** @var UsuariosModel $creado */
            $creado = UsuariosModel::query()->create([
                'empleado_id' => $empleadoId,
                'rol_id' => $rolId,
                'nombre_usuario' => $usuarioLimpio,
                'contrasena' => ManejadorClave::hash($clavePlana),
                'pin' => $pin,
                'estatus' => 1,
                'intentos' => 0,
            ]);

            $permisosDelRol = RolPermisoModel::query()
                ->where('rol_id', $rolId)
                ->pluck('permiso_id')
                ->map(static fn ($id): int => (int) $id)
                ->all();

            $creado->permisos()->sync($permisosDelRol);

            return $creado;
        });
    }

    /**
     * Actualiza datos de cuenta, rol, contraseña opcional y sincroniza permisos si cambia el rol.
     */
    public function actualizarUsuario(
        int $usuarioId,
        int $rolId,
        string $nombreUsuario,
        ?string $claveNueva = null,
        ?int $pin = null,
        bool $heredarPermisosRol = false
    ): UsuariosModel {
        $usuario = UsuariosModel::query()->find($usuarioId);
        if (!$usuario instanceof UsuariosModel) {
            throw new InvalidArgumentException('Usuario no encontrado.', 404);
        }

        $usuarioLimpio = strtolower(trim($nombreUsuario));
        if ($usuarioLimpio === '' || strlen($usuarioLimpio) < 3 || strlen($usuarioLimpio) > 30) {
            throw new InvalidArgumentException('El nombre de usuario debe tener entre 3 y 30 caracteres.', 422);
        }

        if (!RolModel::query()->where('rol_id', $rolId)->exists()) {
            throw new InvalidArgumentException('El rol seleccionado no es válido.', 422);
        }

        $duplicado = UsuariosModel::query()
            ->whereRaw('LOWER(nombre_usuario) = ?', [$usuarioLimpio])
            ->where('usuario_id', '!=', $usuarioId)
            ->exists();

        if ($duplicado) {
            throw new InvalidArgumentException("El nombre de usuario '{$usuarioLimpio}' ya pertenece a otra cuenta.", 409);
        }

        if ($claveNueva !== null && trim($claveNueva) !== '' && strlen(trim($claveNueva)) < 6) {
            throw new InvalidArgumentException('La nueva contraseña debe tener al menos 6 caracteres.', 422);
        }

        $rolAnterior = (int) $usuario->rol_id;

        DB::connection()->transaction(static function () use (
            $usuario,
            $rolId,
            $rolAnterior,
            $usuarioLimpio,
            $claveNueva,
            $pin,
            $heredarPermisosRol
        ): void {
            $datos = [
                'rol_id' => $rolId,
                'nombre_usuario' => $usuarioLimpio,
            ];

            if ($claveNueva !== null && trim($claveNueva) !== '') {
                $datos['contrasena'] = ManejadorClave::hash(trim($claveNueva));
            }
            if ($pin !== null) {
                $datos['pin'] = $pin;
            }

            $usuario->fill($datos);
            $usuario->save();

            if ($rolId !== $rolAnterior || $heredarPermisosRol) {
                $permisosDelRol = RolPermisoModel::query()
                    ->where('rol_id', $rolId)
                    ->pluck('permiso_id')
                    ->map(static fn ($id): int => (int) $id)
                    ->all();

                $usuario->permisos()->sync($permisosDelRol);
            }
        });

        return $usuario;
    }

    /**
     * Cambia el `estatus` de un usuario (`1 = Activo`, `0 = Desactivado`).
     */
    public function cambiarEstatus(int $usuarioId, int $nuevoEstatus): UsuariosModel
    {
        $usuario = UsuariosModel::query()->find($usuarioId);
        if (!$usuario instanceof UsuariosModel) {
            throw new InvalidArgumentException('Usuario no encontrado.', 404);
        }

        $estatusSeguro = $nuevoEstatus === 1 ? 1 : 0;
        $usuario->estatus = $estatusSeguro;
        if ($estatusSeguro === 1) {
            $usuario->intentos = 0;
        }
        $usuario->save();

        return $usuario;
    }

    /**
     * Sincroniza directamente los permisos asignados a un usuario en `usuario_permisos`.
     *
     * @param list<int> $permisoIds
     */
    public function actualizarPermisosUsuario(int $usuarioId, array $permisoIds): void
    {
        $usuario = UsuariosModel::query()->find($usuarioId);
        if (!$usuario instanceof UsuariosModel) {
            throw new InvalidArgumentException('Usuario no encontrado.', 404);
        }

        $idsValidos = PermisosModel::query()
            ->whereIn('permisos_id', array_values(array_unique($permisoIds)))
            ->pluck('permisos_id')
            ->map(static fn ($id): int => (int) $id)
            ->all();

        $usuario->permisos()->sync($idsValidos);
    }

    /**
     * Lista todos los roles con conteo real de usuarios y permisos asignados.
     *
     * @return list<array<string, mixed>>
     */
    public function listarRolesConDetalle(): array
    {
        $roles = RolModel::query()
            ->withCount(['usuarios', 'permisos'])
            ->orderBy('rol_id')
            ->get();

        $salida = [];
        foreach ($roles as $r) {
            if (!$r instanceof RolModel) {
                continue;
            }
            $idsPermisos = RolPermisoModel::query()
                ->where('rol_id', (int) $r->rol_id)
                ->pluck('permiso_id')
                ->map(static fn ($id): int => (int) $id)
                ->values()
                ->all();

            $salida[] = [
                'rol_id' => (int) $r->rol_id,
                'rol' => (string) $r->rol,
                'usuarios_count' => (int) ($r->getAttribute('usuarios_count') ?? 0),
                'permisos_count' => count($idsPermisos),
                'permiso_ids' => $idsPermisos,
            ];
        }

        return $salida;
    }

    /**
     * Crea o actualiza un rol en `public.rol`.
     */
    public function guardarRol(?int $rolId, string $nombreRol): RolModel
    {
        $limpio = substr(trim($nombreRol), 0, 50);
        if ($limpio === '') {
            throw new InvalidArgumentException('El nombre del rol es obligatorio.', 422);
        }

        $queryDup = RolModel::query()->whereRaw('LOWER(rol) = ?', [strtolower($limpio)]);
        if ($rolId !== null && $rolId > 0) {
            $queryDup->where('rol_id', '!=', $rolId);
        }

        if ($queryDup->exists()) {
            throw new InvalidArgumentException("Ya existe un rol con el nombre '{$limpio}'.", 409);
        }

        if ($rolId !== null && $rolId > 0) {
            $existente = RolModel::query()->find($rolId);
            if (!$existente instanceof RolModel) {
                throw new InvalidArgumentException('Rol no encontrado.', 404);
            }
            $existente->fill(['rol' => $limpio]);
            $existente->save();
            return $existente;
        }

        /** @var RolModel $nuevo */
        $nuevo = RolModel::query()->create(['rol' => $limpio]);
        return $nuevo;
    }

    /**
     * Actualiza los permisos ejecutables de un rol en `rol_permiso` y opcionalmente sincroniza `usuario_permisos`.
     *
     * @param list<int> $permisoIds
     */
    public function actualizarPermisosRol(int $rolId, array $permisoIds, bool $sincronizarUsuarios = true): void
    {
        $rol = RolModel::query()->find($rolId);
        if (!$rol instanceof RolModel) {
            throw new InvalidArgumentException('Rol no encontrado.', 404);
        }

        $idsValidos = PermisosModel::query()
            ->whereIn('permisos_id', array_values(array_unique($permisoIds)))
            ->pluck('permisos_id')
            ->map(static fn ($id): int => (int) $id)
            ->all();

        DB::connection()->transaction(static function () use ($rol, $rolId, $idsValidos, $sincronizarUsuarios): void {
            $rol->permisos()->sync($idsValidos);

            if ($sincronizarUsuarios) {
                $usuariosDelRol = UsuariosModel::query()->where('rol_id', $rolId)->get();
                foreach ($usuariosDelRol as $u) {
                    $u->permisos()->sync($idsValidos);
                }
            }
        });
    }
}
