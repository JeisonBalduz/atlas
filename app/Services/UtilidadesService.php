<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Database\Capsule\Manager as DB;
use InvalidArgumentException;

/**
 * Servicio para funciones utilitarias de PostgreSQL (`public.fn_calcular_edad`).
 */
final class UtilidadesService
{
    private const FN_CALCULAR_EDAD = 'public.fn_calcular_edad';

    /**
     * Ejecuta `public.fn_calcular_edad(fecha_nacimiento)` para una fecha individual
     * y retorna la edad formateada en texto (o `''` si la fecha es nula o vacía).
     */
    public function calcularEdad(?string $fechaNacimiento): string
    {
        $fecha = trim((string) $fechaNacimiento);
        if ($fecha === '') {
            return '';
        }

        $row = DB::selectOne(
            'SELECT ' . self::FN_CALCULAR_EDAD . '(?) AS edad',
            [$fecha]
        );

        return (string) ($row->edad ?? '');
    }

    /**
     * Retorna la expresión SQL parametrizada por columna para listados y consultas Eloquent
     * (`selectRaw` / subconsultas), evitando consultas N+1 y centralizando la función SQL.
     */
    public function expresionSql(string $columna = 'fecha_nacimiento', ?string $alias = null): string
    {
        $columnaLimpia = trim($columna);
        if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*(\.[a-zA-Z_][a-zA-Z0-9_]*)?$/', $columnaLimpia) !== 1) {
            throw new InvalidArgumentException('Identificador de columna inválido para calcular edad.');
        }

        $expresion = self::FN_CALCULAR_EDAD . '(' . $columnaLimpia . ')';
        if ($alias === null) {
            return $expresion;
        }

        $aliasLimpio = trim($alias);
        if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $aliasLimpio) !== 1) {
            throw new InvalidArgumentException('Alias SQL inválido para calcular edad.');
        }

        return $expresion . ' AS ' . $aliasLimpio;
    }
}
