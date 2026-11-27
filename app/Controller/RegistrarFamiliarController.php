<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\LimpiarInputs;
use App\Model\Beneficios\DetalleDiscapacidadModel;
use App\Model\Beneficios\GradoDiscapacidadModel;
use App\Model\Catalogos\TipoParentescoModel;
use App\Model\Personas\EmpleadosModel;
use App\Model\Personas\FamiliaresModel;
use App\Model\Personas\PersonasModel;
use App\Services\CatalogosService;
use App\Services\GestorDocumentos;
use App\Utils\Config;
use DateTimeImmutable;
use flight\Engine;
use InvalidArgumentException;
use Throwable;

/**
 * Controlador general para la vista y registro de carga familiar.
 */
class RegistrarFamiliarController extends BaseController
{
    public function __construct(
        Engine $app,
        Config $config,
        private CatalogosService $catalogos,
        private ?GestorDocumentos $gestorDocumentos = null,
    ) {
        parent::__construct($app, $config);
    }

    public function render(): void
    {
        $empleadoPreseleccionado = $this->app->request()->query['empleado'] ?? '';
        $empleadoId = is_numeric($empleadoPreseleccionado) && (int) $empleadoPreseleccionado > 0
            ? (int) $empleadoPreseleccionado
            : 0;

        $empleadoValido = $empleadoId > 0 && EmpleadosModel::query()
            ->where('empleado_id', $empleadoId)
            ->whereIn('estatus_actual', [1, 3])
            ->exists();

        $empleadoSeleccionado = $empleadoValido ? (string) $empleadoId : '';
        $empleadosPreseleccionados = $empleadoSeleccionado !== ''
            ? $this->catalogos->empleadoParaSelectPorId((int) $empleadoSeleccionado)
            : [];

        $parentescos = $this->catalogos->tiposParentesco();
        $recaudosPorParentesco = [];
        foreach (array_keys($parentescos) as $parentescoId) {
            $recaudosPorParentesco[(string) $parentescoId] = $this->catalogos->recaudosParaFamiliar((int) $parentescoId);
        }

        $tiposDiscMap = [];
        foreach ($this->catalogos->tiposDiscapacidad() as $nombreTipo) {
            $tiposDiscMap[(string) $nombreTipo] = (string) $nombreTipo;
        }

        $this->renderVista('registrar_familiar', [
            'empleados' => $empleadosPreseleccionados,
            'empleado_seleccionado' => $empleadoSeleccionado,
            'estados_civiles' => $this->catalogos->estadosCiviles(),
            'parentescos' => $parentescos,
            'tipos_documentos' => $this->catalogos->tiposDocumentos(),
            'recaudos_por_parentesco' => $recaudosPorParentesco,
            'recaudo_discapacidad' => $this->catalogos->recaudoDiscapacidadUnificado(),
            'tipos_discapacidad' => $tiposDiscMap,
            'grados_discapacidad' => $this->catalogos->gradosDiscapacidad(),
            'generos' => $this->catalogos->generos(),
        ]);
    }

