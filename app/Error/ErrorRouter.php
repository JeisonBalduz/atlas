<?php

namespace App\Atlas\Error;

use App\Atlas\Interfaces\ErrorInterfaces;

class ErrorRouter implements ErrorInterfaces { 
    public static array $arrayErrores = [
        'ERROR: HTTP 404 - Página no encontrada.',
        'ERROR: HTTP 404 - Recurso no encontrado.',
        'ERROR: HTTP 500 -  El controlador o método no existe.'
    ];

     public static function imprimirError(int $indiceError, int $codigo): void  {
        http_response_code($codigo);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'mensaje' => self::$arrayErrores[$indiceError]
        ]);
        exit();
    }
}