<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\LimpiarInputs;
use App\Model\Personas\DireccionModel;
use App\Model\Personas\EmpleadosModel;
use App\Model\Personas\PersonasModel;
use App\Model\Personas\TelefonoPersonasModel;
use App\Services\CatalogosService;
use App\Services\ContextoSesion;
use App\Services\GestorDocumentos;
use App\Interfaces\RenderInterfaces;
use App\Utils\Config;
use flight\Engine;
use Throwable;

/**
 * Controlador general para el registro de empleados y consulta de ubicación geográfica.
 */
class RegistrarEmpleadoController implements RenderInterfaces
{
    /**
     * @param Engine<object> $app
     */
    public function __construct(
        private Engine $app,
        private Config $config,
        private CatalogosService $catalogos,
        private ?GestorDocumentos $gestorDocumentos = null,
    ) {}

    /**
     * [GET] Renderiza el formulario de registro de personal con catálogos hidratados desde Eloquent.
     */
    public function render(): void
    {
        $tiposDiscapacidad = ['0' => 'Ninguna'] + $this->catalogos->tiposDiscapacidad();

        $this->app->render('registrar_empleado', [
            'env' => $this->config->env(),
            'debug' => $this->config->isDebug(),
            'usuario' => (object) ContextoSesion::todo(),
            'estados_civiles' => $this->catalogos->estadosCiviles(),
            'tipos_discapacidad' => $tiposDiscapacidad,
            'estados' => $this->catalogos->estados(),
            'municipios' => [],
            'parroquias' => [],
            'estatus' => $this->catalogos->estatus(true),
            'cargos' => $this->catalogos->cargos(true),
            'departamentos' => $this->catalogos->departamentos(true),
            'dependencias' => $this->catalogos->dependencias(true),
            'niveles_academicos' => $this->catalogos->nivelesAcademicos(),
            'carreras' => $this->catalogos->carreras(),
            'tipos_documentos' => $this->catalogos->tiposDocumentos(),
            'generos' => $this->catalogos->generos(),
        ]);
    }

