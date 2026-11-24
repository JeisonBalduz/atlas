<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\LimpiarInputs;
use App\Services\UsuariosService;
use App\Utils\Config;
use flight\Engine;
use Throwable;

/**
 * Controlador para registro, listado, edición, cambio de estatus y permisos individuales de Usuarios.
 */
final class UsuariosController extends BaseController
{
    private UsuariosService $usuariosService;

    public function __construct(
        Engine $app,
        Config $config,
        ?UsuariosService $usuariosService = null,
    ) {
        parent::__construct($app, $config);
        $this->usuariosService = $usuariosService ?? new UsuariosService();
    }

    public function render(): void
    {
        $this->renderVista('usuarios', [
            'roles' => $this->usuariosService->opcionesRoles(),
            'resumen_seguridad' => $this->usuariosService->resumenSeguridad(),
            'catalogo_permisos' => $this->usuariosService->catalogoPermisosAgrupados(),
        ]);
    }

    /**
     * [GET] Lista todos los usuarios registrados junto con resumen real de cuentas.
     */
    public function listar(): void
    {
        try {
            $this->app->json([
                'ok' => true,
                'data' => $this->usuariosService->listarUsuariosGestion(),
                'resumen' => $this->usuariosService->resumenSeguridad(),
            ], 200);
        } catch (Throwable) {
            $this->jsonError('No se pudieron consultar los usuarios del sistema.', 500);
        }
    }

    /**
     * [POST] Crea una nueva cuenta de usuario y le asigna los permisos base de su rol.
     */
    public function store(): void
    {
        $input = new LimpiarInputs();
        $error = $input->validar(['empleado_id', 'rol_id', 'nombre_usuario', 'contrasena']);
        if ($error !== null) {
            $this->jsonError($error, 422);
            return;
        }

        $pinRaw = $input->get('pin');
        $pin = is_numeric($pinRaw) && trim((string) $pinRaw) !== '' ? (int) $pinRaw : null;

        try {
            $creado = $this->usuariosService->crearUsuario(
                (int) $input->get('empleado_id'),
                (int) $input->get('rol_id'),
                (string) $input->get('nombre_usuario'),
                (string) $input->get('contrasena'),
                $pin
            );

            $this->jsonOk(['usuario_id' => (int) $creado->usuario_id], 'Usuario registrado correctamente.', 201);
        } catch (Throwable $e) {
            $codigo = (int) $e->getCode();
            $this->jsonError(
                $codigo >= 400 && $codigo < 500 ? $e->getMessage() : 'No se pudo registrar el usuario.',
                $codigo >= 400 && $codigo < 600 ? $codigo : 500
            );
        }
    }

    /**
     * [PUT/POST] Actualiza el rol, nombre de usuario, contraseña opcional y PIN.
     */
    public function update(string $id): void
    {
        $usuarioId = $this->idPositivo($id, 'usuario');
        if ($usuarioId === null) {
            return;
        }

        $input = new LimpiarInputs();
        $error = $input->validar(['rol_id', 'nombre_usuario']);
        if ($error !== null) {
            $this->jsonError($error, 422);
            return;
        }

        $claveRaw = $input->get('contrasena');
        $claveNueva = is_string($claveRaw) && trim($claveRaw) !== '' ? trim($claveRaw) : null;
        $pinRaw = $input->get('pin');
        $pin = is_numeric($pinRaw) && trim((string) $pinRaw) !== '' ? (int) $pinRaw : null;
        $heredar = filter_var($input->get('heredar_permisos_rol', false), FILTER_VALIDATE_BOOLEAN);

        try {
            $actualizado = $this->usuariosService->actualizarUsuario(
                $usuarioId,
                (int) $input->get('rol_id'),
                (string) $input->get('nombre_usuario'),
                $claveNueva,
                $pin,
                $heredar
            );

            $this->jsonOk(['usuario_id' => (int) $actualizado->usuario_id], 'Usuario actualizado correctamente.');
        } catch (Throwable $e) {
            $codigo = (int) $e->getCode();
            $this->jsonError($e->getMessage(), $codigo >= 400 && $codigo < 600 ? $codigo : 422);
        }
    }

    /**
     * [POST/DELETE] Cambia el `estatus` de un usuario (`1 = Activo`, `0 = Desactivado`).
     */
    public function cambiarEstatus(string $id): void
    {
        $usuarioId = $this->idPositivo($id, 'usuario');
        if ($usuarioId === null) {
            return;
        }

        $input = new LimpiarInputs();
        $estatus = (int) $input->get('estatus', 0);

        try {
            $actualizado = $this->usuariosService->cambiarEstatus($usuarioId, $estatus);
            $mensaje = (int) $actualizado->estatus === 1
                ? 'Cuenta de usuario activada correctamente.'
                : 'Cuenta de usuario desactivada correctamente.';

            $this->jsonOk([
                'usuario_id' => (int) $actualizado->usuario_id,
                'estatus' => (int) $actualizado->estatus,
            ], $mensaje);
        } catch (Throwable $e) {
            $codigo = (int) $e->getCode();
            $this->jsonError($e->getMessage(), $codigo >= 400 && $codigo < 600 ? $codigo : 422);
        }
    }

    /**
     * [GET] Devuelve los permisos efectivos asignados al usuario (`vw_usuarios_permisos`).
     */
    public function permisos(string $id): void
    {
        $usuarioId = $this->idPositivo($id, 'usuario');
        if ($usuarioId === null) {
            return;
        }

        try {
            $filas = $this->usuariosService->listarPermisosUsuarios($usuarioId);
            $ids = [];
            $detalle = [];
            foreach ($filas as $fObj) {
                $f = (array) $fObj;
                $pid = (int) ($f['permisos_id'] ?? 0);
                if ($pid > 0) {
                    $ids[] = $pid;
                    $detalle[] = [
                        'permiso_id' => $pid,
                        'permiso' => (string) ($f['permiso'] ?? ''),
                        'descripcion' => (string) ($f['descripcion_permiso'] ?? ''),
                    ];
                }
            }

            $this->jsonOk([
                'usuario_id' => $usuarioId,
                'permiso_ids' => $ids,
                'permisos' => $detalle,
            ]);
        } catch (Throwable) {
            $this->jsonError('No se pudieron consultar los permisos del usuario.', 500);
        }
    }

    /**
     * [PUT/POST] Actualiza los permisos individuales asignados a un usuario en `usuario_permisos`.
     */
    public function actualizarPermisos(string $id): void
    {
        $usuarioId = $this->idPositivo($id, 'usuario');
        if ($usuarioId === null) {
            return;
        }

        $datos = $this->app->request()->data->getData();
        $rawIds = is_array($datos['permiso_ids'] ?? null) ? $datos['permiso_ids'] : [];
        $permisoIds = [];
        foreach ($rawIds as $val) {
            if (is_numeric($val) && (int) $val > 0) {
                $permisoIds[] = (int) $val;
            }
        }

        try {
            $this->usuariosService->actualizarPermisosUsuario($usuarioId, $permisoIds);
            $this->jsonOk(['usuario_id' => $usuarioId, 'total' => count($permisoIds)], 'Permisos del usuario actualizados correctamente.');
        } catch (Throwable $e) {
            $codigo = (int) $e->getCode();
            $this->jsonError($e->getMessage(), $codigo >= 400 && $codigo < 600 ? $codigo : 422);
        }
    }
}
