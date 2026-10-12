<?php

namespace App\Atlas\Middlewares;

use Flight;

class AutenMiddleware
{
    private static function verificarEstadoSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario'])) {
                Flight::errorPage(
                    'Acceso No Autorizado',
                    'Debes iniciar sesión con tu cuenta para poder ver este contenido.',
                    401
                );
            exit;
        }
    }

    public function before()
    {
        $this->verificarEstadoSession();
    }

}