    /**
     * [POST] Registra un nuevo trabajador usando LimpiarInputs con nacionalidad y cédula separadas.
     */
    public function store(): void
    {
        $input = new LimpiarInputs();

        $camposRequeridos = $input->get('primerNombre') !== null
            ? ['primerNombre', 'primerApellido', 'nacionalidad', 'cedula', 'genero', 'civil', 'dependencia', 'departamento', 'cargo', 'estatus', 'nivelAcademico']
            : ['primer_nombre', 'primer_apellido', 'nacionalidad', 'cedula', 'genero', 'estado_civil_id', 'dependencia_id', 'departamento_id', 'cargo_id', 'estatus_id', 'nivel_academico_id'];

        $error = $input->validar($camposRequeridos);
        if ($error !== null) {
            $this->app->json(['ok' => false, 'message' => $error], 422);
            return;
        }

        if (!$input->validarNumericos('cedula')) {
            $this->app->json(['ok' => false, 'message' => 'La cédula debe contener solo números.'], 422);
            return;
        }

        // Nacionalidad y cédula se guardan estrictamente por separado
        $nacionalidad = substr(strtoupper((string) $input->get('nacionalidad', 'V')), 0, 1);
        $cedula = (int) $input->get('cedula');

        if (!in_array($nacionalidad, ['V', 'E'], true) || $cedula <= 0) {
            $this->app->json(['ok' => false, 'message' => 'Nacionalidad o cédula inválida.'], 422);
            return;
        }

        $existePersona = PersonasModel::query()
            ->where('nacionalidad', $nacionalidad)
            ->where('cedula', $cedula)
            ->exists();

        if ($existePersona) {
            $this->app->json(['ok' => false, 'message' => 'Ya existe una persona registrada con esa nacionalidad y cédula.'], 422);
            return;
        }

        $primerNombre = substr((string) ($input->get('primer_nombre') ?? $input->get('primerNombre', '')), 0, 100);
        $segundoNombre = substr((string) ($input->get('segundo_nombre') ?? $input->get('segundoNombre', '')), 0, 100);
        $primerApellido = substr((string) ($input->get('primer_apellido') ?? $input->get('primerApellido', '')), 0, 100);
        $segundoApellido = substr((string) ($input->get('segundo_apellido') ?? $input->get('segundoApellido', '')), 0, 100);
        $genero = (string) $input->get('genero', 'Masculino');

        $fechaNacimiento = (string) $input->get('fecha_nacimiento', '');
        if ($fechaNacimiento === '' && $input->get('ano') && $input->get('meses') && $input->get('dia')) {
            $fechaNacimiento = sprintf('%04d-%02d-%02d', (int) $input->get('ano'), (int) $input->get('meses'), (int) $input->get('dia'));
        }

        $estadoCivilId = (int) ($input->get('estado_civil_id') ?? $input->get('civil', 0));
        $dependenciaId = (int) ($input->get('dependencia_id') ?? $input->get('dependencia', 0));
        $departamentoId = (int) ($input->get('departamento_id') ?? $input->get('departamento', 0));
        $cargoId = (int) ($input->get('cargo_id') ?? $input->get('cargo', 0));
        $estatusId = (int) ($input->get('estatus_id') ?? $input->get('estatus', 0));
        $nivelAcademicoId = (int) ($input->get('nivel_academico_id') ?? $input->get('nivelAcademico', 0));
        $carreraRaw = $input->get('carrera_id') ?? $input->get('carrera');
        $carreraId = is_numeric($carreraRaw) && (int) $carreraRaw > 0 ? (int) $carreraRaw : null;

        // codigo_empleado es VARCHAR(10) en PostgreSQL: nunca superar 10 caracteres
        $codigoInput = (string) $input->get('codigo_empleado', '');
        $codigoEmpleado = substr($codigoInput !== '' ? $codigoInput : (string) $cedula, 0, 10);

        $correoRaw = (string) $input->get('correo', '');
        $correo = $correoRaw !== '' ? substr($correoRaw, 0, 255) : null;

        $parroquiaRaw = $input->get('parroquia_id') ?? $input->get('parroquia');
        $parroquiaId = is_numeric($parroquiaRaw) && (int) $parroquiaRaw > 0 ? (int) $parroquiaRaw : null;

        $telPrincipal = (string) $input->get('telefono_principal', '');
        if ($telPrincipal === '' && $input->get('linea') && $input->get('telefono')) {
            $telPrincipal = (string) $input->get('linea') . (string) $input->get('telefono');
        }
        $telPrincipal = substr($telPrincipal, 0, 20);
        $telEmergencia = substr((string) $input->get('telefono_emergencia', ''), 0, 20);

        $archivos = $this->app->request()->files->getData();
        $entradaArchivo = $archivos['archivo'] ?? null;
        $tipoDocRaw = $input->get('tipo_documento_id');
        $tipoDocumentoId = is_numeric($tipoDocRaw) && (int) $tipoDocRaw > 0 ? (int) $tipoDocRaw : null;

        $conexion = (new EmpleadosModel())->getConnection();

        try {
            $resultado = $conexion->transaction(function () use (
                $primerNombre,
                $segundoNombre,
                $primerApellido,
                $segundoApellido,
                $genero,
                $cedula,
                $nacionalidad,
                $fechaNacimiento,
                $estadoCivilId,
                $dependenciaId,
                $departamentoId,
                $estatusId,
                $cargoId,
                $nivelAcademicoId,
                $carreraId,
                $codigoEmpleado,
                $correo,
                $parroquiaId,
                $input,
                $telPrincipal,
                $telEmergencia,
                $entradaArchivo,
                $tipoDocumentoId
            ): array {
                $persona = PersonasModel::query()->create([
                    'primer_nombre' => $primerNombre,
                    'segundo_nombre' => $segundoNombre,
                    'primer_apellido' => $primerApellido,
                    'segundo_apellido' => $segundoApellido,
                    'genero' => $genero,
                    'cedula' => $cedula,
                    'nacionalidad' => $nacionalidad,
                    'fecha_nacimiento' => $fechaNacimiento,
                    'estado_civil_id' => $estadoCivilId > 0 ? $estadoCivilId : null,
                ]);

                $empleado = EmpleadosModel::query()->create([
                    'persona_id' => (int) $persona->persona_id,
                    'dependencia_id' => $dependenciaId,
                    'departamento_id' => $departamentoId,
                    'estatus_id' => $estatusId,
                    'cargo_id' => $cargoId,
                    'nivel_academico_id' => $nivelAcademicoId,
                    'carrera_id' => $carreraId,
                    'codigo_empleado' => $codigoEmpleado,
                    'estatus_actual' => 1,
                    'correo' => $correo,
                ]);

                if ($parroquiaId !== null) {
                    DireccionModel::query()->create([
                        'empleado_id' => (int) $empleado->empleado_id,
                        'parroquia_id' => $parroquiaId,
                        'vivienda' => substr((string) $input->get('vivienda', 'Casa'), 0, 30),
                        'calle' => substr((string) $input->get('calle', 'S/N'), 0, 30),
                        'nombre_urb' => substr((string) $input->get('nombre_urb', 'N/A'), 0, 100),
                        'piso_edificio' => substr((string) $input->get('piso_edificio', 'N/A'), 0, 10),
                        'numero_vivienda' => substr((string) $input->get('numero_vivienda', 'S/N'), 0, 10),
                        'descripcion' => $input->get('descripcion') ? substr((string) $input->get('descripcion'), 0, 255) : null,
                    ]);
                }

                if ($telPrincipal !== '') {
                    TelefonoPersonasModel::query()->create([
                        'persona_id' => (int) $persona->persona_id,
                        'numero_telefono' => $telPrincipal,
                        'emergencia' => false,
                    ]);
                }

                if ($telEmergencia !== '') {
                    TelefonoPersonasModel::query()->create([
                        'persona_id' => (int) $persona->persona_id,
                        'numero_telefono' => $telEmergencia,
                        'emergencia' => true,
                    ]);
                }

                $documento = null;
                if (
                    $this->gestorDocumentos !== null
                    && is_array($entradaArchivo)
                    && ($entradaArchivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE
                    && $tipoDocumentoId !== null
                ) {
                    $documento = $this->gestorDocumentos->subir($entradaArchivo, $tipoDocumentoId, (int) $empleado->empleado_id);
                }

                return [
                    'empleado_id' => (int) $empleado->empleado_id,
                    'persona_id' => (int) $persona->persona_id,
                    'codigo_empleado' => (string) $empleado->codigo_empleado,
                    'documento_id' => $documento?->documentos_id,
                ];
            });

            $this->app->json([
                'ok' => true,
                'message' => 'Trabajador registrado correctamente.',
                'data' => $resultado,
            ], 201);
        } catch (Throwable $e) {
            $codigo = (int) $e->getCode();
            $this->app->json([
                'ok' => false,
                'message' => $codigo >= 400 && $codigo < 500 ? $e->getMessage() : 'No se pudo registrar el trabajador en la base de datos.',
            ], $codigo >= 400 && $codigo < 600 ? $codigo : 500);
        }
    }

    /**
     * [GET] Lista los municipios pertenecientes a un estado.
     */
    public function municipios(string $estadoId): void
    {
        if (!ctype_digit($estadoId) || (int) $estadoId <= 0) {
            $this->app->json(['ok' => false, 'message' => 'Identificador de estado inválido.'], 400);
            return;
        }

        $this->app->json([
            'ok' => true,
            'data' => $this->catalogos->municipiosPorEstado((int) $estadoId),
        ], 200);
    }

    /**
     * [GET] Lista las parroquias pertenecientes a un municipio.
     */
    public function parroquias(string $municipioId): void
    {
        if (!ctype_digit($municipioId) || (int) $municipioId <= 0) {
            $this->app->json(['ok' => false, 'message' => 'Identificador de municipio inválido.'], 400);
            return;
        }

        $this->app->json([
            'ok' => true,
            'data' => $this->catalogos->parroquiasPorMunicipio((int) $municipioId),
        ], 200);
    }
}
