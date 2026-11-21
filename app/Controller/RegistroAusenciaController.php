<?php

declare(strict_types=1);

namespace App\Controller;

use App\Services\CatalogosService;
use App\Services\ContextoSesion;
use App\Interfaces\RenderInterfaces;
use App\Utils\Config;
use flight\Engine;

/**
 * Controlador para la vista de registro de ausencias justificadas (permisos y reposos).
 */
class RegistroAusenciaController implements RenderInterfaces
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
     * [GET] Renderiza la vista unificada de ausencias justificadas.
     */
    public function render(): void
    {
        $this->app->render('registro_ausencia', [
            'env' => $this->config->env(),
            'debug' => $this->config->isDebug(),
            'usuario' => (object) ContextoSesion::todo(),
            'empleados' => [],
            'tipos_permiso' => $this->catalogos->tiposPermiso(),
            'tipos_reposo' => $this->catalogos->tiposReposo(),
            'tipos_institucion' => $this->catalogos->tiposInstitucionEmisora(),
            'tipos_documentos' => $this->catalogos->tiposDocumentos(),
        ]);
    }
}
