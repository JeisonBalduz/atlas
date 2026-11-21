<?php

declare(strict_types=1);

namespace App\Services;

use App\Model\Personas\DocumentosModel;
use App\Services\Documentos\AlmacenArchivoDocumento;
use App\Services\Documentos\ConfiguracionMime;
use App\Services\Documentos\ConstructorRutaDocumento;
use App\Services\Documentos\ExtensionesPeligrosas;
use App\Services\Documentos\ReemplazarDocumento;
use App\Services\Documentos\SubirDocumento;
use App\Services\Documentos\ValidadorDocumento;
use Illuminate\Database\ConnectionInterface;

/**
 * Servicio principal de gestión documental sobre `app/storage/DocumentosInces/` y `documentos`.
 */
final class GestorDocumentos
{
    private SubirDocumento $subirDocumento;
    private ReemplazarDocumento $reemplazarDocumento;
    private AlmacenArchivoDocumento $almacen;

    public function __construct(
        ConnectionInterface $db,
        string $raizAlmacenamiento,
        int $tamanoMaximoBytes = 5242880,
        ?SubirDocumento $subirDocumento = null,
        ?ReemplazarDocumento $reemplazarDocumento = null,
        ?AlmacenArchivoDocumento $almacen = null,
    ) {
        $validador = new ValidadorDocumento(new ConfiguracionMime(), new ExtensionesPeligrosas(), $tamanoMaximoBytes);
        $constructorRuta = new ConstructorRutaDocumento();
        $this->almacen = $almacen ?? new AlmacenArchivoDocumento($raizAlmacenamiento);

        $this->subirDocumento = $subirDocumento ?? new SubirDocumento($db, $validador, $constructorRuta, $this->almacen);
        $this->reemplazarDocumento = $reemplazarDocumento ?? new ReemplazarDocumento($db, $validador, $constructorRuta, $this->almacen);
    }

    /**
     * @param array<string, mixed> $archivo
     */
    public function subir(
        array $archivo,
        int $tipoDocumentoId,
        int $empleadoId,
        ?int $familiaId = null,
        bool $esSubidaHttp = true
    ): DocumentosModel {
        return $this->subirDocumento->ejecutar($archivo, $tipoDocumentoId, $empleadoId, $familiaId, $esSubidaHttp);
    }

    /**
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
        return $this->reemplazarDocumento->ejecutar($documentoId, $archivo, $nuevoTipoDocumentoId, $empleadoId, $familiaId, $esSubidaHttp);
    }

    public function rutaAbsoluta(DocumentosModel $documento): string
    {
        return $this->almacen->rutaAbsoluta($documento);
    }
}
