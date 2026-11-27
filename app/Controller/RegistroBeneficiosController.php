<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\LimpiarInputs;
use App\Model\Beneficios\DetalleBeneficioModel;
use App\Model\Beneficios\TipoBeneficiosModel;
use App\Model\Personas\EmpleadosModel;
use App\Model\Personas\FamiliaresModel;
use App\Services\BeneficiosService;
use App\Services\CatalogosService;
use App\Services\GestorDocumentos;
use App\Utils\Config;
use flight\Engine;
use Illuminate\Database\Capsule\Manager as DB;
use Throwable;

/**
 * Controlador de asignación y aprobación de beneficios socioeconómicos conectado a BD real y Contrato Colectivo INCES.
 */
class RegistroBeneficiosController extends BaseController
{
    private BeneficiosService $beneficiosService;
    private ?GestorDocumentos $gestorDocumentos;

    public function __construct(
        Engine $app,
        Config $config,
        private CatalogosService $catalogos,
        ?BeneficiosService $beneficiosService = null,
        ?GestorDocumentos $gestorDocumentos = null,
    ) {
        parent::__construct($app, $config);
        $this->beneficiosService = $beneficiosService ?? new BeneficiosService();
        $this->gestorDocumentos = $gestorDocumentos;
    }

    public function render(): void
    {
        $tiposDocumentos = $this->catalogos->tiposDocumentos();

        $this->renderVista('registro_beneficios', [
            'personas' => [],
            'total_beneficiarios' => $this->catalogos->contarPersonasElegibles(),
            'tipos_beneficio' => $this->catalogos->tiposBeneficio(),
            'tipos_documentos' => $tiposDocumentos,
            'reglas_beneficios' => $this->beneficiosService->obtenerReglasParaCliente($tiposDocumentos),
        ]);
    }

    public function buscarPersonasParaSelect(): void
    {
        $query = $this->app->request()->query;
        $busqueda = trim((string) ($query['q'] ?? ''));
        $limite = is_numeric($query['limit'] ?? null) ? (int) $query['limit'] : 30;

        $this->jsonOk($this->catalogos->buscarPersonasParaSelect($busqueda, $limite));
    }

    /**
     * [GET] Verifica si una persona ya posee documentos vigentes requeridos para un beneficio.
     */
    public function estadoDocumental(): void
    {
        $query = $this->app->request()->query;
        $personaId = is_numeric($query['persona_id'] ?? null) ? (int) $query['persona_id'] : 0;
        $beneficioId = is_numeric($query['beneficio_id'] ?? null) ? (int) $query['beneficio_id'] : 0;

        if ($personaId <= 0 || $beneficioId <= 0) {
            $this->jsonOk(['requiere_documento' => false, 'tiene_vigente' => true, 'documentos_vigentes' => []]);
            return;
        }

        try {
            $this->jsonOk($this->beneficiosService->estadoDocumentalPersonaBeneficio($personaId, $beneficioId));
        } catch (Throwable) {
            $this->jsonOk(['requiere_documento' => false, 'tiene_vigente' => false, 'documentos_vigentes' => []]);
        }
    }

    public function listar(): void
    {
        try {
            $this->jsonOk($this->beneficiosService->listar());
        } catch (Throwable) {
            $this->jsonError('No se pudieron consultar los beneficios asignados.', 500);
        }
    }

