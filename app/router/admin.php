<?php

declare(strict_types=1);

/**
 * Rutas de respaldo para recursos estáticos cuando se invocan vía Router de Flight.
 * En peticiones HTTP normales, `StaticAssetResponder::tryServe()` los resuelve antes del bootstrap.
 *
 * @var \flight\net\Router $router
 * @var \flight\Engine<object> $app
 */

use App\Http\StaticAssetResponder;

$servirArchivoSeguro = static function (
    string $directorioBase,
    string $prefijo,
    array $mimeTypes,
    bool $inline = false
) use ($app): void {
    $rutaUrl = (string) (parse_url($app->request()->url, PHP_URL_PATH) ?? '');
    StaticAssetResponder::servirDesde(
        $directorioBase,
        $prefijo,
        $rutaUrl,
        $mimeTypes,
        $inline ? 86400 : 60,
        $inline
    );
};

$router->get('/css/*', function () use ($servirArchivoSeguro): void {
    $servirArchivoSeguro(dirname(__DIR__, 2) . '/resources/css', '/css/', [
        'css'   => 'text/css; charset=utf-8',
        'map'   => 'application/json',
        'woff'  => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf'   => 'font/ttf',
    ]);
});

$router->get('/js/*', function () use ($servirArchivoSeguro): void {
    $servirArchivoSeguro(dirname(__DIR__, 2) . '/resources/js', '/js/', [
        'js'   => 'application/javascript; charset=utf-8',
        'mjs'  => 'application/javascript; charset=utf-8',
        'json' => 'application/json; charset=utf-8',
    ]);
});

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

$router->get('/photo/*', function () use ($servirArchivoSeguro): void {
    $servirArchivoSeguro(dirname(__DIR__, 2) . '/app/storage/photos', '/photo/', [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'webp' => 'image/webp',
        'gif'  => 'image/gif',
    ], true);
});
