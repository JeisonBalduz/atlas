<?php

declare(strict_types=1);

namespace App\Controller;

use App\Services\ContextoSesion;
use App\Utils\Config;
use flight\Engine;

/**
 * Controlador para la vista de listado de empleados.
 */
class RegistrosEmpleadosController
{
    public function __construct(
        private Engine $app,
        private Config $config
    ) {
    }

    /**
     * [GET] Renderiza la vista principal del directorio de empleados.
     */
    public function render(): void
    {
        $this->app->render('registros_empleados', [
            'env' => $this->config->env(),
            'debug' => $this->config->isDebug(),
            'usuario' => (object) ContextoSesion::todo(),
        ]);
    }
}
