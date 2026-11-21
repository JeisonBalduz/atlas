<?php

declare(strict_types=1);

namespace App\Services;

use App\Model\Catalogos\TipoDocumentosModel;
use App\Model\Personas\DocumentosModel;
use App\Model\Personas\EmpleadosModel;
use App\Model\Personas\FamiliaresModel;
use App\Model\Personas\PersonasModel;
use DateTimeImmutable;
use finfo;
use Illuminate\Database\ConnectionInterface;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

/**
 * Servicio único y reutilizable de gestión documental sobre `app/storage/DocumentosInces/` y `documentos`.
 */
final class GestorDocumentos
{
    /** @var array<string, list<string>> */
    private const MIME_POR_EXTENSION = [
        'pdf' => ['application/pdf'],
        'jpg' => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
        'webp' => ['image/webp'],
    ];

    /** @var list<string> */
    private const EXTENSIONES_PELIGROSAS = [
        'php', 'phtml', 'phar', 'php3', 'php4', 'php5', 'php7', 'php8',
        'exe', 'sh', 'bat', 'cmd', 'com', 'js', 'mjs', 'html', 'htm', 'svg', 'asp', 'aspx', 'jsp',
    ];

    public function __construct(
        private ConnectionInterface $db,
        private string $raizAlmacenamiento,
        private int $tamanoMaximoBytes = 5242880,
    ) {
        if ($this->tamanoMaximoBytes <= 0) {
            $this->tamanoMaximoBytes = 5242880;
        }
    }

    /**
     * Valida el archivo, resuelve trabajador/familiar y tipo en BD, guarda en disco y registra en `documentos`.
     *
     * @param array<string, mixed> $archivo Entrada individual de `$request->files` o `$_FILES`.
     */
    public function subir(
        array $archivo,
        int $tipoDocumentoId,
        int $empleadoId,
        ?int $familiaId = null,
        bool $esSubidaHttp = true
    ): DocumentosModel {
        $validado = $this->validarArchivo($archivo, $esSubidaHttp);
        $tipo = $this->resolverTipoDocumento($tipoDocumentoId);
        $propietario = $this->resolverPropietario($empleadoId, $familiaId);
        $ahora = new DateTimeImmutable();
        $destino = $this->construirDestino($propietario, $tipo, $validado['extension'], $ahora);

        $raizReal = $this->asegurarRaiz();
        $carpetaAbsoluta = $this->asegurarSubdirectorio($raizReal, $destino['carpeta_relativa']);
        $rutaFinal = $carpetaAbsoluta . DIRECTORY_SEPARATOR . $destino['nombre_archivo'];
        $rutaPart = $rutaFinal . '.part';

        $this->depositarTemporal($validado['tmp_name'], $rutaPart, $esSubidaHttp);

        try {
            /** @var DocumentosModel $documento */
            $documento = $this->db->transaction(function () use (
                $tipo,
                $propietario,
                $destino,
                $validado,
                $ahora,
                $rutaPart,
                $rutaFinal
            ): DocumentosModel {
                $registro = DocumentosModel::query()->create([
                    'tipo_documento_id' => (int) $tipo->tpdocumentos_id,
                    'persona_id' => $propietario['persona_id'],
                    'codigo' => $destino['codigo'],
                    'ruta' => $destino['ruta_relativa'],
                    'estatus_documento' => 'Vigente',
                    'nombre_original' => $validado['nombre_original'],
                    'tamano' => $validado['tamano'],
                    'formato' => $validado['extension'],
                    'fecha_subida' => $ahora,
                ]);

                if (!@rename($rutaPart, $rutaFinal)) {
                    throw new RuntimeException('No se pudo consolidar el archivo en almacenamiento.', 500);
                }

                return $registro;
            });
        } catch (Throwable $e) {
            if (is_file($rutaPart)) {
                @unlink($rutaPart);
            }
            if (is_file($rutaFinal)) {
                @unlink($rutaFinal);
            }
            $this->limpiarDirectoriosVacios($carpetaAbsoluta, $raizReal);
            throw $e;
        }

        $documento->load(['tipoDocumento', 'persona']);

        return $documento;
    }

