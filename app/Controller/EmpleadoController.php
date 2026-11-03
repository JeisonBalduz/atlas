<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\ViwsEmpleadoModel;
use Flight;

class EmpleadoController
{
    public function index()
    {
        $id = 1000;
        // 1. Consultamos la vista de Postgres usando nuestro helper/repositorio
        $empleados = ViwsEmpleadoModel::obtenerPorId($id);

        // 2. Aquí puedes renderizar tu vista con BladeOne pasándole los datos
        // Ejemplo: echo Flight::view()->run('empleados.index', ['empleados' => $empleados]);
        
        // Prueba temporal para verificar en pantalla
        echo "<pre>";
        print_r($empleados);
        echo "</pre>";
    }
}