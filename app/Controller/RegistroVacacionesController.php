<?php

declare(strict_types=1);

namespace App\Controller;

use App\Services\CatalogosService;
use App\Services\ContextoSesion;
use App\Utils\Config;
use flight\Engine;

/**
 * Controlador para la vista de registro y control de vacaciones.
 */
class RegistroVacacionesController
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
     * [GET] Renderiza la vista de solicitudes e historial de vacaciones.
     */
    public function render(): void
    {
        $this->app->render('registro_vacaciones', [
            'env' => $this->config->env(),
            'debug' => $this->config->isDebug(),
            'usuario' => (object) ContextoSesion::todo(),
            'empleados' => [],
            'total_empleados_activos' => $this->catalogos->contarEmpleadosActivos(),
        ]);
    }
}
