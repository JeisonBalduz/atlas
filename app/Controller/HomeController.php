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
    /** @var Engine<object> */
    private Engine $app;

    /** @var Config */
    private Config $config;

    /**
     * @param Engine<object> $app
     */
    public function __construct(Engine $app, Config $config)
    {
        $this->app = $app;
        $this->config = $config;
    }

    public function index(): void
    {
        $this->app->render('welcome', [
            'message' => 'You are gonna do great things!',
            'env' => $this->config->env(),
            'debug' => $this->config->isDebug(),
        ]);
    }

    public function  mostrarEmpleado(string $id): void
    {   
        // Si internamente necesitas usarlo como número para la base de datos, 
        // simplemente lo conviertes (cast) a entero dentro de la función:
        
        if(is_numeric($id)) {
             $idEntero = (int) $id;
            var_dump("Convertido a entero: " . $idEntero);
         
        } else {
           
            var_dump("no es entero" . $id);
        }
    }

    public function actualizar(): void
    {
        /** @var object $data */
        $data = $this->app->request()->data;

        $nombre = $data->nombre;
        $correo = $data->apellido;

        var_dump("Nombre enviado por fetch: " . $nombre);
        var_dump("Correo enviado por fetch: " . $correo);
    }
}