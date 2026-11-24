<?php

declare(strict_types=1);

namespace App\Controller;

use App\Utils\Config;
use App\Model\UsuarioModel;
use App\Http\LimpiarInputs;
use App\Services\ManejadorClave;
use App\Services\AutentecionSesion;
use App\Interfaces\RenderInterfaces;
use flight\Engine;
use InvalidArgumentException;


final class SesionController implements RenderInterfaces
{

    private LimpiarInputs $input;
    private UsuarioModel $usuarioModel;

    // Las propiedades $app y $config se declaran y asignan directamente en los argumentos
    public function __construct(
        private Engine $app,
        private Config $config
    ) {
        // Solo inicializas la clase interna que no viene por inyección de dependencias
        $this->input = new LimpiarInputs();
        $this->usuarioModel = new UsuarioModel();
    }

    public function render(): void
    {
        // Lógica del controlador Login
        $this->app->render('autenticacion', [
            'message' => 'Bienvenido a Atlas',
            'env' => $this->config->env(),
            'debug' => $this->config->isDebug(),
        ]);
    }

    public function autentecacion(): void
    {
        // 1. Validamos que los campos obligatorios no estén vacíos
        $errorValidacion = $this->input->validar(['usuario', 'clave']);
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
        $vetificacionUsuario = $this->usuarioModel->existeUsuario($usuario);

        // Si no devuelve nada, el usuario no existe
        if (!$vetificacionUsuario) {
            $this->app->json([
                'ok' => false,
                'message' => 'El usuario ingresado no existe'
            ], 404);
            return;
        }

        $claveUsuario = (string) ($this->usuarioModel->obtenerClaveUsuario($usuario) ?? '');
        // 3. Validamos la contraseña usando el array plano obtenido
        if ($claveUsuario === '' || !ManejadorClave::verificar($clave, $claveUsuario)) {
            $this->app->json([
                'ok' => false,
                'message' => 'La contraseña es incorrecta'
            ], 401);
            return;
        }

        // Obtenemos el id del usuario 
        $usuario_id = $this->usuarioModel->obtenerUsuarioId($usuario);
    
        if (!$usuario_id) {
            $this->app->json([
                'ok' => false,
                'message' => 'Error interno al procesar la identidad del usuario'
            ], 500); // 500 Internal Server Error
            return;
        }

        // Con el id del usuario obtener todos los datos del usuario para iniciar sesión
        $usuarioDatos = $this->usuarioModel->obtenerTodoDatosUsuarioId($usuario_id);
        
        if (empty($usuarioDatos)) {
            $this->app->json([
                'ok' => false,
                'message' => 'No se pudieron recuperar los datos de perfil del usuario'
            ], 422); // 422 Unprocessable Entity
            return;
        }

        if (strtolower((string) ($usuarioDatos['estatus'] ?? '')) !== 'activo') {
            $this->app->json([
                'ok' => false,
                'message' => 'Esta cuenta de usuario se encuentra desactivada. Contacta al administrador.'
            ], 403);
            return;
        }
        // Gestión del inicio de sesión y manejo seguro del estado de la aplicación
        try {
            AutentecionSesion::autenticacion($usuarioDatos);

            $this->app->json([
                'ok' => true,
                'success' => true,
                'message' => '¡Inicio de sesión exitoso!',
                'redirect' => '/Inicio'
            ], 200);

        } catch (InvalidArgumentException $e) {
            $this->app->json([
                'ok' => false,
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function cerrarSesion(): void
    {
        AutentecionSesion::cerrarSesion();
    }

}
