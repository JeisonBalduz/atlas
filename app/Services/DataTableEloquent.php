<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Manejador genérico Server-Side para DataTables 3.1.5 usando exclusivamente Eloquent ORM.
 */
final class DataTableEloquent
{
    private const MAX_POR_PAGINA = 100;

    /**
     * Procesa cualquier Builder Eloquent bajo el contrato `{ draw, recordsTotal, recordsFiltered, data }`.
     *
     * @template TModel of Model
     * @param Builder<TModel> $query Query base (puede incluir `with()` y `withCount()`).
     * @param array<string, mixed> $entrada Payload de DataTables (`$_POST`/`request()->data` o `query`).
     * @param array<string, string|callable|null> $columnasOrdenables Lista blanca: clave => columna o callback(Builder, dir).
     * @param list<string> $columnasBuscables Columnas directas o con relación en punto (ej. `'persona.primer_nombre'`, `'persona.cedula:int'`).
     * @param (callable(TModel): array<string, mixed>)|null $transformar Transformador opcional por fila.
     * @param (callable(Builder<TModel>, array<string, mixed>): void)|null $aplicarFiltros Filtros personalizados antes de contar filtrados.
     * @param array{0: string, 1?: string}|null $ordenPorDefecto Ej. `['empleado_id', 'desc']`.
     * @return array{draw: int, recordsTotal: int, recordsFiltered: int, data: list<array<string, mixed>>}
     */
    public static function procesar(
        Builder $query,
        array $entrada,
        array $columnasOrdenables = [],
        array $columnasBuscables = [],
        ?callable $transformar = null,
        ?callable $aplicarFiltros = null,
        ?array $ordenPorDefecto = null,
    ): array {
        $draw = self::extraerDraw($entrada);
        $start = isset($entrada['start']) && is_numeric($entrada['start']) ? max(0, (int) $entrada['start']) : 0;
        $lengthRaw = isset($entrada['length']) && is_numeric($entrada['length']) ? (int) $entrada['length'] : 10;
        $length = $lengthRaw <= 0 ? self::MAX_POR_PAGINA : min($lengthRaw, self::MAX_POR_PAGINA);

        $recordsTotal = self::contarRegistros($query);

        if ($aplicarFiltros !== null) {
            $aplicarFiltros($query, $entrada);
        }

        $busqueda = self::extraerBusqueda($entrada);
        if ($busqueda !== '' && $columnasBuscables !== []) {
            self::aplicarBusqueda($query, $busqueda, $columnasBuscables);
        }

        $recordsFiltered = self::contarRegistros($query);

        self::aplicarOrden($query, $entrada, $columnasOrdenables, $ordenPorDefecto);

        $coleccion = $query->skip($start)->take($length)->get();
        $data = [];

        foreach ($coleccion as $item) {
            $data[] = $transformar !== null ? $transformar($item) : $item->toArray();
        }

        return [
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ];
    }

    /**
     * Respuesta de error compatible con DataTables 3.1.5.
     *
     * @param array<string, mixed> $entrada
     * @return array{draw: int, recordsTotal: int, recordsFiltered: int, data: list<mixed>, error: string}
     */
    public static function error(array $entrada, string $mensaje): array
    {
        return [
            'draw' => self::extraerDraw($entrada),
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => [],
            'error' => $mensaje,
        ];
    }

    /**
     * @template TModel of Model
     * @param Builder<TModel> $query
     */
    private static function contarRegistros(Builder $query): int
    {
        $copia = clone $query;
        $copia->setEagerLoads([]);
        $base = $copia->getQuery();
        $base->orders = null;
        if ($base->groups === null) {
            $base->columns = null;
        }

        return $copia->count();
    }

