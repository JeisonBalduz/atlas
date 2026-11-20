<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Database\Capsule\Manager as DB;

/**
 * Servicio para funciones utilitarias de PostgreSQL (`public.fn_calcular_edad`).
 */
final class UtilidadesService
{
    /**
     * Ejecuta `public.fn_calcular_edad(fecha_nacimiento)` y retorna la edad formateada en texto.
     */
    public function calcularEdad(string $fechaNacimiento): string
    {
        $row = DB::selectOne(
            'SELECT public.fn_calcular_edad(?) AS edad',
            [$fechaNacimiento]
        );

        return (string) ($row->edad ?? '');
    }
}
