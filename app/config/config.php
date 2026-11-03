<?php

/**
 * Project defaults — literal array only (Runway-safe).
 *
 * Copy to config.php (composer create-project does this for you).
 * Secrets and deploy-specific values belong in .env (see .env.example).
 *
 * Do NOT put $_ENV expressions here: `runway config:set` rewrites this
 * file as static literals and would bake resolved secrets into the file.
 *
 * Side effects (timezone, error_reporting, flight.* settings) live in
 * bootstrap.php so Runway only rewrites the data block.
 */
return [
    'app' => [
        'env' => 'development',
        'debug' => true,
        // Trailing slash is normalized at runtime (Config::baseUrl()).
        // Use '/' for app root, or a subpath like '/myapp' / '/myapp/'.
        'base_url' => '/',
        'timezone' => 'UTC',
    ],
    'database' => [
        'driver' => 'pgsql',
        'host' => 'localhost',
        'port' => '5432',
        'dbname' => 'talento_humano',
        'user' => 'postgres',
        'password' => '190803',
        'charset' => 'utf8',
    ],
    'session' => [
        'prefix' => 'flight_sess_',
        // null = system temp /flight_sessions (see flightphp/session docs)
        'save_path' => null,
    ],
    'runway' => [
        'index_root' => 'public/index.php',
        'app_root' => 'app/',
    ],
];