    /**
     * Reemplaza el archivo físico y metadatos de un documento existente sin dejar archivos huérfanos.
     *
     * @param array<string, mixed> $archivo Entrada individual de `$request->files` o `$_FILES`.
     */
    public function reemplazar(
        int $documentoId,
        array $archivo,
        ?int $nuevoTipoDocumentoId = null,
        ?int $empleadoId = null,
        ?int $familiaId = null,
        bool $esSubidaHttp = true
    ): DocumentosModel {
        $validado = $this->validarArchivo($archivo, $esSubidaHttp);

        $actual = DocumentosModel::query()
            ->with(['persona.empleado', 'persona.familiares.empleado.persona', 'tipoDocumento'])
            ->find($documentoId);

        if (!$actual instanceof DocumentosModel) {
            throw new InvalidArgumentException("El documento #{$documentoId} no existe.", 404);
        }

        $tipo = $this->resolverTipoDocumento($nuevoTipoDocumentoId ?? (int) $actual->tipo_documento_id);
        $propietario = $this->resolverPropietarioReemplazo($actual, $empleadoId, $familiaId);
        $ahora = new DateTimeImmutable();
        $destino = $this->construirDestino($propietario, $tipo, $validado['extension'], $ahora);

        $raizReal = $this->asegurarRaiz();
        $carpetaAbsoluta = $this->asegurarSubdirectorio($raizReal, $destino['carpeta_relativa']);
        $rutaFinal = $carpetaAbsoluta . DIRECTORY_SEPARATOR . $destino['nombre_archivo'];
        $rutaPart = $rutaFinal . '.part';
        $rutaAnterior = (string) $actual->ruta;

        $this->depositarTemporal($validado['tmp_name'], $rutaPart, $esSubidaHttp);

        try {
            /** @var DocumentosModel $actualizado */
            $actualizado = $this->db->transaction(function () use (
                $documentoId,
                $tipo,
                $propietario,
                $destino,
                $validado,
                $ahora,
                $rutaPart,
                $rutaFinal
            ): DocumentosModel {
                $doc = DocumentosModel::query()->whereKey($documentoId)->lockForUpdate()->first();
                if (!$doc instanceof DocumentosModel) {
                    throw new InvalidArgumentException("El documento #{$documentoId} ya no existe.", 404);
                }

                $doc->fill([
                    'tipo_documento_id' => (int) $tipo->tpdocumentos_id,
                    'persona_id' => $propietario['persona_id'],
                    'codigo' => $destino['codigo'],
                    'ruta' => $destino['ruta_relativa'],
                    'estatus_documento' => 'Vigente',
                    'nombre_original' => $validado['nombre_original'],
                    'tamano' => $validado['tamano'],
                    'formato' => $validado['extension'],
                    'fecha_subida' => $ahora,
                ]);
                $doc->save();

                if (!@rename($rutaPart, $rutaFinal)) {
                    throw new RuntimeException('No se pudo consolidar el nuevo archivo al reemplazar.', 500);
                }

                return $doc;
            });
        } catch (Throwable $e) {
            if (is_file($rutaPart)) {
                @unlink($rutaPart);
            }
            if (is_file($rutaFinal)) {
                @unlink($rutaFinal);
            }
            $this->limpiarDirectoriosVacios($carpetaAbsoluta, $raizReal);
            throw $e;
        }

        if ($rutaAnterior !== '' && $rutaAnterior !== $destino['ruta_relativa']) {
            $this->eliminarArchivoRelativo($rutaAnterior, $raizReal);
        }

        $actualizado->load(['tipoDocumento', 'persona']);

        return $actualizado;
    }

    /**
     * Elimina el registro en BD (desvinculando trámites si existen) y borra el archivo físico.
     */
    public function eliminar(int $documentoId): void
    {
        $documento = DocumentosModel::query()->find($documentoId);
        if (!$documento instanceof DocumentosModel) {
            throw new InvalidArgumentException("El documento #{$documentoId} no existe.", 404);
        }

        $rutaRelativa = (string) $documento->ruta;
        $raizReal = is_dir($this->raizAlmacenamiento) ? realpath($this->raizAlmacenamiento) : false;

        $this->db->transaction(function () use ($documento): void {
            if ($this->db->getSchemaBuilder()->hasTable('tramite_documento')) {
                $documento->tramites()->detach();
            }
            $documento->delete();
        });

        if (is_string($raizReal) && $rutaRelativa !== '') {
            $this->eliminarArchivoRelativo($rutaRelativa, $raizReal);
        }
    }

