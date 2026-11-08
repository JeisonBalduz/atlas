<?php

declare(strict_types=1);

namespace App\Controller;

use App\Utils\Config;
use App\Model\LoginModel;
use flight\Engine;

class LoginController
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
        // Lógica del controlador Login
        $this->app->render('login');
    }
}
