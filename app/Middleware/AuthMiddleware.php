<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Services\AuthSession;
use Flight;

class AuthMiddleware
{
    public function before(): void
    {
        // Validamos directamente con el helper estático de la sesión
        if (!AuthSession::check()) {
            Flight::redirect('/login');
            exit;
        }
    }
}