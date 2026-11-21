<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Model\Personas\DocumentosModel;
use App\Model\Personas\EmpleadosModel;
use App\Model\Personas\FamiliaresModel;
use App\Model\Personas\PersonasModel;
use App\Services\CatalogosService;
use App\Services\GestorDocumentos;
use finfo;
use flight\Engine;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

/**
 * Endpoints API y descarga segura de documentos gestionados con GestorDocumentos (100% Eloquent ORM).
 */
final class DocumentosApiController
{
    /**
     * @param Engine<object> $app
     */
    public function __construct(
        private Engine $app,
        private GestorDocumentos $gestorDocumentos,
    ) {
    }

    /**
     * [GET] Lista los documentos personales del trabajador y los de sus familiares asociados.
     */
    public function listarPorEmpleado(string $id): void
    {
        if (!ctype_digit($id) || (int) $id <= 0) {
            $this->app->json(['ok' => false, 'message' => 'Identificador de empleado inválido.'], 400);
            return;
        }

        $empleado = EmpleadosModel::query()
            ->with([
                'persona.documentos.tipoDocumento',
                'familiares.persona.documentos.tipoDocumento',
                'familiares.tipoParentesco',
            ])
            ->find((int) $id);

        if (!$empleado instanceof EmpleadosModel || !$empleado->persona instanceof PersonasModel) {
            $this->app->json(['ok' => false, 'message' => 'Trabajador no encontrado.'], 404);
            return;
        }

        $personales = $empleado->persona->documentos
            ->sortBy('documentos_id')
            ->map(static fn (DocumentosModel $doc): array => self::formatearDocumento($doc))
            ->values()
            ->all();

        $familiaresDocs = [];
        foreach ($empleado->familiares as $familiar) {
            if (!$familiar instanceof FamiliaresModel || !$familiar->persona instanceof PersonasModel) {
                continue;
            }
            foreach ($familiar->persona->documentos->sortBy('documentos_id') as $doc) {
                $item = self::formatearDocumento($doc);
                $item['familia_id'] = (int) $familiar->familia_id;
                $item['parentesco'] = (string) ($familiar->tipoParentesco?->parentesco ?? '');
                $item['propietario_nombre'] = CatalogosService::nombreCompletoPersona($familiar->persona);
                $familiaresDocs[] = $item;
            }
        }

        $this->app->json([
            'ok' => true,
            'empleado_id' => (int) $empleado->empleado_id,
            'personales' => $personales,
            'familiares' => $familiaresDocs,
        ], 200);
    }

    /**
     * [POST] Sube un documento para un trabajador o uno de sus familiares.
     */
    public function store(): void
    {
        $datos = $this->app->request()->data->getData();
        $archivos = $this->app->request()->files->getData();

        $tipoDocumentoId = isset($datos['tipo_documento_id']) && is_numeric($datos['tipo_documento_id'])
            ? (int) $datos['tipo_documento_id']
            : 0;
        $empleadoId = isset($datos['empleado_id']) && is_numeric($datos['empleado_id'])
            ? (int) $datos['empleado_id']
            : 0;
        $familiaId = isset($datos['familia_id']) && $datos['familia_id'] !== '' && is_numeric($datos['familia_id'])
            ? (int) $datos['familia_id']
            : null;

        if ($tipoDocumentoId <= 0 || $empleadoId <= 0) {
            $this->app->json([
                'ok' => false,
                'message' => 'Los campos tipo_documento_id y empleado_id son obligatorios.',
            ], 422);
            return;
        }

        $entradaArchivo = $archivos['archivo'] ?? null;
        if (!is_array($entradaArchivo)) {
            $this->app->json(['ok' => false, 'message' => 'Debe adjuntar un archivo en el campo "archivo".'], 422);
            return;
        }

        try {
            $documento = $this->gestorDocumentos->subir($entradaArchivo, $tipoDocumentoId, $empleadoId, $familiaId);

            $this->app->json([
                'ok' => true,
                'message' => 'Documento cargado correctamente.',
                'data' => self::formatearDocumento($documento),
            ], 201);
        } catch (InvalidArgumentException $e) {
            $this->app->json(['ok' => false, 'message' => $e->getMessage()], $this->codigoHttp($e, 422));
        } catch (RuntimeException $e) {
            $this->app->json(['ok' => false, 'message' => $e->getMessage()], $this->codigoHttp($e, 500));
        } catch (Throwable) {
            $this->app->json(['ok' => false, 'message' => 'No se pudo almacenar el documento.'], 500);
        }
    }

