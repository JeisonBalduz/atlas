<?php

declare(strict_types=1);

namespace App\Services;

use Flight;
use InvalidArgumentException;
use App\config\ClavesSesion;
use App\Services\ContextoSesion;

final class AutentecionSesion
{

    /**
     * Guarda los datos del usuario autenticado usando el plugin oficial de sesiones de Flight
     */
    public static function autenticacion(array $usuario): void
    {
        // Datos de usuario al logearse 
        $usuario_id = $usuario['usuario_id'] ?? null;

        // Validar que el campo obligatorio exista
        if (empty($usuario_id)) {
            throw new InvalidArgumentException('No se puede iniciar sesión: falta el identificador del usuario (usuario_id).');
        }

        // Regeneramos el ID de sesión por seguridad tras el login
        ContextoSesion::sesion()->regenerate(true);

        // Guardamos los datos de forma directa y limpia
         ContextoSesion::sesion()->set(ClavesSesion::ID, $usuario_id ?? null);
        ContextoSesion::sesion()->set(ClavesSesion::NOMBRE, $usuario['nombre_usuario'] ?? null);
        ContextoSesion::sesion()->set(ClavesSesion::ESTADO, $usuario['estatus'] ?? null);
        ContextoSesion::sesion()->set(ClavesSesion::EMPLEADO_ID, $usuario['empleado']['empleado_id'] ?? null);
        ContextoSesion::sesion()->set(ClavesSesion::NOMBRE_EMPLEADO, $usuario['empleado']['nombre_completo'] ?? null);
        ContextoSesion::sesion()->set(ClavesSesion::CORREO_EMPLEADO, $usuario['empleado']['correo'] ?? null);
        ContextoSesion::sesion()->set(ClavesSesion::ROL, $usuario['rol'] ?? null);
        ContextoSesion::sesion()->set(ClavesSesion::PERMISOS, $usuario['permisos'] ?? []);
        ContextoSesion::sesion()->set(ClavesSesion::ESTA_AUTENTICADO, true);
    }

    /**
     * Destruye la sesión (Cerrar sesión)
     */
    public static function cerrarSesion(): void
    {

        // 1. Limpiamos todos los datos del contenedor de sesión de Flight de forma segura
        if (method_exists(ContextoSesion::sesion(), 'clear')) {
            ContextoSesion::sesion()->clear();
        }

        // 2. Invalidamos y borramos la cookie PHPSESSID del navegador para cerrar la sesión por completo
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), 
                '', 
                time() - 42000,
                $params["path"], 
                $params["domain"],
                $params["secure"], 
                $params["httponly"]
            );
        }

        Flight::redirect('/autenticacion');
    }

}
