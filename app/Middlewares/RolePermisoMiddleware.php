<?php

namespace App\Atlas\Middlewares;

use App\Atlas\Error\ErrorAccess;

class RolePermisoMiddleware{

    // Matriz base institucional para permisos por rol
    private static array $arrayRoles = [
        'admin' => [
            'panel_completo',
            'verUsuarios',
            'gestionar_empleados'
        ],
        'analista' => [
            'ver_usuarios'
        ],
        'comedor' => [
            'verComidas'
        ]
    ];

    //Validación ligera: Solo exige un rol específico
    public static function verificarRol(string $rolRequerido): bool {
        $rolActual =$_SESSION['usuario']['rol'] ?? null;

        //Si no tiene rol asignado en la sesión, se bloquea
        if (empty($rolActual)) {
           ErrorAccess::imprimirError(1, 403);
        }

        //Pase libre absoluto si es superadmin o admin
        if ($rolActual === 'superadmin' || $rolActual === 'admin') {
            return true; 
        }

        //Para cualquier otro rol, debe coincidir exactamente con el rol requerido de la ruta
        if ($rolActual !== $rolRequerido) {
            ErrorAccess::imprimirError(1, 403);
        }

        return false;
    }

    
    //Validación estricta: Valida el Rol Y el Permiso específico
     
    public static function verificarRolYPermiso(string $rolRequerido, string $permisoRequerido): void {
        // Si verificarRol devuelve TRUE, significa que es Admin/Superadmin.
        // Al poner el "return;", detenemos este método aquí mismo y le damos acceso libre.
        if (self::verificarRol($rolRequerido) === true) {
            return;
        }

        $permisosUsuario = $_SESSION['usuario']['permisos'] ?? [];
        // Validar si tiene permisos asignados
        if (empty($permisosUsuario)) {
            ErrorAccess::imprimirError(3, 403);
        }

        $permisosDelRol = self::$arrayRoles[$rolRequerido] ?? [];

        $tienePermisoMatriz = \in_array($permisoRequerido, $permisosDelRol, true);
        $tienePermisoUsuario = \in_array($permisoRequerido, $permisosUsuario, true);

        if (!$tienePermisoMatriz || !$tienePermisoUsuario) {
            ErrorAccess::imprimirError(3, 403);
        }
    }
}