    /**
     * [POST] Reemplaza el archivo físico y metadatos de un documento existente.
     */
    public function reemplazar(string $id): void
    {
        if (!ctype_digit($id) || (int) $id <= 0) {
            $this->app->json(['ok' => false, 'message' => 'Identificador de documento inválido.'], 400);
            return;
        }

        $datos = $this->app->request()->data->getData();
        $archivos = $this->app->request()->files->getData();
        $entradaArchivo = $archivos['archivo'] ?? null;

        if (!is_array($entradaArchivo)) {
            $this->app->json(['ok' => false, 'message' => 'Debe adjuntar el nuevo archivo en el campo "archivo".'], 422);
            return;
        }

        $nuevoTipoId = isset($datos['tipo_documento_id']) && is_numeric($datos['tipo_documento_id']) && (int) $datos['tipo_documento_id'] > 0
            ? (int) $datos['tipo_documento_id']
            : null;
        $empleadoId = isset($datos['empleado_id']) && is_numeric($datos['empleado_id']) && (int) $datos['empleado_id'] > 0
            ? (int) $datos['empleado_id']
            : null;
        $familiaId = isset($datos['familia_id']) && is_numeric($datos['familia_id']) && (int) $datos['familia_id'] > 0
            ? (int) $datos['familia_id']
            : null;

        try {
            $documento = $this->gestorDocumentos->reemplazar((int) $id, $entradaArchivo, $nuevoTipoId, $empleadoId, $familiaId);

            $this->app->json([
                'ok' => true,
                'message' => 'Documento reemplazado correctamente.',
                'data' => self::formatearDocumento($documento),
            ], 200);
        } catch (InvalidArgumentException $e) {
            $this->app->json(['ok' => false, 'message' => $e->getMessage()], $this->codigoHttp($e, 422));
        } catch (RuntimeException $e) {
            $this->app->json(['ok' => false, 'message' => $e->getMessage()], $this->codigoHttp($e, 500));
        } catch (Throwable) {
            $this->app->json(['ok' => false, 'message' => 'No se pudo reemplazar el documento.'], 500);
        }
    }

    /**
     * [GET] Sirve un documento protegido por sesión desde DocumentosInces.
     */
    public function descargar(string $id): void
    {
        $documento = ctype_digit($id) && (int) $id > 0 ? DocumentosModel::query()->find((int) $id) : null;
        if (!$documento instanceof DocumentosModel) {
            $this->app->halt(404, 'Documento no encontrado.');
            return;
        }

        try {
            $rutaAbsoluta = $this->gestorDocumentos->rutaAbsoluta($documento);
        } catch (InvalidArgumentException|RuntimeException $e) {
            $this->app->halt($this->codigoHttp($e, 404), $e->getMessage());
            return;
        }

        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($rutaAbsoluta);
        $disposicion = ($this->app->request()->query['descargar'] ?? '') === '1' ? 'attachment' : 'inline';
        $nombreSeguro = str_replace(["\r", "\n", '"'], '', (string) $documento->nombre_original) ?: basename($rutaAbsoluta);

        header('X-Content-Type-Options: nosniff');
        header('Content-Type: ' . (is_string($mime) && $mime !== '' ? $mime : 'application/octet-stream'));
        header('Content-Disposition: ' . $disposicion . '; filename="' . $nombreSeguro . '"');
        if (($tamano = filesize($rutaAbsoluta)) !== false) {
            header('Content-Length: ' . (string) $tamano);
        }

        readfile($rutaAbsoluta);
        if (empty(getenv('PHPUNIT_TEST')) && PHP_SAPI !== 'cli') {
            exit;
        }
    }

    private function codigoHttp(Throwable $e, int $defecto): int
    {
        $codigo = (int) $e->getCode();

        return $codigo >= 400 && $codigo < 600 ? $codigo : $defecto;
    }

    /**
     * @return array<string, mixed>
     */
    private static function formatearDocumento(DocumentosModel $documento): array
    {
        return [
            'documentos_id' => (int) $documento->documentos_id,
            'tipo_documento_id' => (int) $documento->tipo_documento_id,
            'tipo_documento' => (string) ($documento->tipoDocumento?->tipo_documentos ?? ''),
            'persona_id' => (int) $documento->persona_id,
            'codigo' => (string) ($documento->codigo ?? ''),
            'ruta' => (string) $documento->ruta,
            'estatus_documento' => (string) ($documento->estatus_documento ?? 'Vigente'),
            'nombre_original' => (string) $documento->nombre_original,
            'tamano' => (int) $documento->tamano,
            'formato' => (string) $documento->formato,
            'fecha_subida' => $documento->fecha_subida?->format('Y-m-d H:i:s'),
            'url_descarga' => '/documentos/' . (int) $documento->documentos_id,
        ];
    }
}
