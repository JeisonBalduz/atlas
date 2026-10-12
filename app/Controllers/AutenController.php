<?php

namespace App\Atlas\Controllers;
use App\Atlas\Config\App;

class AutenController {

    public function procesarLoginAjax() {
        
        // Capturar el JSON enviado por Fetch de JavaScript
        $jsonCrudo = file_get_contents('php://input');
        $datos = json_decode($jsonCrudo, true);

        $cedula = trim($datos['cedula'] ?? '');
        $password = trim($datos['password'] ?? '');

        // Leer el archivo de usuarios de prueba guardado en storage
        $rutaJson = __DIR__ . '/../../storage/usuarios.json';
        if (!file_exists($rutaJson)) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'mensaje' => 'Archivo de usuarios no encontrado']);
            return;
        }

        $usuarios = json_decode(file_get_contents($rutaJson), true);
        $usuarioEncontrado = null;

        foreach ($usuarios as $user) {
            if ($user['cedula'] === $cedula && $user['password'] === $password) {
                $usuarioEncontrado = $user;
                break;
            }
        }
        $redirectUrl = match($usuarioEncontrado['rol']) {
            'admin' => App::URL_WEB . '/admin',
            'analista' => App::URL_WEB . '/analista',
            'comedor' => App::URL_WEB . '/comedor',
            default => App::URL_WEB . '/login'
        };
        if ($usuarioEncontrado) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['usuario'] = $usuarioEncontrado;




            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'success',
                'mensaje' => 'Inicio de sesión exitoso',
                'redirect' => $redirectUrl
            ]);
        } else {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'mensaje' => 'Credenciales incorrectas']);
        }
    }

    public function cerrarSesion() {
        session_destroy();
        header('Location: /pruebanueva2/login');
        exit;
    }
}