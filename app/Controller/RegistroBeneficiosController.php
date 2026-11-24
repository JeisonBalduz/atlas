<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\LimpiarInputs;
use App\Services\BeneficiosService;
use App\Services\CatalogosService;
use App\Utils\Config;
use flight\Engine;
use Throwable;

/**
 * Controlador de asignación y aprobación de beneficios socioeconómicos conectado a BD real.
 */
class RegistroBeneficiosController extends BaseController
{
    private BeneficiosService $beneficiosService;

    public function __construct(
        Engine $app,
        Config $config,
        private CatalogosService $catalogos,
        ?BeneficiosService $beneficiosService = null,
    ) {
        parent::__construct($app, $config);
        $this->beneficiosService = $beneficiosService ?? new BeneficiosService();
    }

    public function render(): void
    {
        $this->renderVista('registro_beneficios', [
            'personas' => [],
            'total_beneficiarios' => $this->catalogos->contarPersonasElegibles(),
            'tipos_beneficio' => $this->catalogos->tiposBeneficio(),
            'tipos_documentos' => $this->catalogos->tiposDocumentos(),
        ]);
    }

    public function buscarPersonasParaSelect(): void
    {
        $query = $this->app->request()->query;
        $busqueda = trim((string) ($query['q'] ?? ''));
        $limite = is_numeric($query['limit'] ?? null) ? (int) $query['limit'] : 30;

        $this->jsonOk($this->catalogos->buscarPersonasParaSelect($busqueda, $limite));
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

            $registro = $this->beneficiosService->registrar(
                (int) $input->get('persona_id'),
                (int) $input->get('beneficio_id'),
                trim((string) $input->get('fecha_solicitud')),
                $fechaAprobada
            );

            $this->jsonOk(['dtbeneficio_id' => (int) $registro->dtbeneficio_id], 'Beneficio asignado correctamente.', 201);
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
