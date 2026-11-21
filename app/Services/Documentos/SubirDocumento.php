<?php

declare(strict_types=1);

namespace App\Services\Documentos;

use App\Model\Personas\DocumentosModel;
use DateTimeImmutable;
use Illuminate\Database\ConnectionInterface;
use Throwable;

/**
 * Caso de uso: validar, mover a su carpeta y registrar en `documentos`.
 */
final class SubirDocumento
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
        array $archivo,
        int $tipoDocumentoId,
        int $empleadoId,
        ?int $familiaId = null,
        bool $esSubidaHttp = true
    ): DocumentosModel {
        $validado = $this->validador->validar($archivo, $esSubidaHttp);
        $ahora = new DateTimeImmutable();
        $destino = $this->constructorRuta->construir(
            $tipoDocumentoId,
            $empleadoId,
            $familiaId,
            $validado['extension'],
            $ahora
        );

        $rutaFisica = $this->almacen->guardar(
            $validado['tmp_name'],
            $destino['carpeta_relativa'],
            $destino['nombre_archivo'],
            $esSubidaHttp
        );

        try {
            /** @var DocumentosModel $documento */
            $documento = $this->db->transaction(static fn (): DocumentosModel => DocumentosModel::query()->create([
                'tipo_documento_id' => $destino['tipo_documento_id'],
                'persona_id' => $destino['persona_id'],
                'codigo' => $destino['codigo'],
                'ruta' => $destino['ruta_relativa'],
                'estatus_documento' => 'Vigente',
                'nombre_original' => $validado['nombre_original'],
                'tamano' => $validado['tamano'],
                'formato' => $validado['extension'],
                'fecha_subida' => $ahora,
            ]));
        } catch (Throwable $e) {
            if (is_file($rutaFisica)) {
                @unlink($rutaFisica);
            }
            throw $e;
        }

        $documento->load(['tipoDocumento', 'persona']);

        return $documento;
    }
}
