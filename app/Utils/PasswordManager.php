<?php

declare(strict_types=1);

namespace App\Utils;

class PasswordManager
{
    /**
     * Genera un hash ultra seguro y moderno utilizando Argon2id.
     * Ideal para cuando creas un usuario nuevo o un seeder de pruebas.
     */
    public static function hash(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Verifica de forma segura si la contraseña ingresada coincide con el hash almacenado.
     * Funciona automáticamente sin importar si fue cifrada con Bcrypt o Argon2id.
     */
    public static function verify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}

//$passwordManager = new PasswordManager();
//print_r($passwordManager->verify('123456jb', '$2y$12$Nkf5ieN.BTkmt9y4oFxsUuUtUvy9w07NJDmAKg57lPSvJxkkuV8xm'));