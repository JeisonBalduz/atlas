<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\LimpiarInputs;
use App\Services\BeneficiosService;
use App\Services\CatalogosService;
use App\Services\GestorDocumentos;
use App\Utils\Config;
use flight\Engine;
use Illuminate\Database\Capsule\Manager as DB;
use Throwable;

/**
 * Controlador de asignación y aprobación de beneficios socioeconómicos conectado a BD real y Contrato Colectivo INCES.
 */
class RegistroBeneficiosController extends BaseController
{
    private BeneficiosService $beneficiosService;
    private ?GestorDocumentos $gestorDocumentos;

    public function __construct(
        Engine $app,
        Config $config,
        private CatalogosService $catalogos,
        ?BeneficiosService $beneficiosService = null,
        ?GestorDocumentos $gestorDocumentos = null,
    ) {
        parent::__construct($app, $config);
        $this->beneficiosService = $beneficiosService ?? new BeneficiosService();
        $this->gestorDocumentos = $gestorDocumentos;
    }

    public function render(): void
    {
        $tiposDocumentos = $this->catalogos->tiposDocumentos();

        $this->renderVista('registro_beneficios', [
            'personas' => [],
            'total_beneficiarios' => $this->catalogos->contarPersonasElegibles(),
            'tipos_beneficio' => $this->catalogos->tiposBeneficio(),
            'tipos_documentos' => $tiposDocumentos,
            'reglas_beneficios' => $this->beneficiosService->obtenerReglasParaCliente($tiposDocumentos),
        ]);
    }

    public function buscarPersonasParaSelect(): void
    {
        $query = $this->app->request()->query;
        $busqueda = trim((string) ($query['q'] ?? ''));
        $limite = is_numeric($query['limit'] ?? null) ? (int) $query['limit'] : 30;

        $this->jsonOk($this->catalogos->buscarPersonasParaSelect($busqueda, $limite));
    }

    /**
     * [GET] Verifica si una persona ya posee documentos vigentes requeridos para un beneficio.
     */
    public function estadoDocumental(): void
    {
        $query = $this->app->request()->query;
        $personaId = is_numeric($query['persona_id'] ?? null) ? (int) $query['persona_id'] : 0;
        $beneficioId = is_numeric($query['beneficio_id'] ?? null) ? (int) $query['beneficio_id'] : 0;

        if ($personaId <= 0 || $beneficioId <= 0) {
            $this->jsonOk(['requiere_documento' => false, 'tiene_vigente' => true, 'documentos_vigentes' => []]);
            return;
        }

        try {
            $this->jsonOk($this->beneficiosService->estadoDocumentalPersonaBeneficio($personaId, $beneficioId));
        } catch (Throwable) {
            $this->jsonOk(['requiere_documento' => false, 'tiene_vigente' => false, 'documentos_vigentes' => []]);
        }
    }

    public function listar(): void
    {
        try {
            $this->jsonOk($this->beneficiosService->listar());
        } catch (Throwable) {
            $this->jsonError('No se pudieron consultar los beneficios asignados.', 500);
        }
    }

    public function store(): void
    {
        $input = new LimpiarInputs();
        $error = $input->validar(['persona_id', 'beneficio_id', 'fecha_solicitud']);
        if ($error !== null) {
            $this->jsonError($error, 422);
            return;
        }

        try {
            $aprobadaRaw = $input->get('fecha_aprobada');
            $fechaAprobada = is_string($aprobadaRaw) && trim($aprobadaRaw) !== '' ? trim($aprobadaRaw) : null;
            $tipoDocRaw = $input->get('tipo_documento_id');
            $tipoDocId = is_numeric($tipoDocRaw) && (int) $tipoDocRaw > 0 ? (int) $tipoDocRaw : null;

            $archivos = $this->app->request()->getUploadedFiles();
            /** @var array<string, mixed>|null $archivoSoporte */
            $archivoSoporte = $_FILES['archivo_soporte'] ?? ($archivos['archivo_soporte'] ?? null);

            $gestor = $this->gestorDocumentos ?? new GestorDocumentos(
                DB::connection(),
                dirname(__DIR__, 2) . '/app/storage/DocumentosInces'
            );

            $registro = $this->beneficiosService->registrar(
                (int) $input->get('persona_id'),
                (int) $input->get('beneficio_id'),
                trim((string) $input->get('fecha_solicitud')),
                $fechaAprobada,
                is_array($archivoSoporte) ? $archivoSoporte : null,
                $tipoDocId,
                $gestor
            );

            $this->jsonOk(['dtbeneficio_id' => (int) $registro->dtbeneficio_id], 'Beneficio asignado correctamente conforme al Contrato Colectivo.', 201);
        } catch (Throwable $e) {
            $codigo = (int) $e->getCode();
            $this->jsonError(
                $codigo >= 400 && $codigo < 500 ? $e->getMessage() : 'No se pudo asignar el beneficio.',
                $codigo >= 400 && $codigo < 600 ? $codigo : 500
            );
        }
    }

    public function aprobar(string $id): void
    {
        $dtId = $this->idPositivo($id, 'beneficio');
        if ($dtId === null) {
            return;
        }

        try {
            $registro = $this->beneficiosService->aprobar($dtId);
            $this->jsonOk([
                'dtbeneficio_id' => (int) $registro->dtbeneficio_id,
                'fecha_aprobada' => $registro->fecha_aprobada?->format('Y-m-d'),
            ], 'Beneficio aprobado correctamente.');
        } catch (Throwable $e) {
            $codigo = (int) $e->getCode();
            $this->jsonError($e->getMessage(), $codigo >= 400 && $codigo < 600 ? $codigo : 500);
        }
    }
}
