<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\ViewsFamiliarModel;
use Illuminate\Database\Capsule\Manager as Capsule;

class FamiliaresController
{
    public function index(){
        // 1. Usando Eloquent directamente sobre la vista de Postgres (sintaxis idéntica a Laravel)
        $familiares = ViewsFamiliarModel::all();

        $familiar = ViewsFamiliarModel::find(5);

        // Buscar por una condición exacta
        $nacionales = ViewsFamiliarModel::where('nacionalidad', 'V')->get();

        // Múltiples condiciones (AND)
        $resultados = ViewsFamiliarModel::where('nacionalidad', 'V')
            ->where('estatus', 'activo')
            ->get();

        //Si solo necesitas un registro específico que cumpla una condición (en lugar de una lista):
        $familiar_cedula = ViewsFamiliarModel::where('cedula', '12345678')->first();
        

        //ordenar elemento
        $ordenados = ViewsFamiliarModel::orderBy('nombre', 'ASC')->get(); // o 'DESC'
        // 2. Si necesitas ejecutar una función almacenada de Postgres (plpgsql) de forma limpia:
        // $resultado = Capsule::select('SELECT * FROM tu_funcion_almacenada(?)', [$id]);

        //limitar la cantidad de registros (Paginación / Límite):
        $primerosCinco = ViewsFamiliarModel::take(5)->get();

        //Contar registros:
        $total = ViewsFamiliarModel::where('estatus', 'activo')->count();

        //Ejecutar consultas personalizadas o funciones de Postgres (plpgsql):
        $empleadoId = 12;
        $resultado = Capsule::select('SELECT * FROM calcular_bono(?)', [$empleadoId]);
        // Mostramos el resultado para probar
        echo "<pre>";
        print_r($familiar);
        echo "</pre>";
    }
}
