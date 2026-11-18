<?php

declare(strict_types=1);

namespace App\Controller;

use App\Utils\Config;
use App\Model\EmployeeregisterModel;
use flight\Engine;
use App\Services\AuthContext;

class EmployeeRegisterController
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
            'usuario' =>(object) AuthContext::all()
        ]);
    }
}
