<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\LimpiarInputs;
use App\Model\Personas\FamiliaresModel;
use App\Model\Personas\PersonasModel;
use App\Services\CatalogosService;
use App\Services\ContextoSesion;
use App\Services\GestorDocumentos;
use App\Interfaces\RenderInterfaces;
use App\Utils\Config;
use flight\Engine;
use InvalidArgumentException;
use Throwable;

/**
 * Controlador general para la vista y registro de carga familiar.
 */
class RegistrarFamiliarController implements RenderInterfaces
{
    /**
     * @param Engine<object> $app
     */
    public function __construct(
        private Engine $app,
        private Config $config,
        private CatalogosService $catalogos,
        private ?GestorDocumentos $gestorDocumentos = null,
    ) {
    }

    /**
     * [GET] Renderiza el formulario de registro de carga familiar.
     */
    public function render(): void
    {
        $empleadoPreseleccionado = $this->app->request()->query['empleado'] ?? '';
        $empleadoSeleccionado = is_numeric($empleadoPreseleccionado) && (int) $empleadoPreseleccionado > 0
            ? (string) (int) $empleadoPreseleccionado
            : '';

        $empleadosPreseleccionados = $empleadoSeleccionado !== ''
            ? $this->catalogos->empleadoParaSelectPorId((int) $empleadoSeleccionado)
            : [];

        $parentescos = $this->catalogos->tiposParentesco();
        $recaudosPorParentesco = [];
        foreach (array_keys($parentescos) as $parentescoId) {
            $recaudosPorParentesco[(string) $parentescoId] = $this->catalogos->recaudosParaFamiliar((int) $parentescoId);
        }

        $this->app->render('registrar_familiar', [
            'env' => $this->config->env(),
            'debug' => $this->config->isDebug(),
            'usuario' => (object) ContextoSesion::todo(),
            'empleados' => $empleadosPreseleccionados,
            'empleado_seleccionado' => $empleadoSeleccionado,
            'estados_civiles' => $this->catalogos->estadosCiviles(),
            'parentescos' => $parentescos,
            'tipos_documentos' => $this->catalogos->tiposDocumentos(),
            'recaudos_por_parentesco' => $recaudosPorParentesco,
            'generos' => $this->catalogos->generos(),
        ]);
    }

