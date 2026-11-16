<?php

declare(strict_types=1);

namespace App\Services;

use Flight;
use App\config\SessionKeys;

final class AuthContext
{
    /**
     * Obtiene la instancia compartida del gestor de sesiones de FlightPHP.
     *
     * @return object Instancia de la sesión activa (\flight\Session).
     */
    public static function session(): object
    {
        return Flight::session();
    }

    /**
     * Verifica si el usuario tiene una sesión activa e iniciada.
     *
     * @return bool True si el usuario está autenticado, false en caso contrario.
     */
    public static function check(): bool
    {
        return self::session()->get(SessionKeys::IS_LOGGED_IN) === true;
    }

    /**
     * Obtiene el identificador único del usuario autenticado.
     *
     * @return int|string|null El ID del usuario o null si no existe en sesión.
     */
    public static function id(): mixed
    {
        return self::session()->get(SessionKeys::ID);
    }

    /**
     * Obtiene el nombre de usuario de la sesión actual.
     *
     * @return string|null El nombre de usuario o null si no está definido.
     */
    public static function name(): ?string
    {
        return self::session()->get(SessionKeys::NAME);
    }

    /**
     * Obtiene el estatus actual de la cuenta del usuario.
     *
     * @return string|null El estatus del usuario o null si no existe.
     */
    public static function status(): mixed
    {
        return self::session()->get(SessionKeys::STATUS);
    }

    /**
     * Obtiene el identificador único del empleado asociado al usuario.
     *
     * @return int|string|null El ID del empleado o null si no está vinculado.
     */
    public static function employeeId(): mixed
    {
        return self::session()->get(SessionKeys::EMPLOYEE_ID);
    }

    /**
     * Obtiene el nombre completo del empleado asociado.
     *
     * @return string|null El nombre completo del empleado o null si no existe.
     */
    public static function employeeName(): ?string
    {
        return self::session()->get(SessionKeys::EMPLOYEE_NAME);
    }

    /**
     * Obtiene el correo electrónico institucional o personal del empleado.
     *
     * @return string|null El correo electrónico o null si no está registrado.
     */
    public static function employeeEmail(): ?string
    {
        return self::session()->get(SessionKeys::EMPLOYEE_EMAIL);
    }

    /**
     * Obtiene el rol asignado al usuario actual.
     *
     * @return string|null El nombre o identificador del rol, o null si no tiene.
     */
    public static function role(): ?string
    {
        return self::session()->get(SessionKeys::ROLE);
    }

    /**
     * Obtiene la lista completa de permisos asignados al usuario.
     *
     * @return array<int, string> Arreglo con los permisos del usuario (vacío si no tiene).
     */
    public static function permissions(): array
    {
        return self::session()->get(SessionKeys::PERMISSION, []);
    }

    /**
     * Comprueba si el usuario cuenta con un permiso específico.
     *
     * @param string $permission Nombre del permiso a verificar.
     * @return bool True si el usuario posee el permiso, false en caso contrario.
     */
    public static function hasPermission(string $permission): bool
    {
        return in_array($permission, self::permissions(), true);
    }

    /**
     * Comprueba si el usuario tiene exactamente el rol especificado.
     *
     * @param string $role Nombre del rol a verificar.
     * @return bool True si coincide con el rol del usuario, false en caso contrario.
     */
    public static function hasRole(string $role): bool
    {
        return self::role() === $role;
    }

    /**
     * Devuelve todos los datos de autenticación del usuario usando las llaves oficiales de SessionKeys.
     *
     * @return array<string, mixed> Arreglo asociativo indexado con las constantes de SessionKeys.
     */
    public static function all(): array
    {
        return [
            SessionKeys::ID             => self::id(),
            SessionKeys::NAME           => self::name(),
            SessionKeys::STATUS         => self::status(),
            SessionKeys::EMPLOYEE_ID    => self::employeeId(),
            SessionKeys::EMPLOYEE_NAME  => self::employeeName(),
            SessionKeys::EMPLOYEE_EMAIL => self::employeeEmail(),
            SessionKeys::ROLE           => self::role(),
            SessionKeys::PERMISSION     => self::permissions(),
            SessionKeys::IS_LOGGED_IN   => self::check(),
        ];
    }
}
