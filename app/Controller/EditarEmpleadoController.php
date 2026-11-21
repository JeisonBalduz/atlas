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
 * Controlador general para la vista y actualización de empleados.
 */
final class EditarEmpleadoController implements RenderInterfaces
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
     * [GET] Renderiza el formulario de edición de empleado con datos y catálogos preseleccionados desde Eloquent.
     */
    public function render (?string $id = null): void
    {
        $idRaw = $id ?? ($this->app->request()->query['id'] ?? null);
        if (!is_numeric($idRaw) || (int) $idRaw <= 0) {
            $this->app->redirect('/empleado/registros');
            return;
        }

        $empleadoId = (int) $idRaw;
        $empleado = EmpleadosModel::query()
            ->with([
                'persona.telefonos',
                'direccion.parroquia.municipio',
            ])
            ->find($empleadoId);

        if (!$empleado instanceof EmpleadosModel || !$empleado->persona instanceof PersonasModel) {
            $this->app->halt(404, 'Empleado no encontrado');
            return;
        }

        $persona = $empleado->persona;
        $telPrincipal = $persona->telefonos->firstWhere('emergencia', false);
        $telEmergencia = $persona->telefonos->firstWhere('emergencia', true);

        $municipioActualId = (int) ($empleado->direccion?->parroquia?->municipio_id ?? 0);
        $estadoActualId = (int) ($empleado->direccion?->parroquia?->municipio?->estado_id ?? 0);

        $this->app->render('editar_empleado', [
            'env' => $this->config->env(),
            'debug' => $this->config->isDebug(),
            'usuario' => (object) ContextoSesion::todo(),
            'empleado' => $empleado,
            'persona' => $persona,
            'direccion' => $empleado->direccion,
            'telefono_principal' => $telPrincipal !== null ? (string) $telPrincipal->numero_telefono : '',
            'telefono_emergencia' => $telEmergencia !== null ? (string) $telEmergencia->numero_telefono : '',
            'estados_civiles' => $this->catalogos->estadosCiviles(),
            'dependencias' => $this->catalogos->dependencias(false),
            'departamentos' => $this->catalogos->departamentos(false),
            'cargos' => $this->catalogos->cargos(false),
            'estatus' => $this->catalogos->estatus(false),
            'niveles_academicos' => $this->catalogos->nivelesAcademicos(),
            'carreras' => $this->catalogos->carreras(),
            'estados' => $this->catalogos->estados(),
            'municipios' => $estadoActualId > 0 ? $this->catalogos->municipiosPorEstado($estadoActualId) : [],
            'parroquias' => $municipioActualId > 0 ? $this->catalogos->parroquiasPorMunicipio($municipioActualId) : [],
            'estado_seleccionado' => $estadoActualId > 0 ? (string) $estadoActualId : '',
            'municipio_seleccionado' => $municipioActualId > 0 ? (string) $municipioActualId : '',
            'tipos_documentos' => $this->catalogos->tiposDocumentos(),
            'generos' => $this->catalogos->generos((string) $persona->genero),
        ]);
    }

    /**
     * [PUT|POST] Actualiza un trabajador existente usando LimpiarInputs con nacionalidad y cédula separadas.
     */
    public function update(string $id): void
    {
        if (!ctype_digit($id) || (int) $id <= 0) {
            $this->app->json(['ok' => false, 'message' => 'Identificador de empleado inválido.'], 400);
            return;
        }

        $empleado = EmpleadosModel::query()->with(['persona.telefonos', 'direccion'])->find((int) $id);
        if (!$empleado instanceof EmpleadosModel || !$empleado->persona instanceof PersonasModel) {
            $this->app->json(['ok' => false, 'message' => 'Trabajador no encontrado.'], 404);
            return;
        }

        $input = new LimpiarInputs();
        $persona = $empleado->persona;
        $dirActual = $empleado->direccion;

        $nacionalidad = substr(strtoupper((string) $input->get('nacionalidad', $persona->nacionalidad)), 0, 1);
        $cedula = (int) $input->get('cedula', (string) $persona->cedula);

        if (!in_array($nacionalidad, ['V', 'E'], true) || $cedula <= 0) {
            $this->app->json(['ok' => false, 'message' => 'Nacionalidad o cédula inválida.'], 422);
            return;
        }

        $duplicada = PersonasModel::query()
            ->where('nacionalidad', $nacionalidad)
            ->where('cedula', $cedula)
            ->where('persona_id', '!=', (int) $empleado->persona_id)
            ->exists();

        if ($duplicada) {
            $this->app->json(['ok' => false, 'message' => 'Ya existe otra persona registrada con esa nacionalidad y cédula.'], 422);
            return;
        }

        $primerNombre = substr((string) $input->get('primer_nombre', $persona->primer_nombre), 0, 100);
        $segundoNombre = substr((string) $input->get('segundo_nombre', $persona->segundo_nombre), 0, 100);
        $primerApellido = substr((string) $input->get('primer_apellido', $persona->primer_apellido), 0, 100);
        $segundoApellido = substr((string) $input->get('segundo_apellido', $persona->segundo_apellido), 0, 100);
        $genero = (string) $input->get('genero', $persona->genero);
        $fechaNacimiento = (string) $input->get('fecha_nacimiento', $persona->fecha_nacimiento?->format('Y-m-d') ?? '');
        $estadoCivilId = (int) $input->get('estado_civil_id', (string) ($persona->estado_civil_id ?? 0));

        $dependenciaId = (int) $input->get('dependencia_id', (string) $empleado->dependencia_id);
        $departamentoId = (int) $input->get('departamento_id', (string) $empleado->departamento_id);
        $cargoId = (int) $input->get('cargo_id', (string) $empleado->cargo_id);
        $estatusId = (int) $input->get('estatus_id', (string) $empleado->estatus_id);
        $nivelAcademicoId = (int) $input->get('nivel_academico_id', (string) $empleado->nivel_academico_id);
        $carreraRaw = $input->get('carrera_id', $empleado->carrera_id);
        $carreraId = is_numeric($carreraRaw) && (int) $carreraRaw > 0 ? (int) $carreraRaw : null;

        // codigo_empleado respetando límite VARCHAR(10)
        $codigoRaw = (string) $input->get('codigo_empleado', (string) ($empleado->codigo_empleado ?? $cedula));
        $codigoEmpleado = substr($codigoRaw !== '' ? $codigoRaw : (string) $cedula, 0, 10);

        $estatusActualRaw = $input->get('estatus_actual', $empleado->estatus_actual);
        $estatusActual = is_numeric($estatusActualRaw) && (int) $estatusActualRaw === 0 ? 0 : 1;

        $correoRaw = (string) $input->get('correo', (string) ($empleado->correo ?? ''));
        $correo = $correoRaw !== '' ? substr($correoRaw, 0, 255) : null;

        $parroquiaRaw = $input->get('parroquia_id', $dirActual?->parroquia_id);
        $parroquiaId = is_numeric($parroquiaRaw) && (int) $parroquiaRaw > 0 ? (int) $parroquiaRaw : null;

        $telPrincipal = substr((string) $input->get('telefono_principal', ''), 0, 20);
        $telEmergencia = substr((string) $input->get('telefono_emergencia', ''), 0, 20);

        try {
            $empleado->getConnection()->transaction(function () use (
                $persona,
                $empleado,
                $dirActual,
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
                $estatusActual,
                $correo,
                $parroquiaId,
                $input,
                $telPrincipal,
                $telEmergencia
            ): void {
                $persona->fill([
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
                $persona->save();

                $empleado->fill([
                    'dependencia_id' => $dependenciaId,
                    'departamento_id' => $departamentoId,
                    'estatus_id' => $estatusId,
                    'cargo_id' => $cargoId,
                    'nivel_academico_id' => $nivelAcademicoId,
                    'carrera_id' => $carreraId,
                    'codigo_empleado' => $codigoEmpleado,
                    'estatus_actual' => $estatusActual,
                    'correo' => $correo,
                ]);
                $empleado->save();

                if ($parroquiaId !== null) {
                    DireccionModel::query()->updateOrCreate(
                        ['empleado_id' => (int) $empleado->empleado_id],
                        [
                            'parroquia_id' => $parroquiaId,
                            'vivienda' => substr((string) $input->get('vivienda', $dirActual?->vivienda ?? 'Casa'), 0, 30),
                            'calle' => substr((string) $input->get('calle', $dirActual?->calle ?? 'S/N'), 0, 30),
                            'nombre_urb' => substr((string) $input->get('nombre_urb', $dirActual?->nombre_urb ?? 'N/A'), 0, 100),
                            'piso_edificio' => substr((string) $input->get('piso_edificio', $dirActual?->piso_edificio ?? 'N/A'), 0, 10),
                            'numero_vivienda' => substr((string) $input->get('numero_vivienda', $dirActual?->numero_vivienda ?? 'S/N'), 0, 10),
                            'descripcion' => $input->get('descripcion', $dirActual?->descripcion)
                                ? substr((string) $input->get('descripcion', $dirActual?->descripcion), 0, 255)
                                : null,
                        ]
                    );
                }

                if ($telPrincipal !== '') {
                    TelefonoPersonasModel::query()->updateOrCreate(
                        ['persona_id' => (int) $persona->persona_id, 'emergencia' => false],
                        ['numero_telefono' => $telPrincipal]
                    );
                }

                if ($telEmergencia !== '') {
                    TelefonoPersonasModel::query()->updateOrCreate(
                        ['persona_id' => (int) $persona->persona_id, 'emergencia' => true],
                        ['numero_telefono' => $telEmergencia]
                    );
                }
            });

            $this->app->json([
                'ok' => true,
                'message' => 'Datos del trabajador actualizados correctamente.',
                'data' => ['empleado_id' => (int) $empleado->empleado_id],
            ], 200);
        } catch (Throwable) {
            $this->app->json(['ok' => false, 'message' => 'No se pudo actualizar el trabajador.'], 500);
        }
    }
}
