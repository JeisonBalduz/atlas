<?php

declare(strict_types=1);

namespace App\Controller;

use App\Utils\Config;
use App\Model\UserModel;
use App\Http\LimpiezaInputs;
use App\Utils\PasswordManager;
use Illuminate\Database\Capsule\Manager as Capsule;
use flight\Engine;

class LoginController
{

    private LimpiezaInputs $input;
    private UserModel $userModel;

   // Las propiedades $app y $config se declaran y asignan directamente en los argumentos
    public function __construct(
        private Engine $app, 
        private Config $config
    ) {
        // Solo inicializas la clase interna que no viene por inyección de dependencias
        $this->input = new LimpiezaInputs();
        $this->userModel = new UserModel();
    }

    public function render(): void
    {
        // Lógica del controlador Login
        $this->app->render('login', [
            'message' => 'Bienvenido a Atlas',
            'env' => $this->config->env(),
            'debug' => $this->config->isDebug(),
        ]);
    }

    public function authenticate(): void
    {
        // Validamos que los campos obligatorios no estén vacíos
        $errorValidacion = $this->input->validate(['usuario', 'clave']);

        if ($errorValidacion !== null) {
            $this->app->json([
                'ok' => false,
                'message' => $errorValidacion
            ], 400);
            return;
        }

        // Obtenemos los valores limpios
        $usuario = $this->input->get('usuario');
        $clave = $this->input->get('clave');

        // Buscar el usuario en la base de datos
        $user = $this->userModel->getUser($usuario);

        // Validamos si el usuario existe
        if (!$user) {
            $this->app->json([
                'ok' => false,
                'message' => 'El usuario ingresado no existe.'
            ], 404); // Not Found o 401 según prefieras
            return;
        } else {
            // Si el usuario existe, validamos la contraseña
            if (!PasswordManager::verify($clave, $user->contrasena)) {
                $this->app->json([
                    'ok' => false,
                    'message' => 'La contraseña es incorrecta.'
                ], 401); // Unauthorized
                return;
            }
        }

        // Si todo es correcto, login exitoso
        $this->app->json([
            'ok' => true,
            'message' => 'Login exitoso',
            'redirect' => '/Home' // Redirigir a la página de inicio
        ], 200);
    }
}
