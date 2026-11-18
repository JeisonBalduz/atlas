<?php

declare(strict_types=1);

namespace App\Services;

use Flight;
use App\config\ClavesSesion;

final class ContextoSesion
{
    /**
     * Obtiene la instancia compartida del gestor de sesiones de FlightPHP.
     *
     * @return object Instancia de la sesión activa (\flight\Session).
     */
    public static function sesion(): object
    {
        return Flight::session();
    }

    /**
     * Verifica si el usuario tiene una sesión activa e iniciada.
     *
     * @return bool True si el usuario está autenticado, false en caso contrario.
     */
    public static function comprobar(): bool
    {
        return self::sesion()->get(ClavesSesion::ESTA_AUTENTICADO) === true;
    }

    /**
     * Obtiene el identificador único del usuario autenticado.
     *
     * @return int|string|null El ID del usuario o null si no existe en sesión.
     */
    public static function id(): mixed
    {
        return self::sesion()->get(ClavesSesion::ID);
    }

    /**
     * Obtiene el nombre de usuario de la sesión actual.
     *
     * @return string|null El nombre de usuario o null si no está definido.
     */
    public static function nombre(): ?string
    {
        return self::sesion()->get(ClavesSesion::NOMBRE);
    }

    /**
     * Obtiene el estatus actual de la cuenta del usuario.
     *
     * @return string|null El estatus del usuario o null si no existe.
     */
    public static function estatus(): mixed
    {
        return self::sesion()->get(ClavesSesion::ESTADO);
    }

    /**
     * Obtiene el identificador único del empleado asociado al usuario.
     *
     * @return int|string|null El ID del empleado o null si no está vinculado.
     */
    public static function empleadoId(): mixed
    {
        return self::sesion()->get(ClavesSesion::EMPLEADO_ID);
    }

    /**
     * Obtiene el nombre completo del empleado asociado.
     *
     * @return string|null El nombre completo del empleado o null si no existe.
     */
    public static function nombreEmpleado(): ?string
    {
        return self::sesion()->get(ClavesSesion::NOMBRE_EMPLEADO);
    }

    /**
     * Obtiene el correo electrónico institucional o personal del empleado.
     *
     * @return string|null El correo electrónico o null si no está registrado.
     */
    public static function emailEmpleado(): ?string
    {
        return self::sesion()->get(ClavesSesion::CORREO_EMPLEADO);
    }

    /**
     * Obtiene el rol asignado al usuario actual.
     *
     * @return string|null El nombre o identificador del rol, o null si no tiene.
     */
    public static function role(): ?string
    {
        return self::sesion()->get(ClavesSesion::ROL);
    }

    /**
     * Obtiene la lista completa de permisos asignados al usuario.
     *
     * @return array<int, string> Arreglo con los permisos del usuario (vacío si no tiene).
     */
    public static function permisos(): array
    {
        return self::sesion()->get(ClavesSesion::PERMISOS, []);
    }

    /**
     * Comprueba si el usuario cuenta con un permiso específico.
     *
     * @param string $permiso Nombre del permiso a verificar.
     * @return bool True si el usuario posee el permiso, false en caso contrario.
     */
    public static function tienePermiso(string $permiso): bool
    {
        return in_array($permiso, self::permisos(), true);
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
     * Devuelve todos los datos de autenticación del usuario usando las llaves oficiales de ClavesSesion.
     *
     * @return array<string, mixed> Arreglo asociativo indexado con las constantes de ClavesSesion.
     */
    public static function todo(): array
    {
        return [
            ClavesSesion::ID                => self::id(),
            ClavesSesion::NOMBRE            => self::nombre(),
            ClavesSesion::ESTADO            => self::estatus(),
            ClavesSesion::EMPLEADO_ID       => self::empleadoId(),
            ClavesSesion::NOMBRE_EMPLEADO   => self::nombreEmpleado(),
            ClavesSesion::CORREO_EMPLEADO   => self::emailEmpleado(),
            ClavesSesion::ROL               => self::role(),
            ClavesSesion::PERMISOS          => self::permisos(),
            ClavesSesion::ESTA_AUTENTICADO  => self::comprobar(),
        ];
    }
}
