<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\Personas\EmpleadosModel;
use App\Services\CatalogosService;
use App\Services\ContextoSesion;
use App\Utils\Config;
use flight\Engine;

/**
 * Controlador para la vista de listado de empleados.
 */
class RegistrosEmpleadosController
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
     * [GET] Renderiza la vista principal del directorio de empleados.
     */
    public function render(): void
    {
        $total = EmpleadosModel::query()->count();
        $activos = EmpleadosModel::query()->where('estatus_actual', 1)->count();
        $inactivos = max(0, $total - $activos);

        $this->app->render('registros_empleados', [
            'env' => $this->config->env(),
            'debug' => $this->config->isDebug(),
            'usuario' => (object) ContextoSesion::todo(),
            'estatus_catalogo' => $this->catalogos->estatus(false),
            'dependencias' => $this->catalogos->dependencias(true),
            'departamentos' => $this->catalogos->departamentos(true),
            'tipos_documentos' => $this->catalogos->tiposDocumentos(),
            'resumen' => [
                'total' => $total,
                'activos' => $activos,
                'inactivos' => $inactivos,
            ],
        ]);
    }
}
