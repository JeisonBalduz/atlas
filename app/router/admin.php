<?php

declare(strict_types=1);

/**
 * Rutas de recursos y panel administrativo.
 *
 * @var \flight\net\Router $router
 * @var \flight\Engine<object> $app
 */

// Ruta dinámica para servir cualquier archivo o subcarpeta dentro de resources/css/
$router->get('/css/*', function () use ($app): void {
    $request = $app->request();
    // Obtiene todo lo que venga después de /css/ (ej. 'app.css' o 'components/botones.css')
    $path = str_replace('/css/', '', $request->url);
    $filePath = __DIR__ . '/../../resources/css/' . $path;

    if (file_exists($filePath) && !is_dir($filePath)) {
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        $mimeTypes = [
            'css'   => 'text/css',
            'map'   => 'application/json',
            'woff'  => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf'   => 'font/ttf',
        ];

        header('Content-Type: ' . ($mimeTypes[$ext] ?? 'text/plain'));
        readfile($filePath);
        exit;
    }

    $app->halt(404, 'Archivo CSS no encontrado');
});

// Ruta dinámica para servir cualquier archivo o subcarpeta dentro de resources/js/
$router->get('/js/*', function () use ($app): void {
    $request = $app->request();
    $path = str_replace('/js/', '', $request->url);
    $filePath = __DIR__ . '/../../resources/js/' . $path;

    if (file_exists($filePath) && !is_dir($filePath)) {
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        $mimeTypes = [
            'js'   => 'application/javascript',
            'mjs'  => 'application/javascript',
            'json' => 'application/json',
        ];

        header('Content-Type: ' . ($mimeTypes[$ext] ?? 'text/plain'));
        readfile($filePath);
        exit;
    }

    $app->halt(404, 'Archivo JS no encontrado');
});

// Ruta dinámica para servir librerías de app/lib/
$router->get('/lib/*', function () use ($app): void {
    $request = $app->request();
    $path = str_replace('/lib/', '', $request->url);
    $filePath = __DIR__ . '/../../app/lib/' . $path;

    if (file_exists($filePath) && !is_dir($filePath)) {
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        $mimeTypes = [
            'css'   => 'text/css',
            'js'    => 'application/javascript',
            'mjs'   => 'application/javascript',
            'json'  => 'application/json',
            'png'   => 'image/png',
            'jpg'   => 'image/jpeg',
            'svg'   => 'image/svg+xml',
            'woff2' => 'font/woff2',
        ];

        $contentType = $mimeTypes[$ext] ?? mime_content_type($filePath) ?: 'application/octet-stream';

        header('Content-Type: ' . $contentType);
        readfile($filePath);
        exit;
    }

    $app->halt(404, 'Archivo no encontrado');
});

// Ruta dinámica para servir las fotos de los empleados
$router->get('/photo/*', function () use ($app): void {
    $request = $app->request();
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
        header('Content-Length: ' . (string) filesize($filePath));

        readfile($filePath);
        exit;
    }

    $app->halt(404, 'Archivo no encontrado en la ruta: ' . $filePath);
});
