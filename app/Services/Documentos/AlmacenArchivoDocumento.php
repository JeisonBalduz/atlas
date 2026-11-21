<?php

declare(strict_types=1);

namespace App\Services\Documentos;

use App\Model\Personas\DocumentosModel;
use InvalidArgumentException;
use RuntimeException;

/**
 * Crea la carpeta destino, mueve el archivo físico a `DocumentosInces/` y resuelve rutas para descarga.
 */
final class AlmacenArchivoDocumento
{
    public function __construct(private string $raizAlmacenamiento)
    {
    }

    public function guardar(
        string $origenTemporal,
        string $carpetaRelativa,
        string $nombreArchivo,
        bool $esSubidaHttp
    ): string {
        $carpetaDestino = $this->raizAlmacenamiento
            . DIRECTORY_SEPARATOR
            . str_replace('/', DIRECTORY_SEPARATOR, $carpetaRelativa);

        if (!is_dir($carpetaDestino) && !@mkdir($carpetaDestino, 0750, true) && !is_dir($carpetaDestino)) {
            throw new RuntimeException('No se pudo crear la carpeta del documento.', 500);
        }

        $rutaFinal = $carpetaDestino . DIRECTORY_SEPARATOR . $nombreArchivo;
        $ok = $esSubidaHttp ? @move_uploaded_file($origenTemporal, $rutaFinal) : @copy($origenTemporal, $rutaFinal);
        if (!$ok) {
            throw new RuntimeException('No se pudo mover el archivo a su carpeta destino.', 500);
        }

        return $rutaFinal;
    }

    public function rutaAbsoluta(DocumentosModel $documento): string
    {
        $rutaRelativa = (string) $documento->ruta;
        $raizReal = realpath($this->raizAlmacenamiento);

        if ($rutaRelativa === '' || str_contains($rutaRelativa, '..') || str_contains($rutaRelativa, "\0")) {
            throw new RuntimeException('La ruta documental registrada no es válida.', 400);
        }

        if ($raizReal === false) {
            throw new InvalidArgumentException('El directorio DocumentosInces no existe.', 404);
        }

        $archivoReal = realpath($raizReal . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($rutaRelativa, '/\\')));
        if ($archivoReal === false || !is_file($archivoReal) || !str_starts_with($archivoReal, $raizReal . DIRECTORY_SEPARATOR)) {
            throw new InvalidArgumentException('El archivo del documento no existe en disco.', 404);
        }

        return $archivoReal;
    }
}
