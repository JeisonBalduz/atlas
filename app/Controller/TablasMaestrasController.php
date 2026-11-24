<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\LimpiarInputs;
use App\Model\Catalogos\CargoModel;
use App\Model\Catalogos\CarreraModel;
use App\Model\Catalogos\DepartamentosModel;
use App\Model\Catalogos\DependenciasModel;
use App\Model\Catalogos\EstadoCivilModel;
use App\Model\Catalogos\EstatusModel;
use App\Model\Catalogos\NivelAcademicosModel;
use App\Model\Catalogos\TipoDocumentosModel;
use App\Model\Catalogos\TipoParentescoModel;
use App\Model\Ubicacion\EstadosModel;
use App\Model\Ubicacion\MunicipiosModel;
use App\Model\Ubicacion\ParroquiasModel;
use App\Services\CatalogosService;
use App\Utils\Config;
use flight\Engine;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Throwable;

/**
 * Controlador general para la vista y operaciones CRUD de las Tablas Maestras del sistema.
 * Muestra registros activos e inactivos (`true` y `false`) en los 9 catálogos configurables.
 */
class TablasMaestrasController extends BaseController
{
    /** @var array<string, array{modelo: class-string<Model>, campo: string, limite: int, tiene_activo: bool}> */
    private const CATALOGOS = [
        'estatus' => ['modelo' => EstatusModel::class, 'campo' => 'estatus', 'limite' => 50, 'tiene_activo' => true],
        'dependencias' => ['modelo' => DependenciasModel::class, 'campo' => 'dependencia', 'limite' => 100, 'tiene_activo' => true],
        'departamentos' => ['modelo' => DepartamentosModel::class, 'campo' => 'departamento', 'limite' => 100, 'tiene_activo' => true],
        'cargo' => ['modelo' => CargoModel::class, 'campo' => 'cargo', 'limite' => 50, 'tiene_activo' => true],
        'nivel_academicos' => ['modelo' => NivelAcademicosModel::class, 'campo' => 'nivel_academico', 'limite' => 100, 'tiene_activo' => true],
        'carrera' => ['modelo' => CarreraModel::class, 'campo' => 'carrera', 'limite' => 150, 'tiene_activo' => true],
        'estados' => ['modelo' => EstadosModel::class, 'campo' => 'estado', 'limite' => 100, 'tiene_activo' => true],
        'municipios' => ['modelo' => MunicipiosModel::class, 'campo' => 'municipio', 'limite' => 100, 'tiene_activo' => true],
        'parroquias' => ['modelo' => ParroquiasModel::class, 'campo' => 'parroquia', 'limite' => 100, 'tiene_activo' => true],
        'tipo_documentos' => ['modelo' => TipoDocumentosModel::class, 'campo' => 'tipo_documentos', 'limite' => 150, 'tiene_activo' => false],
        'estado_civil' => ['modelo' => EstadoCivilModel::class, 'campo' => 'estado_civil', 'limite' => 50, 'tiene_activo' => false],
        'tipo_parentesco' => ['modelo' => TipoParentescoModel::class, 'campo' => 'parentesco', 'limite' => 50, 'tiene_activo' => false],
    ];

    /** @var array<string, list<string>> */
    private const RELACIONES = [
        'dependencias' => ['estado'],
        'municipios' => ['estado'],
        'parroquias' => ['municipio.estado'],
    ];

    public function __construct(
        Engine $app,
        Config $config,
        private CatalogosService $catalogos,
    ) {
        parent::__construct($app, $config);
    }

    public function render(): void
    {
        $this->renderVista('tabla_maestra', [
            'estados' => $this->catalogos->estados(false),
            'tipos_documentos' => $this->catalogos->tiposDocumentos(),
        ]);
    }

    public function index(string $tipo): void
    {
        $definicion = self::CATALOGOS[$tipo] ?? null;
        if ($definicion === null) {
            $this->jsonError('Catálogo no reconocido.', 404);
            return;
        }

        $input = $this->limpiarQuery();

        /** @var class-string<Model> $modelo */
        $modelo = $definicion['modelo'];
        $query = $modelo::query();

        $relaciones = self::RELACIONES[$tipo] ?? [];
        if ($relaciones !== []) {
            $query->with($relaciones);
        }

        $this->aplicarFiltros($query, $tipo, $definicion, $input);

        if ($tipo === 'nivel_academicos') {
            $query->orderBy('jerarquia');
        }

        $query->orderBy($definicion['campo']);

        $this->app->json([
            'ok' => true,
            'catalogo' => $tipo,
            'data' => $query->get()->map(static fn (Model $m): array => $m->toArray())->values()->all(),
        ], 200);
    }

