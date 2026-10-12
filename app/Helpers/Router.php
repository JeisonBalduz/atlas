<?php

namespace App\Atlas\Helpers;

use App\Atlas\Error\ErrorRouter;

class Router {

    public static function obtenerPathLimpio(): string {
        // 1. Obtenemos la URI completa de la peticion
        $requestUri = $_SERVER['REQUEST_URI'];

        // 2. Extraemos unicamente la parte del path, separandola de los parametros (?id=1)
        // Nota: Los parametros ?id=1 no se pierden, PHP y Flight los leen por separado en $_GET.
        $path = parse_url($requestUri, PHP_URL_PATH);

        // 3. Obtenemos las rutas base del entorno local
        $scriptName = $_SERVER['SCRIPT_NAME']; // Ej: /pruebanueva2/public/index.php
        $basePublic = dirname($scriptName);     // Ej: /pruebanueva2/public
        $baseRoot = dirname($basePublic);       // Ej: /pruebanueva2

        // 4. Limpiamos dinamicamente el prefijo dependiendo de como llegue la peticion
        if (strpos($path, $basePublic) === 0) {
            $path = substr($path, strlen($basePublic));
        } elseif (strpos($path, $baseRoot) === 0) {
            $path = substr($path, strlen($baseRoot));
        }

        // 5. Normalizamos el resultado para asegurarnos de que devuelva /login, /contacto, etc.
        $path = '/' . trim($path, '/');
        
        return ($path === '/' || $path === '') ? '/' : $path;
    }
}