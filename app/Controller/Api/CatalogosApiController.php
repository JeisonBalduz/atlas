<?php

declare(strict_types=1);

namespace App\Controller\Api;

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
use flight\Engine;
use Illuminate\Database\Eloquent\Model;
use Throwable;

/**
 * Endpoints API CRUD genéricos sobre una lista blanca estricta de catálogos Eloquent.
 */
final class CatalogosApiController
{
    /**
     * @param Engine<object> $app
     */
    public function __construct(private Engine $app)
    {
    }

    /**
     * [GET] Lista todos los registros de un catálogo permitido.
     */
    public function index(string $tipo): void
    {
        $definicion = $this->resolverCatalogo($tipo);
        if ($definicion === null) {
            $this->app->json(['ok' => false, 'message' => 'Catálogo no reconocido.'], 404);
            return;
        }

        /** @var class-string<Model> $modelo */
        $modelo = $definicion['modelo'];
        $query = $modelo::query();

        if ($tipo === 'nivel_academicos') {
            $query->orderBy('jerarquia');
        }
        if ($tipo === 'dependencias') {
            $query->with('estado');
        }

        $query->orderBy($definicion['campo']);

        $registros = $query->get()->map(static fn (Model $m): array => $m->toArray())->values()->all();

        $this->app->json([
            'ok' => true,
            'catalogo' => $tipo,
            'data' => $registros,
        ], 200);
    }

    /**
     * [POST] Crea un nuevo registro en el catálogo indicado.
     */
    public function store(string $tipo): void
    {
        $definicion = $this->resolverCatalogo($tipo);
        if ($definicion === null) {
            $this->app->json(['ok' => false, 'message' => 'Catálogo no reconocido.'], 404);
            return;
        }

        $datos = $this->app->request()->data->getData();
        $campo = $definicion['campo'];
        $valor = trim((string) ($datos[$campo] ?? $datos['nombre'] ?? ''));

        if ($valor === '') {
            $this->app->json(['ok' => false, 'message' => "El campo '{$campo}' es obligatorio."], 422);
            return;
        }

        $payload = [$campo => $valor];

        if ($definicion['tiene_activo']) {
            $payload['activo'] = isset($datos['activo']) ? (bool) $datos['activo'] : true;
        }

        if ($tipo === 'dependencias') {
            $codigo = trim((string) ($datos['codigo'] ?? ''));
            $estadoId = isset($datos['estado_id']) && is_numeric($datos['estado_id']) ? (int) $datos['estado_id'] : 0;
            if ($codigo === '' || $estadoId <= 0 || !EstadosModel::query()->whereKey($estadoId)->exists()) {
                $this->app->json(['ok' => false, 'message' => 'Para dependencias los campos codigo y estado_id válidos son obligatorios.'], 422);
                return;
            }
            $payload['codigo'] = $codigo;
            $payload['estado_id'] = $estadoId;
        }

        if ($tipo === 'nivel_academicos') {
            if (!isset($datos['jerarquia']) || !is_numeric($datos['jerarquia'])) {
                $this->app->json(['ok' => false, 'message' => 'Para nivel académico la jerarquía numérica es obligatoria.'], 422);
                return;
            }
            $payload['jerarquia'] = (int) $datos['jerarquia'];
        }

        try {
            /** @var class-string<Model> $modelo */
            $modelo = $definicion['modelo'];
            $creado = $modelo::query()->create($payload);

            $this->app->json([
                'ok' => true,
                'message' => 'Registro de catálogo creado correctamente.',
                'data' => $creado->toArray(),
            ], 201);
        } catch (Throwable) {
            $this->app->json(['ok' => false, 'message' => 'No se pudo crear el registro en el catálogo.'], 500);
        }
    }

