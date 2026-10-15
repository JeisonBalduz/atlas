<?php

namespace App\Atlas\error;

use App\Atlas\interfaces\ErrorInterfaces;
class ErrorAccess implements ErrorInterfaces{

    public static array $arrayErrores = [
        'Acceso denegado: Usted no tiene rol asignado en el sistema.',
        'Acceso denegado: Su rol no cuenta con autorización para esta sección.',
        'Acceso denegado: Usted tiene rol asignado pero no posee permisos asignados.',
        'Acceso denegado: No tienes permisos para esta pantalla.'
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
