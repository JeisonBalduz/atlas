<?php

namespace App\Atlas\Config;

use App\Atlas\Interfaces\RecursoInterfaces;

class MapeoVistas implements RecursoInterfaces {
    private static array $arrayRutas = [
        'login'   =>  '/../Views/auth/login.php',
        'admin'   =>  '/../Views/admin/index.php',
        'verUsuarios'  => '/../Views/admin/usuarios.php',
        'analista'=> '/../Views/analista/index.php',
        'comedor' => '/../Views/comedor/index.php',
        'verComidas'   => '/../Views/comedor/verComidas.php'
    ];

    public static function obtenerRuta(string $indice): ?string {
        return self::$arrayRutas[$indice] ?? null;
    }

    public static function cargarRuta(string $ruta): ? string {
       return __DIR__ . "{$ruta}";
    }
}