    public function store(string $tipo): void
    {
        $definicion = self::CATALOGOS[$tipo] ?? null;
        if ($definicion === null) {
            $this->jsonError('Catálogo no reconocido.', 404);
            return;
        }

        $input = new LimpiarInputs();
        $resultado = $this->construirPayload($tipo, $definicion, $input, null);
        if (!isset($resultado['payload'])) {
            $this->jsonError($resultado['error'] ?? 'Datos inválidos.', 422);
            return;
        }

        try {
            /** @var class-string<Model> $modelo */
            $modelo = $definicion['modelo'];
            $creado = $modelo::query()->create($resultado['payload']);
            $this->cargarRelaciones($tipo, $creado);

            $this->jsonOk($creado->toArray(), 'Registro de catálogo creado correctamente.', 201);
        } catch (Throwable) {
            $this->jsonError('No se pudo crear el registro en el catálogo.', 500);
        }
    }

    public function update(string $tipo, string $id): void
    {
        $definicion = self::CATALOGOS[$tipo] ?? null;
        if ($definicion === null) {
            $this->jsonError('Catálogo no reconocido.', 404);
            return;
        }

        if (!ctype_digit($id) || (int) $id <= 0) {
            $this->jsonError('Identificador inválido.', 400);
            return;
        }

        /** @var class-string<Model> $modelo */
        $modelo = $definicion['modelo'];
        $registro = $modelo::query()->find((int) $id);
        if (!$registro instanceof Model) {
            $this->jsonError('Registro de catálogo no encontrado.', 404);
            return;
        }

        $input = new LimpiarInputs();
        $resultado = $this->construirPayload($tipo, $definicion, $input, $registro);
        if (!isset($resultado['payload'])) {
            $this->jsonError($resultado['error'] ?? 'Datos inválidos.', 422);
            return;
        }

        try {
            $registro->fill($resultado['payload']);
            $registro->save();
            $this->cargarRelaciones($tipo, $registro);

            $this->jsonOk($registro->toArray(), 'Registro de catálogo actualizado correctamente.');
        } catch (Throwable) {
            $this->jsonError('No se pudo actualizar el registro del catálogo.', 500);
        }
    }

    public function destroy(string $tipo, string $id): void
    {
        $definicion = self::CATALOGOS[$tipo] ?? null;
        if ($definicion === null) {
            $this->jsonError('Catálogo no reconocido.', 404);
            return;
        }

        if (!ctype_digit($id) || (int) $id <= 0) {
            $this->jsonError('Identificador inválido.', 400);
            return;
        }

        /** @var class-string<Model> $modelo */
        $modelo = $definicion['modelo'];
        $registro = $modelo::query()->find((int) $id);
        if (!$registro instanceof Model) {
            $this->jsonError('Registro de catálogo no encontrado.', 404);
            return;
        }

        try {
            if ($definicion['tiene_activo']) {
                $registro->setAttribute('activo', false);
                $registro->save();

                $this->jsonOk($registro->toArray(), 'Registro de catálogo desactivado correctamente.');
                return;
            }

            $registro->delete();
            $this->jsonOk(['id' => (int) $id], 'Registro de catálogo eliminado correctamente.');
        } catch (Throwable) {
            $this->jsonError('No se puede eliminar este registro porque está asociado a otros datos.', 409);
        }
    }

    private function limpiarQuery(): LimpiarInputs
    {
        $postOriginal = $_POST;
        $contentTypeOriginal = $_SERVER['CONTENT_TYPE'] ?? null;
        $_POST = $this->app->request()->query->getData();
        $_SERVER['CONTENT_TYPE'] = '';

        try {
            return new LimpiarInputs();
        } finally {
            $_POST = $postOriginal;
            if ($contentTypeOriginal === null) {
                unset($_SERVER['CONTENT_TYPE']);
            } else {
                $_SERVER['CONTENT_TYPE'] = $contentTypeOriginal;
            }
        }
    }

    /**
     * @param Builder<Model> $query
     * @param array{modelo: class-string<Model>, campo: string, limite: int, tiene_activo: bool} $definicion
     */
    private function aplicarFiltros(Builder $query, string $tipo, array $definicion, LimpiarInputs $input): void
    {
        if ($definicion['tiene_activo']) {
            $activo = $input->get('activo');
            if ($activo === '1' || $activo === 1 || $activo === true || $activo === 'true') {
                $query->where('activo', true);
            } elseif ($activo === '0' || $activo === 0 || $activo === false || $activo === 'false') {
                $query->where('activo', false);
            }
        }

        $estadoId = self::enteroPositivo($input->get('estado_id'));
        $municipioId = self::enteroPositivo($input->get('municipio_id'));

        if (($tipo === 'dependencias' || $tipo === 'municipios') && $estadoId !== null) {
            $query->where('estado_id', $estadoId);
        }

        if ($tipo === 'parroquias') {
            if ($municipioId !== null) {
                $query->where('municipio_id', $municipioId);
            } elseif ($estadoId !== null) {
                $query->whereHas('municipio', static function (Builder $sub) use ($estadoId): void {
                    $sub->where('estado_id', $estadoId);
                });
            }
        }

        $busqueda = trim((string) ($input->get('buscar') ?? $input->get('q', '')));
        if ($busqueda !== '') {
            $op = $query->getConnection()->getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';
            $patron = '%' . $busqueda . '%';
            $campo = $definicion['campo'];

            $query->where(static function (Builder $q) use ($tipo, $campo, $op, $patron): void {
                $q->where($campo, $op, $patron);
                if ($tipo === 'dependencias') {
                    $q->orWhere('codigo', $op, $patron);
                }
            });
        }
    }

