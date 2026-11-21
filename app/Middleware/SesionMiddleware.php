<?php

declare(strict_types=1);

namespace App\Middleware;

use App\config\ClavesSesion;
use App\Services\ContextoSesion;
use flight\Engine;
use flight\Session;

class SesionMiddleware
{
    /** @var Engine<object> */
    private Engine $app;

    private ?Session $session;

    /**
     * @param Engine<object> $app
     */
    public function __construct(Engine $app, ?Session $session = null)
    {
        $this->app = $app;
        $this->session = $session;
    }

    /**
     * Verifica la sesión activa y responde con 401 JSON en rutas API/AJAX o redirecciona en web.
     *
     * @param array<string, mixed> $params
     */
    public function before(array $params = []): void
    {
        $autenticado = $this->session !== null
            ? $this->session->get(ClavesSesion::ESTA_AUTENTICADO) === true
            : ContextoSesion::comprobar();

        if ($autenticado) {
            return;
        }

        $request = $this->app->request();
        $esPeticionJson = $request->ajax
            || str_starts_with($request->url, '/api/')
            || str_contains(strtolower($request->accept), 'application/json');

        if ($esPeticionJson) {
            $this->app->jsonHalt([
                'ok' => false,
                'message' => 'Sesión no iniciada o expirada.',
            ], 401);
            return;
        }

        $this->app->redirect('/autenticacion');
        if (empty(getenv('PHPUNIT_TEST')) && PHP_SAPI !== 'cli') {
            exit;
        }
    }
}
