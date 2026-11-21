<?php

declare(strict_types=1);

namespace App\Http;

/**
 * Respondedor rápido de recursos estáticos (/css, /js, /lib, /photo).
 * Se ejecuta antes del arranque de sesión, PDO/PostgreSQL, Eloquent y BladeOne,
 * evitando bloqueos de sesión y conexiones innecesarias a la base de datos.
 */
final class StaticAssetResponder
{
    /** @var array<string, array{dir: string, mimes: array<string, string>, max_age: int, inline: bool}> */
    private const MAPA_RUTAS = [
        '/css/' => [
            'dir' => 'resources/css',
            'mimes' => [
                'css' => 'text/css; charset=utf-8',
                'map' => 'application/json',
                'woff' => 'font/woff',
                'woff2' => 'font/woff2',
                'ttf' => 'font/ttf',
            ],
            'max_age' => 60,
            'inline' => false,
        ],
        '/js/' => [
            'dir' => 'resources/js',
            'mimes' => [
                'js' => 'application/javascript; charset=utf-8',
                'mjs' => 'application/javascript; charset=utf-8',
                'json' => 'application/json; charset=utf-8',
            ],
            'max_age' => 60,
            'inline' => false,
        ],
        '/lib/' => [
            'dir' => 'app/lib',
            'mimes' => [
                'css' => 'text/css; charset=utf-8',
                'js' => 'application/javascript; charset=utf-8',
                'mjs' => 'application/javascript; charset=utf-8',
                'json' => 'application/json; charset=utf-8',
                'png' => 'image/png',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'svg' => 'image/svg+xml',
                'woff2' => 'font/woff2',
            ],
            'max_age' => 604800,
            'inline' => false,
        ],
        '/photo/' => [
            'dir' => 'app/storage/photos',
            'mimes' => [
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'webp' => 'image/webp',
                'gif' => 'image/gif',
            ],
            'max_age' => 86400,
            'inline' => true,
        ],
    ];

    /**
     * Intenta servir el archivo estático para la petición HTTP actual.
     * Retorna false si la ruta no corresponde a un recurso estático.
     */
    public static function tryServe(string $projectRoot): bool
    {
        if (PHP_SAPI === 'cli') {
            return false;
        }

        $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        if ($method !== 'GET' && $method !== 'HEAD') {
            return false;
        }

        $uriPath = (string) (parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH) ?? '');
        foreach (self::MAPA_RUTAS as $prefijo => $config) {
            if (str_starts_with($uriPath, $prefijo)) {
                $directorio = $projectRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $config['dir']);
                self::servirDesde($directorio, $prefijo, $uriPath, $config['mimes'], $config['max_age'], $config['inline'], $method === 'HEAD');
                return true;
            }
        }

        return false;
    }

    /**
     * Resuelve y envía un archivo validando contención dentro de $directorioBase.
     *
     * @param array<string, string> $mimeTypes
     */
    public static function servirDesde(
        string $directorioBase,
        string $prefijo,
        string $rutaUrl,
        array $mimeTypes,
        int $maxAge = 60,
        bool $inline = false,
        bool $soloCabeceras = false
    ): void {
        $relativo = str_starts_with($rutaUrl, $prefijo)
            ? substr($rutaUrl, strlen($prefijo))
            : '';
        $relativo = ltrim(rawurldecode($relativo), '/\\');

        if ($relativo === '' || str_contains($relativo, "\0") || str_contains($relativo, '..')) {
            self::responder404();
            return;
        }

        $baseReal = realpath($directorioBase);
        if ($baseReal === false || !is_dir($baseReal)) {
            self::responder404();
            return;
        }

        $rutaNormalizada = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativo);
        $archivoReal = realpath($baseReal . DIRECTORY_SEPARATOR . $rutaNormalizada);

        if (
            $archivoReal === false
            || !is_file($archivoReal)
            || !str_starts_with($archivoReal, $baseReal . DIRECTORY_SEPARATOR)
        ) {
            self::responder404();
            return;
        }

        $mtime = filemtime($archivoReal) ?: 0;
        $tamano = filesize($archivoReal);
        $etag = '"' . md5($archivoReal . ':' . $mtime . ':' . ($tamano !== false ? $tamano : 0)) . '"';

        $ext = strtolower(pathinfo($archivoReal, PATHINFO_EXTENSION));
        $contentType = $mimeTypes[$ext] ?? (mime_content_type($archivoReal) ?: 'application/octet-stream');

        header('X-Content-Type-Options: nosniff');
        header('Content-Type: ' . $contentType);
        header('ETag: ' . $etag);
        if ($mtime > 0) {
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $mtime) . ' GMT');
        }
        header('Cache-Control: public, max-age=' . $maxAge . ', must-revalidate');

        $ifNoneMatch = trim((string) ($_SERVER['HTTP_IF_NONE_MATCH'] ?? ''));
        $ifModifiedSince = (string) ($_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? '');
        $noModificadoPorEtag = $ifNoneMatch !== '' && $ifNoneMatch === $etag;
        $noModificadoPorFecha = $ifModifiedSince !== '' && $mtime > 0 && strtotime($ifModifiedSince) >= $mtime;

        if ($noModificadoPorEtag || $noModificadoPorFecha) {
            http_response_code(304);
            exit;
        }

        if ($inline) {
            $nombreArchivo = str_replace(["\r", "\n", '"'], '', basename($archivoReal));
            header('Content-Disposition: inline; filename="' . $nombreArchivo . '"');
        }

        if ($tamano !== false) {
            header('Content-Length: ' . (string) $tamano);
        }

        if (!$soloCabeceras) {
            readfile($archivoReal);
        }
        exit;
    }

    private static function responder404(): void
    {
        http_response_code(404);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Archivo no encontrado';
        exit;
    }
}
