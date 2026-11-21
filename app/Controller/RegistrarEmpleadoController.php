<?php

declare(strict_types=1);

namespace App\Controller;

use App\Services\CatalogosService;
use App\Services\ContextoSesion;
use App\Utils\Config;
use flight\Engine;

/**
 * Controlador para la vista de registro de empleado.
 */
class RegistrarEmpleadoController
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
     * [GET] Renderiza el formulario de registro de personal con catálogos hidratados desde Eloquent.
     */
    public function render(): void
    {
        $tiposDiscapacidad = ['0' => 'Ninguna'] + $this->catalogos->tiposDiscapacidad();

        $this->app->render('registrar_empleado', [
            'env' => $this->config->env(),
            'debug' => $this->config->isDebug(),
            'usuario' => (object) ContextoSesion::todo(),
            'estados_civiles' => $this->catalogos->estadosCiviles(),
            'tipos_discapacidad' => $tiposDiscapacidad,
            'estados' => $this->catalogos->estados(),
            'municipios' => [],
            'parroquias' => [],
            'estatus' => $this->catalogos->estatus(true),
            'cargos' => $this->catalogos->cargos(true),
            'departamentos' => $this->catalogos->departamentos(true),
            'dependencias' => $this->catalogos->dependencias(true),
            'niveles_academicos' => $this->catalogos->nivelesAcademicos(),
            'carreras' => $this->catalogos->carreras(),
            'tipos_documentos' => $this->catalogos->tiposDocumentos(),
            'generos' => $this->catalogos->generos(),
        ]);
    }
}
