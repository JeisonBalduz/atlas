<?php

declare(strict_types=1);

namespace App\Services\Documentos;

use finfo;
use InvalidArgumentException;

/**
 * Valida archivos subidos (peso, errores HTTP, extensión segura y MIME real).
 */
final class ValidadorDocumento
{
    public function __construct(
        private ConfiguracionMime $configMime,
        private ExtensionesPeligrosas $extensionesPeligrosas,
        private int $tamanoMaximoBytes = 5242880,
    ) {
        if ($this->tamanoMaximoBytes <= 0) {
            $this->tamanoMaximoBytes = 5242880;
        }
    }

    /**
     * @param array<string, mixed> $archivo
     * @return array{tmp_name: string, nombre_original: string, extension: string, tamano: int}
     */
    public function validar(array $archivo, bool $esSubidaHttp = true): array
    {
        return $this->validarArchivo($archivo, $esSubidaHttp);
    }

    /**
     * @param array<string, mixed> $archivo
     * @return array{tmp_name: string, nombre_original: string, extension: string, tamano: int}
     */
    private function validarArchivo(array $archivo, bool $esSubidaHttp): array
    {
        if (!isset($archivo['name'], $archivo['tmp_name'], $archivo['error']) || !is_string($archivo['name']) || !is_string($archivo['tmp_name'])) {
            throw new InvalidArgumentException('Estructura de archivo inválida.', 422);
        }

        $error = (int) $archivo['error'];
        if ($error !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException('Error al recibir el archivo subido.', 422);
        }

        $tmp = $archivo['tmp_name'];
        if ($tmp === '' || !is_file($tmp) || ($esSubidaHttp && !is_uploaded_file($tmp))) {
            throw new InvalidArgumentException('El archivo temporal no es válido.', 422);
        }

        $tamano = filesize($tmp);
        if ($tamano === false || $tamano <= 0 || $tamano > $this->tamanoMaximoBytes) {
            $maxMb = round($this->tamanoMaximoBytes / 1048576, 2);
            throw new InvalidArgumentException("El archivo debe pesar entre 1 byte y {$maxMb} MB.", 422);
        }

        $extension = $this->extraerExtensionSegura($archivo['name']);
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);

        if (!is_string($mime) || !$this->configMime->esValido($extension, $mime)) {
            throw new InvalidArgumentException("El contenido del archivo no coincide con .{$extension}.", 422);
        }

        return [
            'tmp_name' => $tmp,
            'nombre_original' => substr(basename(str_replace('\\', '/', trim($archivo['name']))), 0, 200) ?: 'documento',
            'extension' => $extension,
            'tamano' => (int) $tamano,
        ];
    }

    private function extraerExtensionSegura(string $nombre): string
    {
        $base = basename(str_replace('\\', '/', trim($nombre)));
        $partes = explode('.', $base);
        if ($base === '' || str_contains($base, "\0") || count($partes) < 2) {
            throw new InvalidArgumentException('El archivo debe tener un nombre y extensión válidos.', 422);
        }

        $extension = strtolower((string) array_pop($partes));
        if (!isset(ConfiguracionMime::MIME_POR_EXTENSION[$extension])) {
            throw new InvalidArgumentException("Extensión .{$extension} no permitida.", 422);
        }

        if ($this->extensionesPeligrosas->esPeligrosa($partes)) {
            throw new InvalidArgumentException('Nombre rechazado por doble extensión peligrosa.', 422);
        }

        return $extension;
    }
}
