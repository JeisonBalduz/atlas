<?php

declare(strict_types=1);

namespace App\Controller;

use App\Utils\Config;
use flight\Engine;

/**
 * Welcome page controller — canonical controller example for the skeleton.
 *
 * No Flight:: facade. No $_ENV. Inject what you need.
 */
class HomeController
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
        $this->app->render('welcome', [
            'message' => 'You are gonna do great things!',
            'env' => $this->config->env(),
            'debug' => $this->config->isDebug(),
        ]);
    }

    public function mostrarEmpleado(string $id): void
    {
        if (is_numeric($id)) {
            $idEntero = (int) $id;
            var_dump("Convertido a entero: " . $idEntero);
        } else {
            var_dump("no es entero: " . $id);
        }
    }

    public function actualizar(): void
    {
        // Forma 1 (Recomendada con PHPStan): acceso por array/Collection
        $data = $this->app->request()->data;

        $nombre = is_string($data['nombre'] ?? null) ? $data['nombre'] : '';
        $correo = is_string($data['apellido'] ?? null) ? $data['apellido'] : '';

        var_dump("Nombre enviado por fetch: " . $nombre);
        var_dump("Correo enviado por fetch: " . $correo);
    }
}