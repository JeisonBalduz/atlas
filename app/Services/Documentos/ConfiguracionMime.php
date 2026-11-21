<?php

declare(strict_types=1);

namespace App\Services\Documentos;

/**
 * Extensiones permitidas y sus tipos MIME válidos.
 */
final class ConfiguracionMime
{
    /** @var array<string, list<string>> */
    public const MIME_POR_EXTENSION = [
        'pdf' => ['application/pdf'],
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
        'webp' => ['image/webp'],
    ];

    public function esValido(string $extension, string $mime): bool
    {
        return in_array($mime, self::MIME_POR_EXTENSION[strtolower($extension)] ?? [], true);
    }
}
