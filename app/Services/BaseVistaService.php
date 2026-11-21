<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Query\Builder;

/**
 * Clase base abstracta para los servicios de solo lectura sobre vistas `public.vw_*`
 * y funciones JSON de PostgreSQL.
 */
abstract class BaseVistaService
{
    /**
     * Aplica filtros enteros, exactos y de texto (`ilike`) sobre un Builder de vista.
     *
     * @param array<string, mixed> $filtros
     * @param list<string> $camposTexto
     * @param list<string> $camposExactos
     * @param list<string> $camposEnteros
     */
    protected function aplicarFiltros(
        Builder $query,
        array $filtros,
        array $camposTexto = [],
        array $camposExactos = [],
        array $camposEnteros = []
    ): void {
        foreach ($camposEnteros as $campo) {
            if (isset($filtros[$campo]) && $filtros[$campo] !== '') {
                $query->where($campo, (int) $filtros[$campo]);
            }
        }

        foreach ($camposExactos as $campo) {
            if (isset($filtros[$campo]) && $filtros[$campo] !== '') {
                $query->where($campo, '=', (string) $filtros[$campo]);
            }
        }

        foreach ($camposTexto as $campo) {
            if (!empty($filtros[$campo])) {
                $query->where($campo, 'ilike', '%' . (string) $filtros[$campo] . '%');
            }
        }
    }

    /**
     * Aplica ordenamiento seguro y paginación opcional (`limite`, `offset`).
     *
     * @param array<string, mixed> $filtros
     */
    protected function aplicarOrdenYLimites(
        Builder $query,
        array $filtros,
        string $ordenDefecto,
        string $direccionDefecto = 'asc'
    ): void {
        $ordenPor = !empty($filtros['orden_por']) ? (string) $filtros['orden_por'] : $ordenDefecto;
        $dirInput = strtolower((string) ($filtros['direccion'] ?? $direccionDefecto));
        $direccion = $dirInput === 'desc' || ($dirInput !== 'asc' && strtolower($direccionDefecto) === 'desc')
            ? 'desc'
            : 'asc';

        $query->orderBy($ordenPor, $direccion);

        if (isset($filtros['limite']) && (int) $filtros['limite'] > 0) {
            $query->limit((int) $filtros['limite']);
        }

        if (isset($filtros['offset']) && (int) $filtros['offset'] >= 0) {
            $query->offset((int) $filtros['offset']);
        }
    }

    /**
     * Ejecuta una función PL/pgSQL que retorna JSON y lo decodifica a arreglo PHP.
     *
     * @return array<int|string, mixed>
     */
    protected function ejecutarFuncionJson(string $funcionSql, int $id): array
    {
        $row = DB::selectOne("SELECT {$funcionSql}(?) AS resultado", [$id]);
        $raw = isset($row->resultado) && is_string($row->resultado) && $row->resultado !== ''
            ? $row->resultado
            : '{}';
        $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

        return is_array($decoded) ? $decoded : [];
    }
}
