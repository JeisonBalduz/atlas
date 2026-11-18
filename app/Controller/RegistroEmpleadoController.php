<?php

declare(strict_types=1);

namespace App\Controller;

use App\Utils\Config;
use flight\Engine;
use App\Services\ContextoSesion;
//Modelos
use App\Model\VwEmpleadoModel;
use App\Model\EstatusModel;
use App\Model\EstadoCivilModel;

class RegistroEmpleadoController
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
        $this->app->render('employee_register', [
            'message' => 'Bienvenido a Atlas',
            'env' => $this->config->env(),
            'debug' => $this->config->isDebug(),
            'usuario' =>(object) ContextoSesion::todo(),
            'estatus' => (object)  EstatusModel::todoDatosEstatus(),
            'estado_civil' => (object) EstadoCivilModel::todoDatosEstadoCivil()
        ]);
    }
}
