<?php
//Ruta dinámica para servir cualquier archivo o subcarpeta dentro de resources/css/
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

//Ruta dinámica para servir cualquier archivo o subcarpeta dentro de resources/js/
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

//Ruta dinamica para servir las fotos de los empleados
Flight::route('/photo/*', function() {
    $request = Flight::request();
    $path = str_replace('/photo/', '', $request->url);
    $path = ltrim($path, '/');
    
    // Subimos desde la ubicación actual hasta la raíz de tu proyecto 'atlas', 
    // y de ahí entramos a app/storage/photos/
    $filePath = dirname(__DIR__, 2) . '/app/storage/photos/' . $path;
    
    if (file_exists($filePath) && !is_dir($filePath)) {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        $mimeTypes = [
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'webp' => 'image/webp',
            'gif'  => 'image/gif',
        ];
        
        $contentType = $mimeTypes[$ext] ?? 'application/octet-stream';
        $fileName = basename($filePath);
        
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: inline; filename="' . $fileName . '"');
        header('Content-Length: ' . filesize($filePath));
        
        readfile($filePath);
        exit;
    } else {
        Flight::halt(404, 'Archivo no encontrado en la ruta: ' . $filePath);
    }
});