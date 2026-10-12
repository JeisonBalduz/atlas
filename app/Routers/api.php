<?php

use App\Atlas\Middlewares\AutenMiddleware;
use App\Atlas\Controllers\AutenController;

Flight::route('POST /api/login', [AutenController::class, 'procesarLoginAjax']);
