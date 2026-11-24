<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\LimpiarInputs;
use App\Services\UsuariosService;
use App\Utils\Config;
use flight\Engine;
use Throwable;

/**
 * Controlador de la pantalla de Roles y Permisos ejecutables por cada rol.
 */
final class RolesPermisosController extends BaseController
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
        $this->renderVista('roles_permisos', [
            'resumen_seguridad' => $this->usuariosService->resumenSeguridad(),
            'catalogo_permisos' => $this->usuariosService->catalogoPermisosAgrupados(),
        ]);
    }

    /**
     * [GET] Lista todos los roles con sus conteos y `permiso_ids` actuales.
     */
    public function listar(): void
    {
        try {
            $this->app->json([
                'ok' => true,
                'data' => $this->usuariosService->listarRolesConDetalle(),
                'resumen' => $this->usuariosService->resumenSeguridad(),
            ], 200);
        } catch (Throwable) {
            $this->jsonError('No se pudieron consultar los roles del sistema.', 500);
        }
    }

    /**
     * [POST] Crea un nuevo rol.
     */
    public function store(): void
    {
        $input = new LimpiarInputs();
        $error = $input->validar(['rol']);
        if ($error !== null) {
            $this->jsonError($error, 422);
            return;
        }

        try {
            $rol = $this->usuariosService->guardarRol(null, (string) $input->get('rol'));
            $this->jsonOk(['rol_id' => (int) $rol->rol_id, 'rol' => (string) $rol->rol], 'Rol creado correctamente.', 201);
        } catch (Throwable $e) {
            $codigo = (int) $e->getCode();
            $this->jsonError($e->getMessage(), $codigo >= 400 && $codigo < 600 ? $codigo : 422);
        }
    }

    /**
     * [PUT] Renombra un rol existente.
     */
    public function update(string $id): void
    {
        $rolId = $this->idPositivo($id, 'rol');
        if ($rolId === null) {
            return;
        }

        $input = new LimpiarInputs();
        $error = $input->validar(['rol']);
        if ($error !== null) {
            $this->jsonError($error, 422);
            return;
        }

        try {
            $rol = $this->usuariosService->guardarRol($rolId, (string) $input->get('rol'));
            $this->jsonOk(['rol_id' => (int) $rol->rol_id, 'rol' => (string) $rol->rol], 'Rol actualizado correctamente.');
        } catch (Throwable $e) {
            $codigo = (int) $e->getCode();
            $this->jsonError($e->getMessage(), $codigo >= 400 && $codigo < 600 ? $codigo : 422);
        }
    }

    /**
     * [PUT/POST] Guarda la matriz de permisos ejecutables de un rol en `rol_permiso`.
     */
    public function actualizarPermisos(string $id): void
    {
        $rolId = $this->idPositivo($id, 'rol');
        if ($rolId === null) {
            return;
        }

        $datos = $this->app->request()->data->getData();
        $rawIds = is_array($datos['permiso_ids'] ?? null) ? $datos['permiso_ids'] : [];
        $sincronizar = !array_key_exists('sincronizar_usuarios', $datos)
            || filter_var($datos['sincronizar_usuarios'], FILTER_VALIDATE_BOOLEAN);

        $permisoIds = [];
        foreach ($rawIds as $val) {
            if (is_numeric($val) && (int) $val > 0) {
                $permisoIds[] = (int) $val;
            }
        }

        try {
            $this->usuariosService->actualizarPermisosRol($rolId, $permisoIds, $sincronizar);
            $this->jsonOk([
                'rol_id' => $rolId,
                'total_permisos' => count($permisoIds),
            ], 'Permisos del rol actualizados correctamente.');
        } catch (Throwable $e) {
            $codigo = (int) $e->getCode();
            $this->jsonError($e->getMessage(), $codigo >= 400 && $codigo < 600 ? $codigo : 422);
        }
    }
}
