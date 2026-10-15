<?php

use App\Atlas\controllers\RouterController;

// Usando la diagonal global \
Flight::set('flight.views.path', __DIR__ . '/../app/Views');

Flight::request()->url = RouterController::obtenerPathLimpio();

Flight::set('flight.base_url', '/atlas/public');

Flight::map('view', function($template, $data = []) {
    Flight::render($template, $data);
});