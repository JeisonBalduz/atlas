<?php

declare(strict_types=1);

namespace App\Controller;

use App\Services\ContextoSesion;
use App\Utils\Config;
use flight\Engine;

/**
 * Controlador para la vista de listado de familiares.
 */
class RegistrosFamiliaresController
{
    public function __construct(
        private Engine $app,
        private Config $config
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
        ]);
    }
}