    /**
     * Devuelve la ruta física verificada dentro de `DocumentosInces/` evitando path traversal.
     */
    public function rutaAbsoluta(DocumentosModel $documento): string
    {
        $rutaRelativa = (string) $documento->ruta;
        if ($rutaRelativa === '' || str_contains($rutaRelativa, "\0") || str_contains($rutaRelativa, '..')) {
            throw new RuntimeException('La ruta documental registrada no es válida.', 400);
        }

        $raizReal = realpath($this->raizAlmacenamiento);
        if ($raizReal === false || !is_dir($raizReal)) {
            throw new InvalidArgumentException('El directorio DocumentosInces no está inicializado.', 404);
        }

        $normalizada = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($rutaRelativa, '/\\'));
        $archivoReal = realpath($raizReal . DIRECTORY_SEPARATOR . $normalizada);

        if (
            $archivoReal === false
            || !is_file($archivoReal)
            || !str_starts_with($archivoReal, $raizReal . DIRECTORY_SEPARATOR)
        ) {
            throw new InvalidArgumentException('El archivo físico del documento no existe en almacenamiento.', 404);
        }

        return $archivoReal;
    }

    /**
     * @param array<string, mixed> $archivo
     * @return array{tmp_name: string, nombre_original: string, extension: string, mime: string, tamano: int}
     */
    private function validarArchivo(array $archivo, bool $esSubidaHttp): array
    {
        if (
            !isset($archivo['name'], $archivo['tmp_name'], $archivo['error'])
            || !is_string($archivo['name'])
            || !is_string($archivo['tmp_name'])
            || !is_numeric($archivo['error'])
        ) {
            throw new InvalidArgumentException('Estructura de archivo inválida o carga múltiple no soportada.', 422);
        }

        $codigoError = (int) $archivo['error'];
        if ($codigoError !== UPLOAD_ERR_OK) {
            $mensaje = match ($codigoError) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'El archivo supera el límite permitido por el servidor.',
                UPLOAD_ERR_PARTIAL => 'El archivo se subió de forma incompleta.',
                UPLOAD_ERR_NO_FILE => 'Debe seleccionar un archivo para subir.',
                default => 'Error al procesar el archivo subido.',
            };
            throw new InvalidArgumentException($mensaje, 422);
        }

        $tmpName = $archivo['tmp_name'];
        if ($tmpName === '' || !is_file($tmpName)) {
            throw new InvalidArgumentException('No se encontró el archivo temporal recibido.', 422);
        }

        if ($esSubidaHttp && !is_uploaded_file($tmpName)) {
            throw new InvalidArgumentException('El archivo no proviene de una subida HTTP válida.', 422);
        }

        $tamano = filesize($tmpName);
        if ($tamano === false || $tamano <= 0) {
            throw new InvalidArgumentException('El archivo está vacío o no pudo leerse.', 422);
        }
        if ($tamano > $this->tamanoMaximoBytes) {
            $maxMb = round($this->tamanoMaximoBytes / 1048576, 2);
            throw new InvalidArgumentException("El archivo excede el tamaño máximo permitido ({$maxMb} MB).", 422);
        }

        $extension = $this->extraerExtensionSegura($archivo['name']);

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmpName);
        if (!is_string($mime) || !in_array($mime, self::MIME_POR_EXTENSION[$extension] ?? [], true)) {
            throw new InvalidArgumentException("El tipo MIME real del archivo no coincide con .{$extension}.", 422);
        }

        $this->validarFirma($tmpName, $extension);

        return [
            'tmp_name' => $tmpName,
            'nombre_original' => $this->sanearNombreOriginal($archivo['name']),
            'extension' => $extension,
            'mime' => $mime,
            'tamano' => (int) $tamano,
        ];
    }

    private function extraerExtensionSegura(string $nombre): string
    {
        $base = basename(str_replace('\\', '/', trim($nombre)));
        if ($base === '' || str_contains($base, "\0")) {
            throw new InvalidArgumentException('Nombre de archivo inválido.', 422);
        }

        $partes = explode('.', $base);
        if (count($partes) < 2) {
            throw new InvalidArgumentException('El archivo debe tener una extensión válida.', 422);
        }

        $extension = strtolower((string) array_pop($partes));
        if (!isset(self::MIME_POR_EXTENSION[$extension])) {
            throw new InvalidArgumentException(
                "Extensión .{$extension} no permitida. Use: " . implode(', ', array_keys(self::MIME_POR_EXTENSION)) . '.',
                422
            );
        }

        foreach ($partes as $i => $segmento) {
            if ($i > 0 && in_array(strtolower($segmento), self::EXTENSIONES_PELIGROSAS, true)) {
                throw new InvalidArgumentException('Nombre de archivo rechazado por doble extensión peligrosa.', 422);
            }
        }

        return $extension;
    }

    private function validarFirma(string $rutaTemporal, string $extension): void
    {
        if ($extension === 'pdf') {
            $fp = @fopen($rutaTemporal, 'rb');
            $cabecera = $fp !== false ? fread($fp, 5) : false;
            if ($fp !== false) {
                fclose($fp);
            }
            if ($cabecera !== '%PDF-') {
                throw new InvalidArgumentException('El archivo no posee una cabecera PDF válida.', 422);
            }
            return;
        }

        $esperado = match ($extension) {
            'jpg', 'jpeg' => IMAGETYPE_JPEG,
            'png' => IMAGETYPE_PNG,
            'webp' => IMAGETYPE_WEBP,
            default => null,
        };

        $info = @getimagesize($rutaTemporal);
        if ($esperado !== null && ($info === false || ($info[2] ?? null) !== $esperado)) {
            throw new InvalidArgumentException("El contenido de la imagen no es válido para .{$extension}.", 422);
        }
    }

    private function sanearNombreOriginal(string $nombre): string
    {
        $soloNombre = basename(str_replace('\\', '/', $nombre));
        $limpio = trim((string) (preg_replace('/[\x00-\x1F\x7F]+/u', '', $soloNombre) ?? ''));
        if ($limpio === '' || $limpio === '.' || $limpio === '..') {
            return 'documento';
        }

        return function_exists('mb_substr') ? mb_substr($limpio, 0, 200, 'UTF-8') : substr($limpio, 0, 200);
    }

    private function resolverTipoDocumento(int $tipoDocumentoId): TipoDocumentosModel
    {
        $tipo = $tipoDocumentoId > 0 ? TipoDocumentosModel::query()->find($tipoDocumentoId) : null;
        if (!$tipo instanceof TipoDocumentosModel) {
            throw new InvalidArgumentException("El tipo de documento #{$tipoDocumentoId} no existe.", 404);
        }

        return $tipo;
    }

    /**
     * @return array{persona_id: int, empleado_id: int, familia_id: ?int, carpeta_titular: string, carpeta_familiar: ?string}
     */
    private function resolverPropietario(int $empleadoId, ?int $familiaId): array
    {
        $empleado = $empleadoId > 0 ? EmpleadosModel::query()->with('persona')->find($empleadoId) : null;
        if (!$empleado instanceof EmpleadosModel || !$empleado->persona instanceof PersonasModel) {
            throw new InvalidArgumentException("No existe el trabajador #{$empleadoId}.", 404);
        }

        $carpetaTitular = $this->carpetaIdentidad($empleado->persona, 'E' . (int) $empleado->empleado_id);

        if ($familiaId === null) {
            return [
                'persona_id' => (int) $empleado->persona->persona_id,
                'empleado_id' => (int) $empleado->empleado_id,
                'familia_id' => null,
                'carpeta_titular' => $carpetaTitular,
                'carpeta_familiar' => null,
            ];
        }

        $familiar = FamiliaresModel::query()
            ->with('persona')
            ->where('familia_id', $familiaId)
            ->where('empleado_id', $empleadoId)
            ->first();

        if (!$familiar instanceof FamiliaresModel || !$familiar->persona instanceof PersonasModel) {
            throw new InvalidArgumentException(
                "El familiar #{$familiaId} no existe o no pertenece al trabajador #{$empleadoId}.",
                404
            );
        }

        return [
            'persona_id' => (int) $familiar->persona->persona_id,
            'empleado_id' => (int) $empleado->empleado_id,
            'familia_id' => (int) $familiar->familia_id,
            'carpeta_titular' => $carpetaTitular,
            'carpeta_familiar' => $this->carpetaIdentidad($familiar->persona, 'P' . (int) $familiar->persona->persona_id),
        ];
    }

    /**
     * @return array{persona_id: int, empleado_id: int, familia_id: ?int, carpeta_titular: string, carpeta_familiar: ?string}
     */
    private function resolverPropietarioReemplazo(
        DocumentosModel $documento,
        ?int $empleadoId,
        ?int $familiaId
    ): array {
        if ($empleadoId !== null) {
            return $this->resolverPropietario($empleadoId, $familiaId);
        }

        if ($familiaId !== null) {
            $fam = FamiliaresModel::query()->with(['persona', 'empleado.persona'])->find($familiaId);
            if (!$fam instanceof FamiliaresModel || !$fam->persona instanceof PersonasModel || !$fam->empleado instanceof EmpleadosModel || !$fam->empleado->persona instanceof PersonasModel) {
                throw new InvalidArgumentException("No existe el familiar #{$familiaId}.", 404);
            }
            return [
                'persona_id' => (int) $fam->persona->persona_id,
                'empleado_id' => (int) $fam->empleado->empleado_id,
                'familia_id' => (int) $fam->familia_id,
                'carpeta_titular' => $this->carpetaIdentidad($fam->empleado->persona, 'E' . (int) $fam->empleado->empleado_id),
                'carpeta_familiar' => $this->carpetaIdentidad($fam->persona, 'P' . (int) $fam->persona->persona_id),
            ];
        }

        $persona = $documento->persona;
        if (!$persona instanceof PersonasModel) {
            throw new InvalidArgumentException('El documento no posee una persona propietaria.', 404);
        }

        $esFamiliar = str_contains((string) $documento->ruta, '/familiares/');
        if (($esFamiliar || !$persona->empleado instanceof EmpleadosModel) && $persona->familiares->isNotEmpty()) {
            $fam = $persona->familiares->first();
            if ($fam instanceof FamiliaresModel && $fam->empleado instanceof EmpleadosModel && $fam->empleado->persona instanceof PersonasModel) {
                return [
                    'persona_id' => (int) $persona->persona_id,
                    'empleado_id' => (int) $fam->empleado->empleado_id,
                    'familia_id' => (int) $fam->familia_id,
                    'carpeta_titular' => $this->carpetaIdentidad($fam->empleado->persona, 'E' . (int) $fam->empleado->empleado_id),
                    'carpeta_familiar' => $this->carpetaIdentidad($persona, 'P' . (int) $persona->persona_id),
                ];
            }
        }

        if ($persona->empleado instanceof EmpleadosModel) {
            return [
                'persona_id' => (int) $persona->persona_id,
                'empleado_id' => (int) $persona->empleado->empleado_id,
                'familia_id' => null,
                'carpeta_titular' => $this->carpetaIdentidad($persona, 'E' . (int) $persona->empleado->empleado_id),
                'carpeta_familiar' => null,
            ];
        }

        throw new InvalidArgumentException('No fue posible determinar el propietario del documento.', 404);
    }

    /**
     * @param array{persona_id: int, empleado_id: int, familia_id: ?int, carpeta_titular: string, carpeta_familiar: ?string} $propietario
     * @return array{carpeta_relativa: string, nombre_archivo: string, ruta_relativa: string, codigo: string}
     */
    private function construirDestino(
        array $propietario,
        TipoDocumentosModel $tipo,
        string $extension,
        DateTimeImmutable $ahora
    ): array {
        $hash = bin2hex(random_bytes(3));
        $slugTipo = $this->slug((string) $tipo->tipo_documentos, 60);
        $carpetaTipo = sprintf('%02d-%s', (int) $tipo->tpdocumentos_id, $slugTipo);

        if ($propietario['carpeta_familiar'] !== null && $propietario['familia_id'] !== null) {
            $carpeta = "{$propietario['carpeta_titular']}/familiares/{$propietario['carpeta_familiar']}/{$carpetaTipo}";
            $codigo = sprintf('DOC-F%d-T%d-%s', $propietario['familia_id'], (int) $tipo->tpdocumentos_id, strtoupper($hash));
        } else {
            $carpeta = "{$propietario['carpeta_titular']}/personales/{$carpetaTipo}";
            $codigo = sprintf('DOC-E%d-T%d-%s', $propietario['empleado_id'], (int) $tipo->tpdocumentos_id, strtoupper($hash));
        }

        $nombreArchivo = sprintf(
            '%s_%s_%s.%s',
            $this->slug((string) $tipo->tipo_documentos, 32),
            $ahora->format('Ymd-His'),
            $hash,
            $extension
        );

        return [
            'carpeta_relativa' => $carpeta,
            'nombre_archivo' => $nombreArchivo,
            'ruta_relativa' => $carpeta . '/' . $nombreArchivo,
            'codigo' => $codigo,
        ];
    }

    private function carpetaIdentidad(PersonasModel $persona, string $sufijo): string
    {
        $nac = strtoupper((string) preg_replace('/[^A-Za-z]/', '', (string) $persona->nacionalidad));
        $nac = $nac !== '' ? substr($nac, 0, 2) : 'V';

        return sprintf('%s%d-%s', $nac, max(0, (int) $persona->cedula), $sufijo);
    }

    private function slug(string $texto, int $max): string
    {
        $mapa = [
            'á' => 'a', 'à' => 'a', 'ä' => 'a', 'â' => 'a', 'Á' => 'a',
            'é' => 'e', 'è' => 'e', 'ë' => 'e', 'ê' => 'e', 'É' => 'e',
            'í' => 'i', 'ì' => 'i', 'ï' => 'i', 'î' => 'i', 'Í' => 'i',
            'ó' => 'o', 'ò' => 'o', 'ö' => 'o', 'ô' => 'o', 'Ó' => 'o',
            'ú' => 'u', 'ù' => 'u', 'ü' => 'u', 'û' => 'u', 'Ú' => 'u', 'Ü' => 'u',
            'ñ' => 'n', 'Ñ' => 'n',
        ];
        $slug = trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower(strtr(trim($texto), $mapa))), '-');
        if ($max > 0 && strlen($slug) > $max) {
            $slug = rtrim(substr($slug, 0, $max), '-');
        }

        return $slug !== '' ? $slug : 'documento';
    }

    private function asegurarRaiz(): string
    {
        if (!is_dir($this->raizAlmacenamiento) && !@mkdir($this->raizAlmacenamiento, 0750, true) && !is_dir($this->raizAlmacenamiento)) {
            throw new RuntimeException('No se pudo crear el directorio raíz DocumentosInces.', 500);
        }

        $raiz = realpath($this->raizAlmacenamiento);
        if ($raiz === false || !is_dir($raiz)) {
            throw new RuntimeException('No se pudo resolver el directorio DocumentosInces.', 500);
        }

        return $raiz;
    }

    private function asegurarSubdirectorio(string $raizReal, string $carpetaRelativa): string
    {
        $destino = $raizReal . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $carpetaRelativa);
        if (!is_dir($destino) && !@mkdir($destino, 0750, true) && !is_dir($destino)) {
            throw new RuntimeException('No se pudo crear el subdirectorio del expediente.', 500);
        }

        $destinoReal = realpath($destino);
        if ($destinoReal === false || !str_starts_with($destinoReal, $raizReal . DIRECTORY_SEPARATOR)) {
            throw new RuntimeException('La ruta destino escapa del directorio DocumentosInces.', 500);
        }

        return $destinoReal;
    }

    private function depositarTemporal(string $origen, string $destinoPart, bool $esSubidaHttp): void
    {
        $ok = $esSubidaHttp ? @move_uploaded_file($origen, $destinoPart) : @copy($origen, $destinoPart);
        if (!$ok) {
            throw new RuntimeException('No se pudo escribir el archivo en almacenamiento.', 500);
        }
    }

    private function eliminarArchivoRelativo(string $rutaRelativa, string $raizReal): void
    {
        if ($rutaRelativa === '' || str_contains($rutaRelativa, '..') || str_contains($rutaRelativa, "\0")) {
            return;
        }

        $objetivo = realpath($raizReal . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($rutaRelativa, '/\\')));
        if ($objetivo !== false && is_file($objetivo) && str_starts_with($objetivo, $raizReal . DIRECTORY_SEPARATOR)) {
            @unlink($objetivo);
            $this->limpiarDirectoriosVacios(dirname($objetivo), $raizReal);
        }
    }

    private function limpiarDirectoriosVacios(string $directorio, string $raizReal): void
    {
        $actual = realpath($directorio);
        while ($actual !== false && $actual !== $raizReal && str_starts_with($actual, $raizReal . DIRECTORY_SEPARATOR) && is_dir($actual)) {
            $contenido = @scandir($actual);
            if ($contenido === false || count($contenido) > 2 || !@rmdir($actual)) {
                break;
            }
            $actual = dirname($actual);
        }
    }
}
