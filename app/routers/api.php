<?php

use App\Atlas\middlewares\AutenMiddleware;
use App\Atlas\controllers\AutenController;

Flight::route('POST /api/login', [AutenController::class, 'procesarLoginAjax']);
