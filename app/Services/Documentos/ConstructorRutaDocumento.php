<?php

declare(strict_types=1);

namespace App\Services\Documentos;

use App\Model\Catalogos\TipoDocumentosModel;
use App\Model\Personas\DocumentosModel;
use App\Model\Personas\EmpleadosModel;
use App\Model\Personas\FamiliaresModel;
use App\Model\Personas\PersonasModel;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Construye la carpeta, ruta relativa, código y nombre destino del documento usando Eloquent.
 */
final class ConstructorRutaDocumento
{
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
    public function construir(
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
        if (!$empleado instanceof EmpleadosModel || !$empleado->persona instanceof PersonasModel) {
            throw new InvalidArgumentException("No existe el trabajador #{$empleadoId}.", 404);
        }

        $titular = $empleado->persona;
        $carpetaTitular = sprintf('%s%d-E%d', strtoupper((string) $titular->nacionalidad ?: 'V'), (int) $titular->cedula, (int) $empleado->empleado_id);
        $hash = strtoupper(bin2hex(random_bytes(3)));
        $carpetaTipo = sprintf('%02d-%s', (int) $tipo->tpdocumentos_id, $this->slug((string) $tipo->tipo_documentos, 60));

        if ($familiaId !== null) {
            $familiar = FamiliaresModel::query()->with('persona')->where('familia_id', $familiaId)->where('empleado_id', $empleadoId)->first();
            if (!$familiar instanceof FamiliaresModel || !$familiar->persona instanceof PersonasModel) {
                throw new InvalidArgumentException("El familiar #{$familiaId} no pertenece al trabajador #{$empleadoId}.", 404);
            }

            $personaId = (int) $familiar->persona->persona_id;
            $carpetaFam = sprintf('%s%d-P%d', strtoupper((string) $familiar->persona->nacionalidad ?: 'V'), (int) $familiar->persona->cedula, $personaId);
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
    public function propietarioDeDocumento(DocumentosModel $documento, ?int $empleadoId, ?int $familiaId): array
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

        $persona = $documento->persona;
        if ($persona instanceof PersonasModel) {
            if (str_contains((string) $documento->ruta, '/familiares/') && $persona->familiares->isNotEmpty()) {
                $fam = $persona->familiares->first();
                if ($fam instanceof FamiliaresModel) {
                    return ['empleado_id' => (int) $fam->empleado_id, 'familia_id' => (int) $fam->familia_id];
                }
            }
            if ($persona->empleado instanceof EmpleadosModel) {
                return ['empleado_id' => (int) $persona->empleado->empleado_id, 'familia_id' => null];
            }
        }

        throw new InvalidArgumentException('No fue posible determinar el propietario del documento.', 404);
    }

    private function slug(string $texto, int $max): string
    {
        $mapa = ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n', 'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u', 'Ü' => 'u', 'Ñ' => 'n'];
        $limpio = trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower(strtr(trim($texto), $mapa))), '-');

        return $limpio !== '' ? rtrim(substr($limpio, 0, $max), '-') : 'documento';
    }
}