    /**
     * [PUT] Actualiza un registro del catálogo indicado.
     */
    public function update(string $tipo, string $id): void
    {
        $definicion = $this->resolverCatalogo($tipo);
        if ($definicion === null) {
            $this->app->json(['ok' => false, 'message' => 'Catálogo no reconocido.'], 404);
            return;
        }

        if (!ctype_digit($id) || (int) $id <= 0) {
            $this->app->json(['ok' => false, 'message' => 'Identificador inválido.'], 400);
            return;
        }

        /** @var class-string<Model> $modelo */
        $modelo = $definicion['modelo'];
        $registro = $modelo::query()->find((int) $id);
        if (!$registro instanceof Model) {
            $this->app->json(['ok' => false, 'message' => 'Registro de catálogo no encontrado.'], 404);
            return;
        }

        $datos = $this->app->request()->data->getData();
        $campo = $definicion['campo'];
        $valor = trim((string) ($datos[$campo] ?? $datos['nombre'] ?? (string) $registro->getAttribute($campo)));

        if ($valor === '') {
            $this->app->json(['ok' => false, 'message' => "El campo '{$campo}' es obligatorio."], 422);
            return;
        }

        $payload = [$campo => $valor];

        if ($definicion['tiene_activo'] && array_key_exists('activo', $datos)) {
            $payload['activo'] = filter_var($datos['activo'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (bool) $datos['activo'];
        }

        if ($tipo === 'dependencias') {
            if (isset($datos['codigo']) && trim((string) $datos['codigo']) !== '') {
                $payload['codigo'] = trim((string) $datos['codigo']);
            }
            if (isset($datos['estado_id']) && is_numeric($datos['estado_id'])) {
                $estadoId = (int) $datos['estado_id'];
                if ($estadoId <= 0 || !EstadosModel::query()->whereKey($estadoId)->exists()) {
                    $this->app->json(['ok' => false, 'message' => 'El estado_id indicado no existe.'], 422);
                    return;
                }
                $payload['estado_id'] = $estadoId;
            }
        }

        if ($tipo === 'nivel_academicos' && isset($datos['jerarquia']) && is_numeric($datos['jerarquia'])) {
            $payload['jerarquia'] = (int) $datos['jerarquia'];
        }

        try {
            $registro->fill($payload);
            $registro->save();

            $this->app->json([
                'ok' => true,
                'message' => 'Registro de catálogo actualizado correctamente.',
                'data' => $registro->toArray(),
            ], 200);
        } catch (Throwable) {
            $this->app->json(['ok' => false, 'message' => 'No se pudo actualizar el registro del catálogo.'], 500);
        }
    }

    /**
     * [DELETE] Desactiva (`activo = false`) si el catálogo soporta baja lógica, o elimina si no tiene dependencias.
     */
    public function destroy(string $tipo, string $id): void
    {
        $definicion = $this->resolverCatalogo($tipo);
        if ($definicion === null) {
            $this->app->json(['ok' => false, 'message' => 'Catálogo no reconocido.'], 404);
            return;
        }

        if (!ctype_digit($id) || (int) $id <= 0) {
            $this->app->json(['ok' => false, 'message' => 'Identificador inválido.'], 400);
            return;
        }

        /** @var class-string<Model> $modelo */
        $modelo = $definicion['modelo'];
        $registro = $modelo::query()->find((int) $id);
        if (!$registro instanceof Model) {
            $this->app->json(['ok' => false, 'message' => 'Registro de catálogo no encontrado.'], 404);
            return;
        }

        try {
            if ($definicion['tiene_activo']) {
                $registro->setAttribute('activo', false);
                $registro->save();

                $this->app->json([
                    'ok' => true,
                    'message' => 'Registro de catálogo desactivado correctamente.',
                    'data' => $registro->toArray(),
                ], 200);
                return;
            }

            $registro->delete();
            $this->app->json([
                'ok' => true,
                'message' => 'Registro de catálogo eliminado correctamente.',
                'data' => ['id' => (int) $id],
            ], 200);
        } catch (Throwable) {
            $this->app->json([
                'ok' => false,
                'message' => 'No se puede eliminar este registro porque está asociado a otros datos.',
            ], 409);
        }
    }

    /**
     * @return array{modelo: class-string<Model>, campo: string, tiene_activo: bool}|null
     */
    private function resolverCatalogo(string $tipo): ?array
    {
        $mapa = [
            'estatus' => ['modelo' => EstatusModel::class, 'campo' => 'estatus', 'tiene_activo' => true],
            'dependencias' => ['modelo' => DependenciasModel::class, 'campo' => 'dependencia', 'tiene_activo' => true],
            'departamentos' => ['modelo' => DepartamentosModel::class, 'campo' => 'departamento', 'tiene_activo' => true],
            'cargo' => ['modelo' => CargoModel::class, 'campo' => 'cargo', 'tiene_activo' => true],
            'nivel_academicos' => ['modelo' => NivelAcademicosModel::class, 'campo' => 'nivel_academico', 'tiene_activo' => false],
            'carrera' => ['modelo' => CarreraModel::class, 'campo' => 'carrera', 'tiene_activo' => false],
            'tipo_documentos' => ['modelo' => TipoDocumentosModel::class, 'campo' => 'tipo_documentos', 'tiene_activo' => false],
            'estado_civil' => ['modelo' => EstadoCivilModel::class, 'campo' => 'estado_civil', 'tiene_activo' => false],
            'tipo_parentesco' => ['modelo' => TipoParentescoModel::class, 'campo' => 'parentesco', 'tiene_activo' => false],
        ];

        return $mapa[$tipo] ?? null;
    }
}
