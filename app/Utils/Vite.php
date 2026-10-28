<?php

declare(strict_types=1);

namespace App\Utils;

class Vite
{
    public static function asset(string $path): string
    {
        $isDev = true; // Cambiar a false cuando pases a producción
        $devServer = 'http://localhost:5173';

        if ($isDev) {
            return "{$devServer}/{$path}";
        }

        $manifestPath = __DIR__ . '/../../public/dist/.vite/manifest.json';
        if (file_exists($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true);
            if (isset($manifest[$path]['file'])) {
                return '/dist/' . $manifest[$path]['file'];
            }
        }

        return '/dist/output.css';
    }
}