    /**
     * @param array{modelo: class-string<Model>, campo: string, limite: int, tiene_activo: bool} $definicion
     * @return array{payload: array<string, mixed>}|array{error: string}
     */
    private function construirPayload(string $tipo, array $definicion, LimpiarInputs $input, ?Model $registro): array
    {
        $esCreacion = $registro === null;
        $campo = $definicion['campo'];
        $porDefecto = $esCreacion ? '' : (string) $registro->getAttribute($campo);
        $valor = substr((string) ($input->get($campo) ?? $input->get('nombre', $porDefecto)), 0, $definicion['limite']);

        if ($valor === '') {
            return ['error' => "El campo '{$campo}' es obligatorio."];
        }

        $payload = [$campo => $valor];

        if ($definicion['tiene_activo']) {
            $activoRaw = $input->get('activo');
            if ($activoRaw !== null) {
                $payload['activo'] = filter_var($activoRaw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (bool) $activoRaw;
            } elseif ($esCreacion) {
                $payload['activo'] = true;
            }
        }

        if ($tipo === 'dependencias') {
            if ($esCreacion) {
                $errorDep = $input->validar(['codigo', 'estado_id']);
                if ($errorDep !== null) {
                    return ['error' => $errorDep];
                }
            }
            $codigo = $input->get('codigo');
            if ($codigo !== null) {
                $codigoLimpio = substr((string) $codigo, 0, 20);
                if ($codigoLimpio === '') {
                    return ['error' => "El campo 'codigo' es obligatorio."];
                }
                $payload['codigo'] = $codigoLimpio;
            }
            $estadoId = self::enteroPositivo($input->get('estado_id'));
            if ($esCreacion || $input->get('estado_id') !== null) {
                if ($estadoId === null || !EstadosModel::query()->whereKey($estadoId)->exists()) {
                    return ['error' => 'El estado seleccionado no es válido.'];
                }
                $payload['estado_id'] = $estadoId;
            }
        }

        if ($tipo === 'municipios') {
            $estadoId = self::enteroPositivo($input->get('estado_id'));
            if ($esCreacion || $input->get('estado_id') !== null) {
                if ($estadoId === null || !EstadosModel::query()->whereKey($estadoId)->exists()) {
                    return ['error' => 'Debes seleccionar un estado válido para el municipio.'];
                }
                $payload['estado_id'] = $estadoId;
            }
        }

        if ($tipo === 'parroquias') {
            $municipioId = self::enteroPositivo($input->get('municipio_id'));
            if ($esCreacion || $input->get('municipio_id') !== null) {
                if ($municipioId === null || !MunicipiosModel::query()->whereKey($municipioId)->exists()) {
                    return ['error' => 'Debes seleccionar un municipio válido para la parroquia.'];
                }
                $payload['municipio_id'] = $municipioId;
            }
        }

        if ($tipo === 'nivel_academicos') {
            $jerarquiaRaw = $input->get('jerarquia');
            if ($esCreacion && ($jerarquiaRaw === null || !is_numeric($jerarquiaRaw))) {
                return ['error' => 'Para nivel académico la jerarquía numérica es obligatoria.'];
            }
            if ($jerarquiaRaw !== null && is_numeric($jerarquiaRaw)) {
                $payload['jerarquia'] = (int) $jerarquiaRaw;
            }
        }

        return ['payload' => $payload];
    }

    private function cargarRelaciones(string $tipo, Model $registro): void
    {
        $relaciones = self::RELACIONES[$tipo] ?? [];
        if ($relaciones !== []) {
            $registro->load($relaciones);
        }
    }

    private static function enteroPositivo(mixed $valor): ?int
    {
        if (is_int($valor)) {
            return $valor > 0 ? $valor : null;
        }
        if (is_string($valor) && ctype_digit($valor) && (int) $valor > 0) {
            return (int) $valor;
        }

        return null;
    }
}
