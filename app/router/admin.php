<?php
// 1. Ruta dinámica para servir cualquier archivo o subcarpeta dentro de resources/css/
Flight::route('/css/*', function() {
    $request = Flight::request();
    // Obtiene todo lo que venga después de /css/ (ej. 'app.css' o 'components/botones.css')
    $path = str_replace('/css/', '', $request->url);
    $filePath = __DIR__ . '/../../resources/css/' . $path;
    
    if (file_exists($filePath) && !is_dir($filePath)) {
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        $mimeTypes = [
            'css' => 'text/css',
            'map' => 'application/json',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
        ];
        
        header('Content-Type: ' . ($mimeTypes[$ext] ?? 'text/plain'));
        readfile($filePath);
        exit;
    } else {
        Flight::halt(404, 'Archivo CSS no encontrado');
    }
});

// 2. Ruta dinámica para servir cualquier archivo o subcarpeta dentro de resources/js/
Flight::route('/js/*', function() {
    $request = Flight::request();
    $path = str_replace('/js/', '', $request->url);
    $filePath = __DIR__ . '/../../resources/js/' . $path;
    
    if (file_exists($filePath) && !is_dir($filePath)) {
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        $mimeTypes = [
            'js' => 'application/javascript',
            'mjs' => 'application/javascript',
            'json' => 'application/json',
        ];
        
        header('Content-Type: ' . ($mimeTypes[$ext] ?? 'text/plain'));
        readfile($filePath);
        exit;
    } else {
        Flight::halt(404, 'Archivo JS no encontrado');
    }
});