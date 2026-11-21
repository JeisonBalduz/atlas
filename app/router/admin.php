<?php

declare(strict_types=1);

/**
 * Rutas de recursos y panel administrativo.
 *
 * @var \flight\net\Router $router
 * @var \flight\Engine<object> $app
 */

/**
 * Resuelve y sirve un archivo estático garantizando contención dentro de su directorio base.
 *
 * @param array<string, string> $mimeTypes
 */
$servirArchivoSeguro = static function (
    string $directorioBase,
    string $prefijo,
    array $mimeTypes,
    bool $inline = false
) use ($app): void {
    $request = $app->request();
    $rutaUrl = (string) (parse_url($request->url, PHP_URL_PATH) ?? '');
    $relativo = str_starts_with($rutaUrl, $prefijo)
        ? substr($rutaUrl, strlen($prefijo))
        : '';
    $relativo = ltrim(rawurldecode($relativo), '/\\');

    if (
        $relativo === ''
        || str_contains($relativo, "\0")
        || str_contains($relativo, '..')
    ) {
        $app->halt(404, 'Archivo no encontrado');
        return;
    }

    $baseReal = realpath($directorioBase);
    if ($baseReal === false || !is_dir($baseReal)) {
        $app->halt(404, 'Archivo no encontrado');
        return;
    }

    $rutaNormalizada = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativo);
    $archivoReal = realpath($baseReal . DIRECTORY_SEPARATOR . $rutaNormalizada);

    if (
        $archivoReal === false
        || !is_file($archivoReal)
        || !str_starts_with($archivoReal, $baseReal . DIRECTORY_SEPARATOR)
    ) {
        $app->halt(404, 'Archivo no encontrado');
        return;
    }

    $ext = strtolower(pathinfo($archivoReal, PATHINFO_EXTENSION));
    $contentType = $mimeTypes[$ext] ?? (mime_content_type($archivoReal) ?: 'application/octet-stream');

    header('X-Content-Type-Options: nosniff');
    header('Content-Type: ' . $contentType);
    if ($inline) {
        $nombreArchivo = str_replace(["\r", "\n", '"'], '', basename($archivoReal));
        header('Content-Disposition: inline; filename="' . $nombreArchivo . '"');
        $tamano = filesize($archivoReal);
        if ($tamano !== false) {
            header('Content-Length: ' . (string) $tamano);
        }
    }

    readfile($archivoReal);
    exit;
};

// Ruta dinámica para servir cualquier archivo o subcarpeta dentro de resources/css/
$router->get('/css/*', function () use ($servirArchivoSeguro): void {
    $servirArchivoSeguro(dirname(__DIR__, 2) . '/resources/css', '/css/', [
        'css'   => 'text/css; charset=utf-8',
        'map'   => 'application/json',
        'woff'  => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf'   => 'font/ttf',
    ]);
});

// Ruta dinámica para servir cualquier archivo o subcarpeta dentro de resources/js/
$router->get('/js/*', function () use ($servirArchivoSeguro): void {
    $servirArchivoSeguro(dirname(__DIR__, 2) . '/resources/js', '/js/', [
        'js'   => 'application/javascript; charset=utf-8',
        'mjs'  => 'application/javascript; charset=utf-8',
        'json' => 'application/json; charset=utf-8',
    ]);
});

// Ruta dinámica para servir librerías de app/lib/
$router->get('/lib/*', function () use ($servirArchivoSeguro): void {
    $servirArchivoSeguro(dirname(__DIR__, 2) . '/app/lib', '/lib/', [
        'css'   => 'text/css; charset=utf-8',
        'js'    => 'application/javascript; charset=utf-8',
        'mjs'   => 'application/javascript; charset=utf-8',
        'json'  => 'application/json; charset=utf-8',
        'png'   => 'image/png',
        'jpg'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'svg'   => 'image/svg+xml',
        'woff2' => 'font/woff2',
    ]);
});

// Ruta dinámica para servir las fotos de los empleados
$router->get('/photo/*', function () use ($servirArchivoSeguro): void {
    $servirArchivoSeguro(dirname(__DIR__, 2) . '/app/storage/photos', '/photo/', [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'webp' => 'image/webp',
        'gif'  => 'image/gif',
    ], true);
});

