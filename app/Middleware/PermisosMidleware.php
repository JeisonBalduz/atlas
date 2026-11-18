<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Services\ContextoSesion;
use App\Model\VwWorkerModel;
use Flight;

final class PermisosMidleware
{

    public function __construct(
        private string $permisoRequerido,
    ) {}

    
    // CREAS EL MÉTODO ESTÁTICO FABRICANTE
    public static function permiso(string $permiso): self
    {
        return new self($permiso);
    }

    public function before(): void
    {

        // Excepción de seguridad: El Administrador tiene acceso total
        if (ContextoSesion::role() === 'Administrador') {
            return; // Permite continuar la ejecución
        }

        $nombrePermiso = array_column(ContextoSesion::permisos(), 'nombre_permiso');

        if (!in_array($this->permisoRequerido, $nombrePermiso, true)) {
            $this->restriccionAcceso();
        }
    }

    private function restriccionAcceso(): void
    {

        if (Flight::request()->ajax) {
            Flight::json([
                'error'   => 'Acceso denegado',
                'mensaje' => 'No tienes permisos para realizar esta acción (' . $this->permisoRequerido . ')'
            ], 403);
            exit;
        }

        Flight::response()->status(403);

        // Aquí BladeOne guarda el HTML listo dentro de Flight::response()
        Flight::render('layouts.permission_access', [
            'permisoRequerido' => $this->permisoRequerido,
            'user' => (object) ContextoSesion::todo(),
            'cedula' => preg_replace('/^v-/i', '', VwWorkerModel::getIdentityId(1))
        ]);

        // Forzamos el envío INMEDIATO del HTML al navegador
        Flight::response()->send();

        exit;
    }
}