    public function store(): void
    {
        $input = new LimpiarInputs();
        $error = $input->validar(['persona_id', 'beneficio_id', 'fecha_solicitud']);
        if ($error !== null) {
            $this->jsonError($error, 422);
            return;
        }

        try {
            $aprobadaRaw = $input->get('fecha_aprobada');
            $fechaAprobada = is_string($aprobadaRaw) && trim($aprobadaRaw) !== '' ? trim($aprobadaRaw) : null;
            $tipoDocRaw = $input->get('tipo_documento_id');
            $tipoDocId = is_numeric($tipoDocRaw) && (int) $tipoDocRaw > 0 ? (int) $tipoDocRaw : null;

            $archivos = $this->app->request()->getUploadedFiles();
            /** @var array<string, mixed>|null $archivoSoporte */
            $archivoSoporte = $_FILES['archivo_soporte'] ?? ($archivos['archivo_soporte'] ?? null);

            $gestor = $this->gestorDocumentos ?? new GestorDocumentos(
                DB::connection(),
                dirname(__DIR__, 2) . '/app/storage/DocumentosInces'
            );

            $registro = $this->beneficiosService->registrar(
                (int) $input->get('persona_id'),
                (int) $input->get('beneficio_id'),
                trim((string) $input->get('fecha_solicitud')),
                $fechaAprobada,
                is_array($archivoSoporte) ? $archivoSoporte : null,
                $tipoDocId,
                $gestor
            );

            $this->jsonOk(['dtbeneficio_id' => (int) $registro->dtbeneficio_id], 'Beneficio asignado correctamente conforme al Contrato Colectivo.', 201);
        } catch (Throwable $e) {
            $codigo = (int) $e->getCode();
            $this->jsonError(
                $codigo >= 400 && $codigo < 500 ? $e->getMessage() : 'No se pudo asignar el beneficio.',
                $codigo >= 400 && $codigo < 600 ? $codigo : 500
            );
        }
    }

    public function cargaMasiva(): void
    {
        // Captura y saneamiento de parámetros recibidos en la petición
        $input = new LimpiarInputs();
        $beneficioId = (int) $input->get('beneficio_id', 0);
        $tipo = trim((string) $input->get('tipo_asignacion', ''));

        // 2. Valida que el ID del beneficio sea positivo y exista en la base de datos
        if ($beneficioId <= 0 || !TipoBeneficiosModel::query()->whereKey($beneficioId)->exists()) {
            $this->jsonError('Selecciona un tipo de beneficio válido.', 422);
            return;
        }

        // Valida de forma estricta que el tipo de asignación esté entre las opciones permitidas
        if (!in_array($tipo, ['empleado', 'familiar'], true)) {
            $this->jsonError('Selecciona una sola opción: empleados o familiar.', 422);
            return;
        }

        try {
            // Obtiene los persona_id objetivo según el tipo elegido (solo vinculados a empleados activos)
            $personaIds = $tipo === 'empleado'
                ? EmpleadosModel::query()
                ->where('estatus_actual', 1)
                ->pluck('persona_id')
                : FamiliaresModel::query()
                ->whereIn(
                    'empleado_id',
                    EmpleadosModel::query()->where('estatus_actual', 1)->select('empleado_id')
                )
                ->pluck('persona_id');

            // Consulta qué personas ya tienen asignado este beneficio el día de hoy para no duplicarlas
            $hoy = date('Y-m-d');
            $existentes = DetalleBeneficioModel::query()
                ->where('beneficio_id', $beneficioId)
                ->where('fecha_solicitud', $hoy)
                ->pluck('persona_id')
                ->map(static fn($id): int => (int) $id) // Normaliza cada ID a entero
                ->all();

            // Filtra duplicados, descarta los ya existentes hoy y construye las filas a insertar
            $filas = $personaIds
                ->map(static fn($id): int => (int) $id) // Normaliza IDs a entero para que unique/diff sean precisos
                ->unique()                               // Evita IDs repetidos en la consulta origen
                ->diff($existentes)                      // Excluye personas que ya recibieron el beneficio hoy
                ->map(static fn(int $id): array => [    // Da formato de fila para la tabla detalle_beneficio
                    'persona_id' => $id,
                    'beneficio_id' => $beneficioId,
                    'fecha_solicitud' => $hoy,
                    'fecha_aprobada' => $hoy
                ])
                ->values();                              // Reindexa las claves numéricas de la colección (0, 1, 2...)

            // Ejecuta la inserción masiva en bloques de 500 dentro de una transacción (todo o nada)
            DB::connection()->transaction(static function () use ($filas): void {
                foreach ($filas->chunk(500) as $lote) {
                    DetalleBeneficioModel::query()->insert($lote->values()->all());
                }
            });

            // Devuelve el total de registros insertados y un mensaje acorde al resultado
            $total = $filas->count();
            $this->jsonOk(
                ['insertados' => $total],
                $total > 0 ? "Beneficio asignado a {$total} beneficiarios." : 'No hay beneficiarios nuevos por asignar.',
                201
            );
        } catch (Throwable) {
            // Captura cualquier fallo técnico (BD, conexión, tipos) y responde con error 500 genérico
            $this->jsonError('No se pudo realizar la carga masiva del beneficio.', 500);
        }
    }

