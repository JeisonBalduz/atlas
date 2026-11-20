<?php

declare(strict_types=1);

namespace App\Controller;

use App\Services\ContextoSesion;
use App\Utils\Config;
use flight\Engine;

/**
 * Controlador para la vista de registro de ausencias justificadas (permisos y reposos).
 */
class RegistroAusenciaController
{
    public function __construct(
        private Engine $app,
        private Config $config
    ) {
    }

    /**
     * [GET] Renderiza la vista unificada de ausencias justificadas.
     */
    public function render(): void
    {
        $this->app->render('registro_ausencia', [
            'env' => $this->config->env(),
            'debug' => $this->config->isDebug(),
            'usuario' => (object) ContextoSesion::todo(),
        ]);
    }
}
