<?php

declare(strict_types=1);

namespace App\Services\Documentos;

/**
 * Extensiones ejecutables o de script bloqueadas si aparecen como doble extensión.
 */
final class ExtensionesPeligrosas
{
    /** @var list<string> */
    public const LISTA = [
        'php', 'phtml', 'phar', 'php3', 'php4', 'php5', 'php7', 'php8',
        'exe', 'sh', 'bat', 'cmd', 'com', 'js', 'mjs', 'html', 'htm', 'svg', 'asp', 'aspx', 'jsp',
    ];

    /**
     * @param list<string> $partesNombre
     */
    public function esPeligrosa(array $partesNombre): bool
    {
        foreach ($partesNombre as $indice => $parte) {
            if ($indice > 0 && in_array(strtolower($parte), self::LISTA, true)) {
                return true;
            }
        }

        return false;
    }
}
