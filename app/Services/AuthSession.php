<?php

declare(strict_types=1);

namespace App\Services;

use Flight;
use InvalidArgumentException;

class AuthSession
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

        $session = Flight::session();

        // Regeneramos el ID de sesión por seguridad tras el login
        $session->regenerate(true);

        // Guardamos los datos de forma directa y limpia
        $session->set('user_id', $user_id ?? null);
        $session->set('username', $user['nombre_usuario'] ?? null);
        $session->set('status', $user['estatus'] ?? null);
        $session->set('employee_id', $user['empleado']['empleado_id'] ?? null);
        $session->set('employee_nombre', $user['empleado']['nombre_completo'] ?? null);
        $session->set('employee_correo', $user['empleado']['correo'] ?? null);
        $session->set('role', $user['rol'] ?? null);
        $session->set('permison', $user['permisos'] ?? []);
        $session->set('is_logged_in', true);
    }
    /**
     * Valida si hay un usuario logueado actualmente
     */
    public static  function check(): bool
    {
        $session = Flight::session();
        return $session->get('is_logged_in') === true;
    }

    /**
     * Obtiene un dato específico de la sesión actual
     */
    public static function get(string $key, $default = null)
    {
        $session = Flight::session();
        return $session->get($key, $default);
    }

    public static function all(): array
    {
        $session = Flight::session();
        return [
            'id' => $session->get('user_id'),
            'username' => $session->get('username'),
            'status' => $session->get('status'),
            'role' => $session->get('role'),
            'permison' => $session->get('permison', []),
            'employee_id' => $session->get('employee_id'),
            'employee_nombre' => $session->get('employee_nombre'),
            'employee_correo' => $session->get('employee_correo'),
            'is_logged_in' => $session->get('is_logged_in', false),
        ];
    }

    /**
     * Destruye la sesión (Cerrar sesión)
     */
}
