<?php

use App\Atlas\middlewares\AutenMiddleware;

// Vistas de login
Flight::route('GET /', function () {
    view('login'); // Cargar contendio
});

// Vistas de login
Flight::route('GET /login', function () {
    view('login'); // Cargar contendio
});

// Vista de administador
Flight::route('GET /admin', function () {
    
        Flight::render('admin/index', [
            'titulo' => 'Panel Principal',
            'usuario' => $_SESSION['usuario']['nombre'],
            'rol' => $_SESSION['usuario']['rol'],
        ]);

})->addMiddleware(AutenMiddleware::class);