    /**
     * @template TModel of Model
     * @param Builder<TModel> $query
     * @param list<string> $columnasBuscables
     */
    private static function aplicarBusqueda(Builder $query, string $busqueda, array $columnasBuscables): void
    {
        $terminos = preg_split('/\s+/u', $busqueda, -1, PREG_SPLIT_NO_EMPTY);
        if (!is_array($terminos) || $terminos === []) {
            return;
        }

        $terminos = array_slice($terminos, 0, 6);
        $op = $query->getConnection()->getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';

        // Agrupar columnas por relación para generar un único orWhereHas por relación
        $directas = [];
        $porRelacion = [];

        foreach ($columnasBuscables as $definicion) {
            $esEntero = str_ends_with($definicion, ':int');
            $ruta = $esEntero ? substr($definicion, 0, -4) : $definicion;
            $pos = strrpos($ruta, '.');

            if ($pos === false) {
                $directas[] = ['col' => $ruta, 'int' => $esEntero];
            } else {
                $relacion = substr($ruta, 0, $pos);
                $col = substr($ruta, $pos + 1);
                $porRelacion[$relacion][] = ['col' => $col, 'int' => $esEntero];
            }
        }

        foreach ($terminos as $termino) {
            $escapado = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $termino);
            $patron = "%{$escapado}%";

            $query->where(function (Builder $sub) use ($termino, $patron, $op, $directas, $porRelacion): void {
                $primero = true;

                foreach ($directas as $campo) {
                    if (!self::esIdentificadorSeguro($campo['col'])) {
                        continue;
                    }
                    self::agregarCondicionCampo($sub, $campo['col'], $campo['int'], $termino, $patron, $op, $primero);
                    $primero = false;
                }

                foreach ($porRelacion as $relacion => $campos) {
                    $metodo = $primero ? 'whereHas' : 'orWhereHas';
                    $sub->{$metodo}($relacion, static function (Builder $qRel) use ($campos, $termino, $patron, $op): void {
                        $qRel->where(static function (Builder $qInterno) use ($campos, $termino, $patron, $op): void {
                            $primerCampo = true;
                            foreach ($campos as $campo) {
                                if (!self::esIdentificadorSeguro($campo['col'])) {
                                    continue;
                                }
                                self::agregarCondicionCampo($qInterno, $campo['col'], $campo['int'], $termino, $patron, $op, $primerCampo);
                                $primerCampo = false;
                            }
                        });
                    });
                    $primero = false;
                }
            });
        }
    }

    /**
     * @param Builder<covariant Model> $query
     */
    private static function agregarCondicionCampo(
        Builder $query,
        string $columna,
        bool $esEntero,
        string $termino,
        string $patron,
        string $op,
        bool $comoAnd
    ): void {
        $metodoWhere = $comoAnd ? 'where' : 'orWhere';
        $metodoRaw = $comoAnd ? 'whereRaw' : 'orWhereRaw';

        if (!$esEntero) {
            $query->{$metodoWhere}($columna, $op, $patron);
            return;
        }

        $query->{$metodoRaw}("CAST({$columna} AS TEXT) {$op} ?", [$patron]);
        if (ctype_digit($termino)) {
            $query->orWhere($columna, (int) $termino);
        }
    }

    /**
     * @template TModel of Model
     * @param Builder<TModel> $query
     * @param array<string, mixed> $entrada
     * @param array<string, string|callable|null> $columnasOrdenables
     * @param array{0: string, 1?: string}|null $ordenPorDefecto
     */
    private static function aplicarOrden(
        Builder $query,
        array $entrada,
        array $columnasOrdenables,
        ?array $ordenPorDefecto
    ): void {
        $indice = 0;
        $dir = 'asc';
        $nombreColumna = null;

        if (isset($entrada['order']) && is_array($entrada['order']) && isset($entrada['order'][0]) && is_array($entrada['order'][0])) {
            $orden = $entrada['order'][0];
            if (isset($orden['column']) && is_numeric($orden['column'])) {
                $indice = max(0, (int) $orden['column']);
            }
            $dir = strtolower(trim((string) ($orden['dir'] ?? 'asc'))) === 'desc' ? 'desc' : 'asc';
            if (isset($orden['name']) && is_string($orden['name']) && $orden['name'] !== '') {
                $nombreColumna = trim($orden['name']);
            }
        }

        if (
            $nombreColumna === null
            && isset($entrada['columns'])
            && is_array($entrada['columns'])
            && isset($entrada['columns'][$indice])
            && is_array($entrada['columns'][$indice])
        ) {
            $colData = $entrada['columns'][$indice]['data'] ?? null;
            if (is_string($colData) && trim($colData) !== '') {
                $nombreColumna = trim($colData);
            }
        }

        // Si tampoco viene columns[i][data], resolver por posición en la lista blanca
        if ($nombreColumna === null) {
            $claves = array_keys($columnasOrdenables);
            $nombreColumna = $claves[$indice] ?? null;
        }

        $regla = $nombreColumna !== null && array_key_exists($nombreColumna, $columnasOrdenables)
            ? $columnasOrdenables[$nombreColumna]
            : null;

        if (is_callable($regla)) {
            $regla($query, $dir);
            return;
        }

        if (is_string($regla) && self::esIdentificadorSeguro($regla, true)) {
            $query->orderBy($regla, $dir);
            return;
        }

        if ($ordenPorDefecto !== null && self::esIdentificadorSeguro($ordenPorDefecto[0], true)) {
            $dirDef = strtolower($ordenPorDefecto[1] ?? 'desc') === 'asc' ? 'asc' : 'desc';
            $query->orderBy($ordenPorDefecto[0], $dirDef);
            return;
        }

        $pk = $query->getModel()->getKeyName();
        if (is_string($pk) && $pk !== '') {
            $query->orderBy($pk, 'desc');
        }
    }

    /**
     * @param array<string, mixed> $entrada
     */
    private static function extraerDraw(array $entrada): int
    {
        return isset($entrada['draw']) && is_numeric($entrada['draw']) ? max(0, (int) $entrada['draw']) : 1;
    }

    /**
     * @param array<string, mixed> $entrada
     */
    private static function extraerBusqueda(array $entrada): string
    {
        if (isset($entrada['search']) && is_array($entrada['search'])) {
            return trim((string) ($entrada['search']['value'] ?? ''));
        }
        if (isset($entrada['search']) && is_string($entrada['search'])) {
            return trim($entrada['search']);
        }

        return '';
    }

    private static function esIdentificadorSeguro(string $valor, bool $permitirTabla = false): bool
    {
        $patron = $permitirTabla
            ? '/^[a-zA-Z_][a-zA-Z0-9_]*(\.[a-zA-Z_][a-zA-Z0-9_]*)?$/'
            : '/^[a-zA-Z_][a-zA-Z0-9_]*$/';

        return preg_match($patron, $valor) === 1;
    }
}
