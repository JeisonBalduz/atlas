<?php

declare(strict_types=1);

namespace App\Controller;

use App\Utils\Config;
use flight\Engine;
use App\Services\ContextoSesion;
//Modelos
use App\Model\VwEmpleadoModel;
use App\Model\Catalogos\EstatusModel;
use App\Model\Catalogos\EstadoCivilModel;

class RegistrarEmpleadoController
{
    private Engine $app;

    private Config $config;

    public function __construct(Engine $app, Config $config)
    {
        $this->app = $app;
        $this->config = $config;
    }

    public function render(): void
    {
        // Lógica del controlador Employeeregister
        $this->app->render('registrar_empleado', [
            'env' => $this->config->env(),
            'debug' => $this->config->isDebug(),
            'usuario' =>(object) (ContextoSesion::todo() ?? []),
            // 'estatus' => EstatusModel::todoDatosEstatus(),
            // 'estado_civil' => EstadoCivilModel::todoDatosEstadoCivil()
        ]);
    }
}
