<?php

declare(strict_types=1);

namespace App\Services;

use Flight;
use InvalidArgumentException;
use App\config\SessionKeys;
use App\Services\AuthContext;

final class AuthSession
{

    /**
     * Guarda los datos del usuario autenticado usando el plugin oficial de sesiones de Flight
     */
    public static function login(array $user): void
    {
        // Datos de usuario al logearse 
        $user_id = $user['usuario_id'] ?? null;

        // Validar que el campo obligatorio exista
        if (empty($user_id)) {
            throw new InvalidArgumentException('No se puede iniciar sesión: falta el identificador del usuario (usuario_id).');
        }

        // Regeneramos el ID de sesión por seguridad tras el login
        AuthContext::session()->regenerate(true);

        // Guardamos los datos de forma directa y limpia
        AuthContext::session()->set(SessionKeys::ID, $user_id ?? null);
        AuthContext::session()->set(SessionKeys::NAME, $user['nombre_usuario'] ?? null);
        AuthContext::session()->set(SessionKeys::STATUS, $user['estatus'] ?? null);
        AuthContext::session()->set(SessionKeys::EMPLOYEE_ID, $user['empleado']['empleado_id'] ?? null);
        AuthContext::session()->set(SessionKeys::EMPLOYEE_NAME, $user['empleado']['nombre_completo'] ?? null);
        AuthContext::session()->set(SessionKeys::EMPLOYEE_EMAIL, $user['empleado']['correo'] ?? null);
        AuthContext::session()->set(SessionKeys::ROLE, $user['rol'] ?? null);
        AuthContext::session()->set(SessionKeys::PERMISSION, $user['permisos'] ?? []);
        AuthContext::session()->set(SessionKeys::IS_LOGGED_IN, true);
    }

    /**
     * Destruye la sesión (Cerrar sesión)
     */
    public static function logout(): void
    {

        // 1. Limpiamos todos los datos del contenedor de sesión de Flight de forma segura
        if (method_exists(AuthContext::session(), 'clear')) {
            AuthContext::session()->clear();
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

        Flight::redirect('/login');
    }

}