    /**
     * [POST] Registra un familiar usando LimpiarInputs con nacionalidad y cédula separadas.
     */
    public function store(): void
    {
        $input = new LimpiarInputs();

        $error = $input->validar([
            'empleado_id',
            'parentesco_id',
            'nacionalidad',
            'cedula',
            'primer_nombre',
            'primer_apellido',
            'fecha_nacimiento',
            'genero',
            'tomo',
            'folio',
        ]);

        if ($error !== null) {
            $this->app->json(['ok' => false, 'message' => $error], 422);
            return;
        }

        if (!$input->validarNumericos('cedula') || !$input->validarNumericos('tomo') || !$input->validarNumericos('folio')) {
            $this->app->json(['ok' => false, 'message' => 'La cédula, el tomo y el folio deben contener solo números.'], 422);
            return;
        }

        // Nacionalidad y cédula se guardan estrictamente por separado
        $nacionalidad = substr(strtoupper((string) $input->get('nacionalidad', 'V')), 0, 1);
        $cedula = (int) $input->get('cedula');

        if (!in_array($nacionalidad, ['V', 'E'], true) || $cedula <= 0) {
            $this->app->json(['ok' => false, 'message' => 'Nacionalidad o cédula del familiar inválida.'], 422);
            return;
        }

        $empleadoId = (int) $input->get('empleado_id');
        $parentescoId = (int) $input->get('parentesco_id');
        $estadoCivilRaw = $input->get('estado_civil_id');
        $estadoCivilId = is_numeric($estadoCivilRaw) && (int) $estadoCivilRaw > 0 ? (int) $estadoCivilRaw : null;

        $primerNombre = substr((string) $input->get('primer_nombre', ''), 0, 100);
        $segundoNombre = substr((string) $input->get('segundo_nombre', ''), 0, 100);
        $primerApellido = substr((string) $input->get('primer_apellido', ''), 0, 100);
        $segundoApellido = substr((string) $input->get('segundo_apellido', ''), 0, 100);
        $fechaNacimiento = (string) $input->get('fecha_nacimiento', '');
        $genero = substr((string) $input->get('genero', 'Masculino'), 0, 20);
        $tomo = (int) $input->get('tomo');
        $folio = (int) $input->get('folio');

        $archivos = $this->app->request()->files->getData();
        $entradaArchivo = $archivos['archivo'] ?? null;
        $documentosPorTipo = CatalogosService::normalizarArchivosPorTipo($archivos);
        $tipoDocRaw = $input->get('tipo_documento_id');
        $tipoDocumentoId = is_numeric($tipoDocRaw) && (int) $tipoDocRaw > 0 ? (int) $tipoDocRaw : null;

        $conexion = (new FamiliaresModel())->getConnection();

        try {
            $resultado = $conexion->transaction(function () use (
                $empleadoId,
                $parentescoId,
                $estadoCivilId,
                $nacionalidad,
                $cedula,
                $primerNombre,
                $segundoNombre,
                $primerApellido,
                $segundoApellido,
                $fechaNacimiento,
                $genero,
                $tomo,
                $folio,
                $entradaArchivo,
                $tipoDocumentoId,
                $documentosPorTipo
            ): array {
                $persona = PersonasModel::query()
                    ->where('nacionalidad', $nacionalidad)
                    ->where('cedula', $cedula)
                    ->first();

                if ($persona instanceof PersonasModel) {
                    $yaVinculado = FamiliaresModel::query()
                        ->where('empleado_id', $empleadoId)
                        ->where('persona_id', (int) $persona->persona_id)
                        ->exists();

                    if ($yaVinculado) {
                        throw new InvalidArgumentException('Esta persona ya se encuentra registrada como familiar del trabajador.', 422);
                    }
                } else {
                    $persona = PersonasModel::query()->create([
                        'primer_nombre' => $primerNombre,
                        'segundo_nombre' => $segundoNombre,
                        'primer_apellido' => $primerApellido,
                        'segundo_apellido' => $segundoApellido,
                        'genero' => $genero,
                        'cedula' => $cedula,
                        'nacionalidad' => $nacionalidad,
                        'fecha_nacimiento' => $fechaNacimiento,
                        'estado_civil_id' => $estadoCivilId,
                    ]);
                }

                $familiar = FamiliaresModel::query()->create([
                    'empleado_id' => $empleadoId,
                    'persona_id' => (int) $persona->persona_id,
                    'parentesco_id' => $parentescoId,
                    'tomo' => $tomo,
                    'folio' => $folio,
                    'genero' => $genero,
                ]);

                $documentosIds = [];
                if ($this->gestorDocumentos !== null) {
                    if (
                        is_array($entradaArchivo)
                        && ($entradaArchivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE
                        && $tipoDocumentoId !== null
                    ) {
                        $doc = $this->gestorDocumentos->subir(
                            $entradaArchivo,
                            $tipoDocumentoId,
                            $empleadoId,
                            (int) $familiar->familia_id
                        );
                        $documentosIds[] = (int) $doc->documentos_id;
                    }

                    foreach ($documentosPorTipo as $tipoDocId => $archivoDoc) {
                        $doc = $this->gestorDocumentos->subir(
                            $archivoDoc,
                            (int) $tipoDocId,
                            $empleadoId,
                            (int) $familiar->familia_id
                        );
                        $documentosIds[] = (int) $doc->documentos_id;
                    }
                }

                return [
                    'familia_id' => (int) $familiar->familia_id,
                    'persona_id' => (int) $persona->persona_id,
                    'empleado_id' => (int) $familiar->empleado_id,
                    'documento_id' => $documentosIds[0] ?? null,
                    'documentos_ids' => $documentosIds,
                ];
            });

            $this->app->json([
                'ok' => true,
                'message' => 'Familiar registrado correctamente.',
                'data' => $resultado,
            ], 201);
        } catch (Throwable $e) {
            $codigo = (int) $e->getCode();
            $this->app->json([
                'ok' => false,
                'message' => $codigo >= 400 && $codigo < 500 ? $e->getMessage() : 'No se pudo registrar el familiar.',
            ], $codigo >= 400 && $codigo < 600 ? $codigo : 500);
        }
    }
}
