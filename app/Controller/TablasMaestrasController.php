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
use App\Services\CatalogosService;
use App\Services\ContextoSesion;
use App\Interfaces\RenderInterfaces;
use App\Utils\Config;
use flight\Engine;
use Illuminate\Database\Eloquent\Model;
use Throwable;

/**
 * Controlador general para la vista y operaciones CRUD de los catálogos del sistema.
 */
class TablasMaestrasController implements RenderInterfaces
{
    /** @var array<string, array{modelo: class-string<Model>, campo: string, limite: int, tiene_activo: bool}> */
    private const CATALOGOS = [
        'estatus' => ['modelo' => EstatusModel::class, 'campo' => 'estatus', 'limite' => 50, 'tiene_activo' => true],
        'dependencias' => ['modelo' => DependenciasModel::class, 'campo' => 'dependencia', 'limite' => 100, 'tiene_activo' => true],
        'departamentos' => ['modelo' => DepartamentosModel::class, 'campo' => 'departamento', 'limite' => 100, 'tiene_activo' => true],
        'cargo' => ['modelo' => CargoModel::class, 'campo' => 'cargo', 'limite' => 50, 'tiene_activo' => true],
        'nivel_academicos' => ['modelo' => NivelAcademicosModel::class, 'campo' => 'nivel_academico', 'limite' => 100, 'tiene_activo' => false],
        'carrera' => ['modelo' => CarreraModel::class, 'campo' => 'carrera', 'limite' => 150, 'tiene_activo' => false],
        'tipo_documentos' => ['modelo' => TipoDocumentosModel::class, 'campo' => 'tipo_documentos', 'limite' => 150, 'tiene_activo' => false],
        'estado_civil' => ['modelo' => EstadoCivilModel::class, 'campo' => 'estado_civil', 'limite' => 50, 'tiene_activo' => false],
        'tipo_parentesco' => ['modelo' => TipoParentescoModel::class, 'campo' => 'parentesco', 'limite' => 50, 'tiene_activo' => false],
    ];

    /**
     * @param Engine<object> $app
     */
    public function __construct(
        private Engine $app,
        private Config $config,
        private CatalogosService $catalogos,
    ) {
    }

    /**
     * [GET] Renderiza la vista unificada de catálogos.
     */
    public function render(): void
    {
        $this->app->render('tabla_maestra', [
            'env' => $this->config->env(),
            'debug' => $this->config->isDebug(),
            'usuario' => (object) ContextoSesion::todo(),
            'estados' => $this->catalogos->estados(),
            'tipos_documentos' => $this->catalogos->tiposDocumentos(),
        ]);
    }

    /**
     * [GET] Lista los registros de un catálogo permitido.
     */
    public function index(string $tipo): void
    {
        $definicion = self::CATALOGOS[$tipo] ?? null;
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

        $this->app->json([
            'ok' => true,
            'catalogo' => $tipo,
            'data' => $query->get()->map(static fn (Model $m): array => $m->toArray())->values()->all(),
        ], 200);
    }

    /**
     * [POST] Crea un registro en el catálogo indicado usando LimpiarInputs.
     */
    public function store(string $tipo): void
    {
        $definicion = self::CATALOGOS[$tipo] ?? null;
        if ($definicion === null) {
            $this->app->json(['ok' => false, 'message' => 'Catálogo no reconocido.'], 404);
            return;
        }

        $input = new LimpiarInputs();
        $campo = $definicion['campo'];
        $valor = substr((string) ($input->get($campo) ?? $input->get('nombre', '')), 0, $definicion['limite']);

        if ($valor === '') {
            $this->app->json(['ok' => false, 'message' => "El campo '{$campo}' es obligatorio."], 422);
            return;
        }

        $payload = [$campo => $valor];

        if ($definicion['tiene_activo']) {
            $activoRaw = $input->get('activo');
            $payload['activo'] = $activoRaw !== null ? (bool) $activoRaw : true;
        }

        if ($tipo === 'dependencias') {
            $errorDep = $input->validar(['codigo', 'estado_id']);
            if ($errorDep !== null) {
                $this->app->json(['ok' => false, 'message' => $errorDep], 422);
                return;
            }
            $payload['codigo'] = substr((string) $input->get('codigo'), 0, 20);
            $payload['estado_id'] = (int) $input->get('estado_id');
        }

        if ($tipo === 'nivel_academicos') {
            if ($input->validar(['jerarquia']) !== null) {
                $this->app->json(['ok' => false, 'message' => 'Para nivel académico la jerarquía numérica es obligatoria.'], 422);
                return;
            }
            $payload['jerarquia'] = (int) $input->get('jerarquia');
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
     * [PUT] Actualiza un registro del catálogo indicado usando LimpiarInputs.
     */
    public function update(string $tipo, string $id): void
    {
        $definicion = self::CATALOGOS[$tipo] ?? null;
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

        $input = new LimpiarInputs();
        $campo = $definicion['campo'];
        $valor = substr((string) ($input->get($campo) ?? $input->get('nombre', (string) $registro->getAttribute($campo))), 0, $definicion['limite']);

        if ($valor === '') {
            $this->app->json(['ok' => false, 'message' => "El campo '{$campo}' es obligatorio."], 422);
            return;
        }

        $payload = [$campo => $valor];

        if ($definicion['tiene_activo'] && $input->get('activo') !== null) {
            $payload['activo'] = filter_var($input->get('activo'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (bool) $input->get('activo');
        }

        if ($tipo === 'dependencias') {
            if ($input->get('codigo') !== null && (string) $input->get('codigo') !== '') {
                $payload['codigo'] = substr((string) $input->get('codigo'), 0, 20);
            }
            if ($input->get('estado_id') !== null && is_numeric($input->get('estado_id'))) {
                $payload['estado_id'] = (int) $input->get('estado_id');
            }
        }

        if ($tipo === 'nivel_academicos' && $input->get('jerarquia') !== null && is_numeric($input->get('jerarquia'))) {
            $payload['jerarquia'] = (int) $input->get('jerarquia');
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
     * [DELETE] Desactiva o elimina un registro del catálogo.
     */
    public function destroy(string $tipo, string $id): void
    {
        $definicion = self::CATALOGOS[$tipo] ?? null;
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
}