    /**
     * Recibe el ID del beneficio asignado y los datos enviados; actualiza beneficiario, tipo, fechas y soporte.
     */
    public function update(string $id): void
    {
        $dtId = $this->idPositivo($id, 'beneficio');
        if ($dtId === null) {
            return;
        }

        $input = new LimpiarInputs();
        $error = $input->validar(['persona_id', 'beneficio_id', 'fecha_solicitud']);
        if ($error !== null) {
            $this->jsonError($error, 422);
            return;
        }

        try {
            $aprobadaRaw = $input->get('fecha_aprobada');
            $fechaAprobada = is_string($aprobadaRaw) && trim($aprobadaRaw) !== '' ? trim($aprobadaRaw) : null;
            $tipoDocRaw = $input->get('tipo_documento_id');
            $tipoDocId = is_numeric($tipoDocRaw) && (int) $tipoDocRaw > 0 ? (int) $tipoDocRaw : null;

            $archivos = $this->app->request()->getUploadedFiles();
            /** @var array<string, mixed>|null $archivoSoporte */
            $archivoSoporte = $_FILES['archivo_soporte'] ?? ($archivos['archivo_soporte'] ?? null);

            $gestor = $this->gestorDocumentos ?? new GestorDocumentos(
                DB::connection(),
                dirname(__DIR__, 2) . '/app/storage/DocumentosInces'
            );

            $registro = $this->beneficiosService->actualizar(
                $dtId,
                (int) $input->get('persona_id'),
                (int) $input->get('beneficio_id'),
                trim((string) $input->get('fecha_solicitud')),
                $fechaAprobada,
                is_array($archivoSoporte) ? $archivoSoporte : null,
                $tipoDocId,
                $gestor
            );

            $this->jsonOk([
                'dtbeneficio_id' => (int) $registro->dtbeneficio_id,
                'persona_id' => (int) $registro->persona_id,
                'beneficio_id' => (int) $registro->beneficio_id,
            ], 'Beneficio actualizado correctamente.');
        } catch (Throwable $e) {
            $codigo = (int) $e->getCode();
            $this->jsonError(
                $codigo >= 400 && $codigo < 500 ? $e->getMessage() : 'No se pudo actualizar el beneficio.',
                $codigo >= 400 && $codigo < 600 ? $codigo : 500
            );
        }
    }

    public function aprobar(string $id): void
    {
        $dtId = $this->idPositivo($id, 'beneficio');
        if ($dtId === null) {
            return;
        }

        try {
            $registro = $this->beneficiosService->aprobar($dtId);
            $this->jsonOk([
                'dtbeneficio_id' => (int) $registro->dtbeneficio_id,
                'fecha_aprobada' => $registro->fecha_aprobada?->format('Y-m-d'),
            ], 'Beneficio aprobado correctamente.');
        } catch (Throwable $e) {
            $codigo = (int) $e->getCode();
            $this->jsonError($e->getMessage(), $codigo >= 400 && $codigo < 600 ? $codigo : 500);
        }
    }
}
