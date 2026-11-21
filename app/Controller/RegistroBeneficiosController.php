<?php

declare(strict_types=1);

namespace App\Controller;

use App\Services\CatalogosService;
use App\Services\ContextoSesion;
use App\Utils\Config;
use flight\Engine;

/**
 * Controlador para la vista de registro y seguimiento de beneficios.
 */
class RegistroBeneficiosController
{
    /**
     * @param Engine<object> $app
     */
    public function __construct(
        private Engine $app,
        private Config $config,
        private CatalogosService $catalogos,
    ) {
    }

    /**
     * [GET] Renderiza la vista de asignación de beneficios.
     */
    public function render(): void
    {
        $this->app->render('registro_beneficios', [
            'env' => $this->config->env(),
            'debug' => $this->config->isDebug(),
            'usuario' => (object) ContextoSesion::todo(),
            'personas' => [],
            'total_beneficiarios' => $this->catalogos->contarPersonasElegibles(),
            'tipos_beneficio' => $this->catalogos->tiposBeneficio(),
            'tipos_documentos' => $this->catalogos->tiposDocumentos(),
        ]);
    }

    /**
     * [GET] Búsqueda paginada de personas (titulares y familiares) para selects remotos.
     */
    public function buscarPersonasParaSelect(): void
    {
        $query = $this->app->request()->query;
        $busqueda = trim((string) ($query['q'] ?? ''));
        $limite = is_numeric($query['limit'] ?? null) ? (int) $query['limit'] : 30;

        $this->app->json([
            'ok' => true,
            'data' => $this->catalogos->buscarPersonasParaSelect($busqueda, $limite),
        ], 200);
    }
}
