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
        $userId = $user['usuario_id'] ?? null;
        $username = $user['nombre_usuario'] ?? null;

        // Validar que el campo obligatorio exista
        if (empty($userId)) {
            throw new InvalidArgumentException('No se puede iniciar sesión: falta el identificador del usuario (usuario_id).');
        }

        $session = Flight::session();
        
        // Regeneramos el ID de sesión por seguridad tras el login
        $session->regenerate(true);

        // Guardamos los datos de forma directa y limpia
        $session->set('user_id', $userId);
        $session->set('nombre_usuario', $username);
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

    /**
     * Destruye la sesión (Cerrar sesión)
     */

}