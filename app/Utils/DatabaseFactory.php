<?php

declare(strict_types=1);

namespace App\Utils;

use flight\database\SimplePdo;
use InvalidArgumentException;
use PDO;

/**
 * Fábrica de conexión exclusiva para PostgreSQL en el sistema Atlas.
 */
final class DatabaseFactory
{
    /**
     * Verifica si la base de datos está configurada.
     */
    public static function isEnabled(Config $config): bool
    {
        $dbname = (string) $config->get('database.dbname', '');
        return $dbname !== '';
    }

    /**
     * Crea y retorna una instancia de SimplePdo conectada exclusivamente a PostgreSQL.
     */
    public static function create(Config $config): SimplePdo
    {
        $host   = (string) $config->get('database.host', 'localhost');
        $port   = (string) $config->get('database.port', '5432');
        $dbname = (string) $config->get('database.dbname', 'talento_humano');
        
        if ($dbname === '') {
            throw new InvalidArgumentException('El nombre de la base de datos (database.dbname) es obligatorio en config.php');
        }

        // DSN estricto para PostgreSQL
        $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
        
        $user = $config->get('database.user');
        $password = $config->get('database.password');

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        return new SimplePdo(
            $dsn,
            $user !== null && $user !== '' ? (string) $user : null,
            $password !== null ? (string) $password : null,
            $options
        );
    }
}