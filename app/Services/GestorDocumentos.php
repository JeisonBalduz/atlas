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
 * Servicio unificado y directo para carga, reemplazo y control de vigencia de documentos.
 * Regla obligatoria: al subir un archivo nuevo de un documento ya cargado para la misma persona,
 * el registro anterior cambia de 'Vigente' a 'No vigente' y el nuevo queda 'Vigente'.
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

    private int $tamanoMaximoBytes;

    public function __construct(
        private ConnectionInterface $db,
        private string $raizAlmacenamiento,
        int $tamanoMaximoBytes = 5242880,
    ) {
        $this->tamanoMaximoBytes = $tamanoMaximoBytes > 0 ? $tamanoMaximoBytes : 5242880;
    }

    /**
     * Valida, guarda en disco y registra el documento como 'Vigente', marcando como 'No vigente'
     * cualquier versión anterior del mismo tipo para la misma persona.
     *
     * @param array<string, mixed> $archivo
     */
    public function subir(
        array $archivo,
        int $tipoDocumentoId,
        int $empleadoId,
        ?int $familiaId = null,
        bool $esSubidaHttp = true
    ): DocumentosModel {
        $validado = $this->validarArchivo($archivo, $esSubidaHttp);
        $ahora = new DateTimeImmutable();
        $destino = $this->construirDestino($tipoDocumentoId, $empleadoId, $familiaId, $validado['extension'], $ahora);
        $rutaFisica = $this->guardarEnDisco($validado['tmp_name'], $destino['carpeta_relativa'], $destino['nombre_archivo'], $esSubidaHttp);

        try {
            /** @var DocumentosModel $documento */
            $documento = $this->db->transaction(static function () use ($destino, $validado, $ahora): DocumentosModel {
                DocumentosModel::query()
                    ->where('persona_id', $destino['persona_id'])
                    ->where('tipo_documento_id', $destino['tipo_documento_id'])
                    ->where('estatus_documento', 'Vigente')
                    ->update(['estatus_documento' => 'No vigente']);

                return DocumentosModel::query()->create([
                    'tipo_documento_id' => $destino['tipo_documento_id'],
                    'persona_id' => $destino['persona_id'],
                    'codigo' => $destino['codigo'],
                    'ruta' => $destino['ruta_relativa'],
                    'estatus_documento' => 'Vigente',
                    'nombre_original' => $validado['nombre_original'],
                    'tamano' => $validado['tamano'],
                    'formato' => $validado['extension'],
                    'fecha_subida' => $ahora,
                ]);
            });
        } catch (Throwable $e) {
            if (is_file($rutaFisica)) {
                @unlink($rutaFisica);
            }
            throw $e;
        }

        $documento->load(['tipoDocumento', 'persona']);

        return $documento;
    }

    /**
     * Reemplaza un documento existente marcándolo como 'No vigente' y creando el nuevo como 'Vigente'.
     *
     * @param array<string, mixed> $archivo
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

        $anterior = DocumentosModel::query()->with(['persona.empleado', 'persona.familiares'])->find($documentoId);
        if (!$anterior instanceof DocumentosModel) {
            throw new InvalidArgumentException("El documento #{$documentoId} no existe.", 404);
        }

        $propietario = $this->resolverPropietario($anterior, $empleadoId, $familiaId);
        $ahora = new DateTimeImmutable();
        $destino = $this->construirDestino(
            $nuevoTipoDocumentoId ?? (int) $anterior->tipo_documento_id,
            $propietario['empleado_id'],
            $propietario['familia_id'],
            $validado['extension'],
            $ahora
        );

        $rutaNuevaFisica = $this->guardarEnDisco($validado['tmp_name'], $destino['carpeta_relativa'], $destino['nombre_archivo'], $esSubidaHttp);

        try {
            /** @var DocumentosModel $nuevo */
            $nuevo = $this->db->transaction(static function () use ($anterior, $destino, $validado, $ahora): DocumentosModel {
                $anterior->estatus_documento = 'No vigente';
                $anterior->save();

                DocumentosModel::query()
                    ->where('persona_id', $destino['persona_id'])
                    ->where('tipo_documento_id', $destino['tipo_documento_id'])
                    ->where('estatus_documento', 'Vigente')
                    ->update(['estatus_documento' => 'No vigente']);

                return DocumentosModel::query()->create([
                    'tipo_documento_id' => $destino['tipo_documento_id'],
                    'persona_id' => $destino['persona_id'],
                    'codigo' => $destino['codigo'],
                    'ruta' => $destino['ruta_relativa'],
                    'estatus_documento' => 'Vigente',
                    'nombre_original' => $validado['nombre_original'],
                    'tamano' => $validado['tamano'],
                    'formato' => $validado['extension'],
                    'fecha_subida' => $ahora,
                ]);
            });
        } catch (Throwable $e) {
            if (is_file($rutaNuevaFisica)) {
                @unlink($rutaNuevaFisica);
            }
            throw $e;
        }

        $nuevo->load(['tipoDocumento', 'persona']);

        return $nuevo;
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

    /**
     * @param array<string, mixed> $archivo
     * @return array{tmp_name: string, nombre_original: string, extension: string, tamano: int}
     */
    private function validarArchivo(array $archivo, bool $esSubidaHttp): array
    {
        if (!isset($archivo['name'], $archivo['tmp_name'], $archivo['error']) || !is_string($archivo['name']) || !is_string($archivo['tmp_name'])) {
            throw new InvalidArgumentException('Estructura de archivo inválida.', 422);
        }

        if ((int) $archivo['error'] !== UPLOAD_ERR_OK) {
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

        $base = basename(str_replace('\\', '/', trim($archivo['name'])));
        $partes = explode('.', $base);
        if ($base === '' || str_contains($base, "\0") || count($partes) < 2) {
            throw new InvalidArgumentException('El archivo debe tener un nombre y extensión válidos.', 422);
        }

        $extension = strtolower((string) array_pop($partes));
        if (!isset(self::MIME_POR_EXTENSION[$extension])) {
            throw new InvalidArgumentException("Extensión .{$extension} no permitida.", 422);
        }

        foreach ($partes as $indice => $parte) {
            if ($indice > 0 && in_array(strtolower($parte), self::EXTENSIONES_PELIGROSAS, true)) {
                throw new InvalidArgumentException('Nombre rechazado por doble extensión peligrosa.', 422);
            }
        }

        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);
        if (!is_string($mime) || !in_array($mime, self::MIME_POR_EXTENSION[$extension], true)) {
            throw new InvalidArgumentException("El contenido del archivo no coincide con .{$extension}.", 422);
        }

        return [
            'tmp_name' => $tmp,
            'nombre_original' => substr($base, 0, 200) ?: 'documento',
            'extension' => $extension,
            'tamano' => (int) $tamano,
        ];
    }

    /**
     * @return array{
     *     tipo_documento_id: int,
     *     persona_id: int,
     *     carpeta_relativa: string,
     *     nombre_archivo: string,
     *     ruta_relativa: string,
     *     codigo: string
     * }
     */
    private function construirDestino(
        int $tipoDocumentoId,
        int $empleadoId,
        ?int $familiaId,
        string $extension,
        DateTimeImmutable $fecha
    ): array {
        $tipo = $tipoDocumentoId > 0 ? TipoDocumentosModel::query()->find($tipoDocumentoId) : null;
        if (!$tipo instanceof TipoDocumentosModel) {
            throw new InvalidArgumentException("El tipo de documento #{$tipoDocumentoId} no existe.", 404);
        }

        $empleado = $empleadoId > 0 ? EmpleadosModel::query()->with('persona')->find($empleadoId) : null;
        $titular = $empleado instanceof EmpleadosModel ? $empleado->getRelationValue('persona') : null;
        if (!$empleado instanceof EmpleadosModel || !$titular instanceof PersonasModel) {
            throw new InvalidArgumentException("No existe el trabajador #{$empleadoId}.", 404);
        }

        $carpetaTitular = sprintf('%s%d-E%d', strtoupper((string) $titular->nacionalidad ?: 'V'), (int) $titular->cedula, (int) $empleado->empleado_id);
        $hash = strtoupper(bin2hex(random_bytes(3)));
        $carpetaTipo = sprintf('%02d-%s', (int) $tipo->tpdocumentos_id, $this->slug((string) $tipo->tipo_documentos, 60));

        if ($familiaId !== null) {
            $familiar = FamiliaresModel::query()->with('persona')->where('familia_id', $familiaId)->where('empleado_id', $empleadoId)->first();
            $personaFam = $familiar instanceof FamiliaresModel ? $familiar->getRelationValue('persona') : null;
            if (!$familiar instanceof FamiliaresModel || !$personaFam instanceof PersonasModel) {
                throw new InvalidArgumentException("El familiar #{$familiaId} no pertenece al trabajador #{$empleadoId}.", 404);
            }

            $personaId = (int) $personaFam->persona_id;
            $carpetaFam = sprintf('%s%d-P%d', strtoupper((string) $personaFam->nacionalidad ?: 'V'), (int) $personaFam->cedula, $personaId);
            $carpeta = "{$carpetaTitular}/familiares/{$carpetaFam}/{$carpetaTipo}";
            $codigo = substr(sprintf('DF%d-%s', (int) $familiar->familia_id, $hash), 0, 10);
        } else {
            $personaId = (int) $titular->persona_id;
            $carpeta = "{$carpetaTitular}/personales/{$carpetaTipo}";
            $codigo = substr(sprintf('DE%d-%s', (int) $empleado->empleado_id, $hash), 0, 10);
        }

        $nombreArchivo = sprintf('%s_%s_%s.%s', $this->slug((string) $tipo->tipo_documentos, 32), $fecha->format('Ymd-His'), strtolower($hash), $extension);

        return [
            'tipo_documento_id' => (int) $tipo->tpdocumentos_id,
            'persona_id' => $personaId,
            'carpeta_relativa' => $carpeta,
            'nombre_archivo' => $nombreArchivo,
            'ruta_relativa' => "{$carpeta}/{$nombreArchivo}",
            'codigo' => $codigo,
        ];
    }

    /**
     * @return array{empleado_id: int, familia_id: ?int}
     */
    private function resolverPropietario(DocumentosModel $documento, ?int $empleadoId, ?int $familiaId): array
    {
        if ($empleadoId !== null) {
            return ['empleado_id' => $empleadoId, 'familia_id' => $familiaId];
        }

        if ($familiaId !== null) {
            $fam = FamiliaresModel::query()->find($familiaId);
            if (!$fam instanceof FamiliaresModel) {
                throw new InvalidArgumentException("No existe el familiar #{$familiaId}.", 404);
            }

            return ['empleado_id' => (int) $fam->empleado_id, 'familia_id' => (int) $fam->familia_id];
        }

        $persona = $documento->getRelationValue('persona');
        if ($persona instanceof PersonasModel) {
            $familiares = $persona->getRelationValue('familiares');
            if (str_contains((string) $documento->ruta, '/familiares/') && $familiares instanceof \Illuminate\Database\Eloquent\Collection && $familiares->isNotEmpty()) {
                $fam = $familiares->first();
                if ($fam instanceof FamiliaresModel) {
                    return ['empleado_id' => (int) $fam->empleado_id, 'familia_id' => (int) $fam->familia_id];
                }
            }
            $emp = $persona->getRelationValue('empleado');
            if ($emp instanceof EmpleadosModel) {
                return ['empleado_id' => (int) $emp->empleado_id, 'familia_id' => null];
            }
        }

        throw new InvalidArgumentException('No fue posible determinar el propietario del documento.', 404);
    }

    private function guardarEnDisco(string $origenTemporal, string $carpetaRelativa, string $nombreArchivo, bool $esSubidaHttp): string
    {
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

    private function slug(string $texto, int $max): string
    {
        $mapa = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
            'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u', 'Ü' => 'u', 'Ñ' => 'n',
        ];
        $limpio = trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower(strtr(trim($texto), $mapa))), '-');

        return $limpio !== '' ? rtrim(substr($limpio, 0, $max), '-') : 'documento';
    }
}
