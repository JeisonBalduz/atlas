<?php

    /**
     * Función global para renderizar vistas fácilmente con Flight
     */
if (!function_exists('view')) {
    /**
     * @param string $template La vista principal (ej. 'home', 'perfil')
     * @param array $data Data exclusiva para la vista principal
     * @param array $headerData Data opcional exclusiva para el header
     */
    function view(string $template, array $data = [], array $headerData = []) {
        
        // 1. Renderizamos el header solo con su data correspondiente (o vacía)
        Flight::render('layouts/header', $headerData);

        // 2. Renderizamos la vista principal con su propia data
        Flight::render($template, $data);

        // 3. Renderizamos el footer (si no necesita data, se queda limpio)
        Flight::render('layouts/footer');
    }
}
