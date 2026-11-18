<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Services\ContextoSesion;
use Flight;

class SesionMiddleware
{
    
    public function before(): void
    {
        // Validamos directamente con el helper estático de la sesión
        if (!ContextoSesion::comprobar()) {
            Flight::redirect('/autenticacion');
            exit;
        }
    }
}