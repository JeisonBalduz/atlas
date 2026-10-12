<?php

namespace App\Atlas\Controllers;

use App\Atlas\Config\MapeoVistas; // Tu mapa de palabras clave
use App\Atlas\Error\ErrorRouter;

class VistaController {

    public static function renderizar(): void {
        // 1. Capturamos la URL actual que el usuario está visitando (ej: 'verUsuario')
        $urlActual = isset($_GET['url']) ? trim($_GET['url'], '/') : 'home';

        // 2. Buscamos esa palabra clave en tu mapa de rutas de vistas
        $rutaRelativa = MapeoVistas::obtenerRuta($urlActual);

        if (!$rutaRelativa) {
            // Si no encontramos la ruta, mostramos un error 404
            ErrorRouter::imprimirError(0, 404);
            return;
        }

        // 3. Construimos la ruta física del archivo
        $archivoVista = MapeoVistas::cargarRuta($rutaRelativa);

        if (file_exists($archivoVista)) {
            require_once $archivoVista;
        } else {
            // Si el archivo no existe, mostramos un error 404
            ErrorRouter::imprimirError(1, 404);
        }

        return;
    }
}