<?php

declare(strict_types=1);

namespace App\Services\Documentos;

use App\Model\Personas\DocumentosModel;
use DateTimeImmutable;
use Illuminate\Database\ConnectionInterface;
use InvalidArgumentException;
use Throwable;

/**
 * Caso de uso: guardar una nueva versión del documento conservando el historial y archivo anterior en disco.
 */
final class ReemplazarDocumento
{
    public function __construct(
        private ConnectionInterface $db,
        private ValidadorDocumento $validador,
        private ConstructorRutaDocumento $constructorRuta,
        private AlmacenArchivoDocumento $almacen,
    ) {
    }

    /**
     * @param array<string, mixed> $archivo
     */
    public function ejecutar(
        int $documentoId,
        array $archivo,
        ?int $nuevoTipoDocumentoId = null,
        ?int $empleadoId = null,
        ?int $familiaId = null,
        bool $esSubidaHttp = true
    ): DocumentosModel {
        $validado = $this->validador->validar($archivo, $esSubidaHttp);

        $anterior = DocumentosModel::query()->with(['persona.empleado', 'persona.familiares'])->find($documentoId);
        if (!$anterior instanceof DocumentosModel) {
            throw new InvalidArgumentException("El documento #{$documentoId} no existe.", 404);
        }

        $propietario = $this->constructorRuta->propietarioDeDocumento($anterior, $empleadoId, $familiaId);
        $ahora = new DateTimeImmutable();
        $destino = $this->constructorRuta->construir(
            $nuevoTipoDocumentoId ?? (int) $anterior->tipo_documento_id,
            $propietario['empleado_id'],
            $propietario['familia_id'],
            $validado['extension'],
            $ahora
        );

        $rutaNuevaFisica = $this->almacen->guardar(
            $validado['tmp_name'],
            $destino['carpeta_relativa'],
            $destino['nombre_archivo'],
            $esSubidaHttp
        );

        try {
            /** @var DocumentosModel $nuevo */
            $nuevo = $this->db->transaction(static function () use ($anterior, $destino, $validado, $ahora): DocumentosModel {
                $anterior->estatus_documento = 'Histórico';
                $anterior->save();

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
}
