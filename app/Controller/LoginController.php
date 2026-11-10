<?php

declare(strict_types=1);

namespace App\Controller;

use App\Utils\Config;
use App\Model\UserModel;
use App\Http\CleanInputs;
use App\Services\PasswordManager;
use App\Services\AuthSession;
use flight\Engine;
use InvalidArgumentException;


class LoginController
{

    private CleanInputs $input;
    private UserModel $userModel;

    // Las propiedades $app y $config se declaran y asignan directamente en los argumentos
    public function __construct(
        private Engine $app,
        private Config $config
    ) {
        // Solo inicializas la clase interna que no viene por inyección de dependencias
        $this->input = new CleanInputs();
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
        // 1. Validamos que los campos obligatorios no estén vacíos
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

        // Verificamos que el usuario exista
        $user = $this->userModel->userExists($usuario);

        // Si no devuelve nada, el usuario no existe
        if (!$user) {
            $this->app->json([
                'ok' => false,
                'message' => 'El usuario ingresado no existe.'
            ], 404);
            return;
        }

        $possword = $this->userModel->getPasswordByUsername($usuario);
        // 3. Validamos la contraseña usando el array plano obtenido
        if (!PasswordManager::verify($clave, $possword)) {
            $this->app->json([
                'ok' => false,
                'message' => 'La contraseña es incorrecta.'
            ], 401);
            return;
        }

        // Obtenemos el id del usuario 
        $userId = $this->userModel->getIdByUsername($usuario);
    
        if (!$userId) {
            $this->app->json([
                'ok' => false,
                'message' => 'Error interno al procesar la identidad del usuario.'
            ], 500); // 500 Internal Server Error
            return;
        }

        // Con el id del usuario obtener todos los datos del usuario para iniciar sesión
        $userAllData = $this->userModel->getUserAllId($userId);
        
        if (empty($userAllData)) {
            $this->app->json([
                'ok' => false,
                'message' => 'No se pudieron recuperar los datos de perfil del usuario.'
            ], 422); // 422 Unprocessable Entity
            return;
        }
        // Gestión del inicio de sesión y manejo seguro del estado de la aplicación
        try {
            AuthSession::login($userAllData);

            $this->app->json([
                'ok' => true,
                'success' => true,
                'message' => '¡Inicio de sesión exitoso!',
                'redirect' => '/Home'
            ], 200);

        } catch (InvalidArgumentException $e) {
            $this->app->json([
                'ok' => false,
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

}
