<?php

declare(strict_types=1);

namespace App\Controller;

use App\Services\CatalogosService;
use App\Services\ContextoSesion;
use App\Utils\Config;
use flight\Engine;

/**
 * Controlador para la vista de registro de familiar.
 */
class RegistrarFamiliarController
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
     * [GET] Renderiza el formulario de registro de carga familiar.
     */
    public function render(): void
    {
        $empleadoPreseleccionado = $this->app->request()->query['empleado'] ?? '';
        $empleadoSeleccionado = is_numeric($empleadoPreseleccionado) && (int) $empleadoPreseleccionado > 0
            ? (string) (int) $empleadoPreseleccionado
            : '';

        $this->app->render('registrar_familiar', [
            'env' => $this->config->env(),
            'debug' => $this->config->isDebug(),
            'usuario' => (object) ContextoSesion::todo(),
            'empleados' => $this->catalogos->empleadosActivosParaSelect(),
            'empleado_seleccionado' => $empleadoSeleccionado,
            'estados_civiles' => $this->catalogos->estadosCiviles(),
            'parentescos' => $this->catalogos->tiposParentesco(),
            'tipos_documentos' => $this->catalogos->tiposDocumentos(),
            'generos' => $this->catalogos->generos(),
        ]);
    }
}