    /**
     * Registra un familiar validando edad < 9 años, tomo/folio según parentesco y discapacidad opcional.
     * Recibe los datos HTTP del formulario y responde JSON 201 o error 422/500.
     */
    public function store(): void
    {
        $input = new LimpiarInputs();

        $error = $input->validar([
            'empleado_id',
            'parentesco_id',
            'nacionalidad',
            'primer_nombre',
            'primer_apellido',
            'fecha_nacimiento',
            'genero',
        ]);

        if ($error !== null) {
            $this->jsonError($error, 422);
            return;
        }

        $empleadoId = (int) $input->get('empleado_id');
        $parentescoId = (int) $input->get('parentesco_id');
        $nacionalidad = substr(strtoupper((string) $input->get('nacionalidad', 'V')), 0, 1);
        $fechaNacimiento = trim((string) $input->get('fecha_nacimiento', ''));

        if (!in_array($nacionalidad, ['V', 'E'], true)) {
            $this->jsonError('Nacionalidad del familiar inválida.', 422);
            return;
        }

        $requiereTomoFolio = $this->esParentescoHijo($parentescoId);
        $tomoRaw = trim((string) $input->get('tomo', ''));
        $folioRaw = trim((string) $input->get('folio', ''));

        if ($requiereTomoFolio) {
            if ($tomoRaw === '' || $folioRaw === '') {
                $this->jsonError('El tomo y el folio del acta son obligatorios para hijos e hijas.', 422);
                return;
            }
            if (!ctype_digit($tomoRaw) || !ctype_digit($folioRaw) || (int) $tomoRaw <= 0 || (int) $folioRaw <= 0) {
                $this->jsonError('El tomo y el folio deben contener solo números positivos.', 422);
                return;
            }
            $tomo = (int) $tomoRaw;
            $folio = (int) $folioRaw;
        } else {
            if (($tomoRaw !== '' && !ctype_digit($tomoRaw)) || ($folioRaw !== '' && !ctype_digit($folioRaw))) {
                $this->jsonError('El tomo y el folio deben contener solo números.', 422);
                return;
            }
            $tomo = $tomoRaw !== '' ? (int) $tomoRaw : 0;
            $folio = $folioRaw !== '' ? (int) $folioRaw : 0;
        }

        $cedulaRaw = trim((string) $input->get('cedula', ''));
        $esMenorDeNueve = $this->esMenorDeNueve($fechaNacimiento);
        $cedulaTemporalGenerada = false;

        if ($cedulaRaw === '') {
            if (!$esMenorDeNueve) {
                $this->jsonError('La cédula del familiar es obligatoria para personas de 9 años o más.', 422);
                return;
            }
            $cedula = $this->generarCedulaTemporalUnica($nacionalidad, $tomo, $folio);
            $cedulaTemporalGenerada = true;
        } else {
            if (!ctype_digit($cedulaRaw) || (int) $cedulaRaw <= 0) {
                $this->jsonError('La cédula debe contener solo números válidos.', 422);
                return;
            }
            $cedula = (int) $cedulaRaw;
        }

        $estadoCivilRaw = $input->get('estado_civil_id');
        $estadoCivilId = is_numeric($estadoCivilRaw) && (int) $estadoCivilRaw > 0 ? (int) $estadoCivilRaw : null;

        $primerNombre = substr((string) $input->get('primer_nombre', ''), 0, 100);
        $segundoNombre = substr((string) $input->get('segundo_nombre', ''), 0, 100);
        $primerApellido = substr((string) $input->get('primer_apellido', ''), 0, 100);
        $segundoApellido = substr((string) $input->get('segundo_apellido', ''), 0, 100);
        $genero = substr((string) $input->get('genero', 'Masculino'), 0, 20);

        $discapacidadValidada = $this->validarDiscapacidadOpcional($input);
        if (isset($discapacidadValidada['error'])) {
            $this->jsonError($discapacidadValidada['error'], 422);
            return;
        }

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
                $cedulaTemporalGenerada,
                $primerNombre,
                $segundoNombre,
                $primerApellido,
                $segundoApellido,
                $fechaNacimiento,
                $genero,
                $tomo,
                $folio,
                $discapacidadValidada,
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

                if (isset($discapacidadValidada['datos'])) {
                    DetalleDiscapacidadModel::query()->create(
                        array_merge(['persona_id' => (int) $persona->persona_id], $discapacidadValidada['datos'])
                    );
                }

                $documentosIds = [];
                $famId = (int) $familiar->familia_id;
                if ($this->gestorDocumentos !== null) {
                    if (is_array($entradaArchivo) && ($entradaArchivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE && $tipoDocumentoId !== null) {
                        $documentosIds[] = (int) $this->gestorDocumentos->subir($entradaArchivo, $tipoDocumentoId, $empleadoId, $famId)->documentos_id;
                    }

                    foreach ($documentosPorTipo as $tipoDocId => $archivoDoc) {
                        $documentosIds[] = (int) $this->gestorDocumentos->subir($archivoDoc, (int) $tipoDocId, $empleadoId, $famId)->documentos_id;
                    }
                }

                return [
                    'familia_id' => $famId,
                    'persona_id' => (int) $persona->persona_id,
                    'empleado_id' => (int) $familiar->empleado_id,
                    'cedula' => $cedula,
                    'cedula_temporal' => $cedulaTemporalGenerada,
                    'documento_id' => $documentosIds[0] ?? null,
                    'documentos_ids' => $documentosIds,
                ];
            });

