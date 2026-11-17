<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Services\AuthContext;
use App\Model\VwWorkerModel;
use Flight;

final class PermissionMidleware
{

    public function __construct(
        private string $permissionRequire,
    ) {}

    
    // CREAS EL MÉTODO ESTÁTICO FABRICANTE
    public static function permission(string $permission): self
    {
        return new self($permission);
    }

    public function before(): void
    {

        // Excepción de seguridad: El Administrador tiene acceso total
        if (AuthContext::role() === 'Administrador') {
            return; // Permite continuar la ejecución
        }

        $namePermision = array_column(AuthContext::permissions(), 'nombre_permiso');

        if (!in_array($this->permissionRequire, $namePermision, true)) {
            $this->restrictAccess();
        }
    }

    private function restrictAccess(): void
    {

        if (Flight::request()->ajax) {
            Flight::json([
                'error'   => 'Acceso denegado',
                'mensaje' => 'No tienes permisos para realizar esta acción (' . $this->permissionRequire . ')'
            ], 403);
            exit;
        }

        Flight::response()->status(403);

        // Aquí BladeOne guarda el HTML listo dentro de Flight::response()
        Flight::render('layouts.permission_access', [
            'permissionRequire' => $this->permissionRequire,
            'user' => (object) AuthContext::all(),
            'cedula' => preg_replace('/^v-/i', '', VwWorkerModel::getIdentityId(1))
        ]);

        // Forzamos el envío INMEDIATO del HTML al navegador
        Flight::response()->send();

        exit;
    }
}
