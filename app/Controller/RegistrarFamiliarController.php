<?php

declare(strict_types=1);

namespace App\Controller;

use App\Services\ContextoSesion;
use App\Utils\Config;
use flight\Engine;

/**
 * Controlador para la vista de registro de familiar.
 */
class RegistrarFamiliarController
{
    public function __construct(
        private Engine $app,
        private Config $config
    ) {
    }

    /**
     * [GET] Renderiza el formulario de registro de carga familiar.
     */
    public function render(): void
    {
        $this->app->render('registrar_familiar', [
            'env' => $this->config->env(),
            'debug' => $this->config->isDebug(),
            'usuario' => (object) ContextoSesion::todo(),
        ]);
    }
}
