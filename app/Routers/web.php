<?php

use App\Atlas\Middlewares\AutenMiddleware;

// Vistas de login
Flight::route('GET /', function () {
    Flight::render('layouts/header'); // Cargar contenido de la cabecera

        Flight::render('login'); // Cargar contendio¿

    Flight::render('layouts/footer');// Cargar contenido inferiror
});

// Vistas de login
Flight::route('GET /login', function () {

    Flight::render('layouts/header'); //Cargar contenido de la cabecera

        Flight::render('login'); // Cargar contendio

    Flight::render('layouts/footer'); // Cargar contenido inferiror
});

// Vista de administador
Flight::route('GET /admin', function () {
    Flight::render('layouts/header'); //Cargar contenido de la cabecera

        Flight::render('admin/index', [
            'titulo' => 'Panel Principal',
            'usuario' => $_SESSION['usuario']['nombre'],
            'rol' => $_SESSION['usuario']['rol'],
        ]);

    Flight::render('layouts/footer'); // Cargar contenido inferiror
})->addMiddleware(AutenMiddleware::class);