            $this->jsonOk($resultado, 'Familiar registrado correctamente.', 201);
        } catch (Throwable $e) {
            $codigo = (int) $e->getCode();
            $this->jsonError(
                $codigo >= 400 && $codigo < 500 ? $e->getMessage() : 'No se pudo registrar el familiar.',
                $codigo >= 400 && $codigo < 600 ? $codigo : 500
            );
        }
    }

    /**
     * Determina si un ID de parentesco corresponde a Hijo o Hija.
     * Recibe el ID de parentesco y retorna true cuando requiere tomo y folio.
     */
    private function esParentescoHijo(int $parentescoId): bool
    {
        $nombre = (string) (TipoParentescoModel::query()->whereKey($parentescoId)->value('parentesco') ?? '');
        if ($nombre !== '') {
            return preg_match('/^hij[oa]\b/iu', trim($nombre)) === 1;
        }

        return in_array($parentescoId, [3, 4], true);
    }

    /**
     * Calcula si la fecha de nacimiento corresponde a un menor de 9 años cumplidos.
     * Recibe fecha ISO (YYYY-MM-DD) y retorna true si edad < 9 años.
     */
    private function esMenorDeNueve(string $fechaNacimiento): bool
    {
        $fecha = DateTimeImmutable::createFromFormat('!Y-m-d', $fechaNacimiento);
        if (!$fecha instanceof DateTimeImmutable) {
            return false;
        }

        $hoy = new DateTimeImmutable('today');
        if ($fecha > $hoy) {
            return false;
        }

        return $fecha->diff($hoy)->y < 9;
    }

    /**
     * Genera una cédula numérica temporal (rango 900000000-999999999) desde tomo y folio sin colisiones en BD.
     * Recibe nacionalidad, tomo y folio; retorna un entero único seguro para PostgreSQL INT32.
     */
    private function generarCedulaTemporalUnica(string $nacionalidad, int $tomo, int $folio): int
    {
        $tomoBloque = str_pad((string) (abs($tomo) % 10000), 4, '0', STR_PAD_LEFT);
        $folioBloque = str_pad((string) (abs($folio) % 10000), 4, '0', STR_PAD_LEFT);
        $base = (int) ('9' . $tomoBloque . $folioBloque);

        if ($base < 900000000 || $base > 999999999) {
            $base = 900000001;
        }

        $candidata = $base;
        $intentos = 0;
        while (
            $intentos < 500
            && PersonasModel::query()
                ->where('nacionalidad', $nacionalidad)
                ->where('cedula', $candidata)
                ->exists()
        ) {
            $candidata = $candidata >= 999999999 ? 900000001 : $candidata + 1;
            $intentos++;
        }

        return $candidata;
    }

    /**
     * Valida el bloque opcional de discapacidad cuando `tiene_discapacidad = 1`.
     * Recibe LimpiarInputs y retorna el arreglo de datos validados o un mensaje de error.
     *
     * @return array{datos?: array<string, mixed>, error?: string}
     */
    private function validarDiscapacidadOpcional(LimpiarInputs $input): array
    {
        if ((string) $input->get('tiene_discapacidad', '0') !== '1') {
            return [];
        }

        $gradoRaw = $input->get('grado_discapacidad_id');
        $gradoId = is_numeric($gradoRaw) && (int) $gradoRaw > 0 ? (int) $gradoRaw : null;
        $tipoNombre = trim((string) $input->get('tipo_discapacidad', ''));
        $numeroCarnet = trim((string) $input->get('numero_carnet', ''));
        $entidadEmisora = trim((string) $input->get('entidad_emisora', 'CONAPDIS')) ?: 'CONAPDIS';
        $descripcion = trim((string) $input->get('descripcion_discapacidad', ''));
        $emisionRaw = trim((string) $input->get('fecha_emision_carnet', ''));
        $vencimientoRaw = trim((string) $input->get('fecha_vencimiento_carnet', ''));

        if ($gradoId === null || !GradoDiscapacidadModel::query()->whereKey($gradoId)->exists()) {
            return ['error' => 'Debe seleccionar un grado de discapacidad válido.'];
        }
        if ($numeroCarnet === '') {
            return ['error' => 'El número de carnet CONAPDIS es obligatorio al asignar discapacidad.'];
        }
        if ($emisionRaw !== '' && $vencimientoRaw !== '' && $vencimientoRaw < $emisionRaw) {
            return ['error' => 'La fecha de vencimiento del carnet no puede ser menor que la fecha de emisión.'];
        }

        return [
            'datos' => [
                'grado_discapacidad_id' => $gradoId,
                'numero_carnet' => substr($numeroCarnet, 0, 30),
                'entidad_emisora' => substr($entidadEmisora, 0, 100),
                'descripcion' => $descripcion !== '' ? $descripcion : null,
                'dirigido' => $tipoNombre !== '' ? substr($tipoNombre, 0, 30) : 'Familiar',
                'fecha_emision_carnet' => preg_match('/^\d{4}-\d{2}-\d{2}$/', $emisionRaw) === 1 ? $emisionRaw : date('Y-m-d'),
                'fecha_vencimiento_carnet' => preg_match('/^\d{4}-\d{2}-\d{2}$/', $vencimientoRaw) === 1
                    ? $vencimientoRaw
                    : date('Y-m-d', strtotime('+5 years')),
            ],
        ];
    }
}
