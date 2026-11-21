<?php

declare(strict_types=1);

namespace App\Controller;

use App\Services\CatalogosService;
use App\Services\ContextoSesion;
use App\Utils\Config;
use flight\Engine;

/**
 * Controlador para la vista de listado de familiares.
 */
class RegistrosFamiliaresController
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
     * [GET] Renderiza la vista principal de carga familiar.
     */
    public function render(): void
    {
        $this->app->render('registros_familiares', [
            'env' => $this->config->env(),
            'debug' => $this->config->isDebug(),
            'usuario' => (object) ContextoSesion::todo(),
            'parentescos' => $this->catalogos->tiposParentesco(),
        ]);
    }
}
