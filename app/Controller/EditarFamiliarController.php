<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\LimpiarInputs;
use App\Model\Personas\EmpleadosModel;
use App\Model\Personas\FamiliaresModel;
use App\Model\Personas\PersonasModel;
use App\Services\CatalogosService;
use App\Services\GestorDocumentos;
use App\Utils\Config;
use flight\Engine;
use Throwable;

/**
 * Controlador general para la vista y actualización de carga familiar.
 */
class EditarFamiliarController extends BaseController
{
    public function __construct(
        Engine $app,
        Config $config,
        private CatalogosService $catalogos,
        private ?GestorDocumentos $gestorDocumentos = null,
    ) {
        parent::__construct($app, $config);
    }

    public function render(?string $id = null): void
    {
        $idRaw = $id ?? ($this->app->request()->query['id'] ?? null);
        if (!is_numeric($idRaw) || (int) $idRaw <= 0) {
            $this->app->redirect('/empleado/registros/familiar');
            return;
        }

        $familiar = FamiliaresModel::query()
            ->with(['persona', 'empleado.persona', 'empleado.cargo', 'empleado.departamento'])
            ->find((int) $idRaw);

        if (!$familiar instanceof FamiliaresModel || !$familiar->persona instanceof PersonasModel) {
            $this->app->halt(404, 'Familiar no encontrado');
            return;
        }

        $titular = $familiar->empleado;
        $titularPersona = $titular instanceof EmpleadosModel ? $titular->persona : null;
        $titularNombre = CatalogosService::nombreCompletoPersona($titularPersona);
        $titularCedula = $titularPersona instanceof PersonasModel
            ? strtoupper((string) $titularPersona->nacionalidad) . '-' . (string) $titularPersona->cedula
            : '';
        $titularCargo = trim(
            ($titular?->cargo?->cargo ?? '')
            . ($titular?->departamento?->departamento !== null ? ' · ' . $titular->departamento->departamento : '')
        );

        $generoActual = (string) ($familiar->persona->genero !== '' ? $familiar->persona->genero : ($familiar->genero ?? ''));

        $this->renderVista('editar_familiar', [
            'familiar' => $familiar,
            'persona' => $familiar->persona,
            'titular' => (object) [
                'empleado_id' => $titular?->empleado_id,
                'nombre' => $titularNombre,
                'cedula' => $titularCedula,
                'cargo' => $titularCargo,
            ],
            'parentescos' => $this->catalogos->tiposParentesco(),
            'recaudos' => $this->catalogos->recaudosParaFamiliar(
                (int) $familiar->parentesco_id,
                (int) $familiar->persona_id,
                (int) ($titularPersona?->persona_id ?? 0)
            ),
            'generos' => $this->catalogos->generos($generoActual),
            'genero_actual' => $generoActual,
        ]);
    }

    public function update(string $id): void
    {
        $familiaId = $this->idPositivo($id, 'familiar');
        if ($familiaId === null) {
            return;
        }

        $familiar = FamiliaresModel::query()->with('persona')->find($familiaId);
        if (!$familiar instanceof FamiliaresModel || !$familiar->persona instanceof PersonasModel) {
            $this->jsonError('Familiar no encontrado.', 404);
            return;
        }

        $input = new LimpiarInputs();
        $personaActual = $familiar->persona;

        $nacionalidad = substr(strtoupper((string) $input->get('nacionalidad', $personaActual->nacionalidad)), 0, 1);
        $cedula = (int) $input->get('cedula', (string) $personaActual->cedula);

        if (!in_array($nacionalidad, ['V', 'E'], true) || $cedula <= 0) {
            $this->jsonError('Nacionalidad o cédula del familiar inválida.', 422);
            return;
        }

        $duplicada = PersonasModel::query()
            ->where('nacionalidad', $nacionalidad)
            ->where('cedula', $cedula)
            ->where('persona_id', '!=', (int) $personaActual->persona_id)
            ->exists();

        if ($duplicada) {
            $this->jsonError('Ya existe otra persona con esa nacionalidad y cédula.', 422);
            return;
        }

        $parentescoId = (int) $input->get('parentesco_id', (string) $familiar->parentesco_id);
        $estadoCivilRaw = $input->get('estado_civil_id', $personaActual->estado_civil_id);
        $estadoCivilId = is_numeric($estadoCivilRaw) && (int) $estadoCivilRaw > 0 ? (int) $estadoCivilRaw : null;

        $primerNombre = substr((string) $input->get('primer_nombre', $personaActual->primer_nombre), 0, 100);
        $segundoNombre = substr((string) $input->get('segundo_nombre', $personaActual->segundo_nombre), 0, 100);
        $primerApellido = substr((string) $input->get('primer_apellido', $personaActual->primer_apellido), 0, 100);
        $segundoApellido = substr((string) $input->get('segundo_apellido', $personaActual->segundo_apellido), 0, 100);
        $fechaNacimiento = (string) $input->get('fecha_nacimiento', $personaActual->fecha_nacimiento?->format('Y-m-d') ?? '');
        $genero = substr((string) $input->get('genero', $personaActual->genero), 0, 20);
        $tomo = (int) $input->get('tomo', (string) $familiar->tomo);
        $folio = (int) $input->get('folio', (string) $familiar->folio);
        $documentosPorTipo = CatalogosService::normalizarArchivosPorTipo($this->app->request()->files->getData());

        try {
            $documentosIds = $familiar->getConnection()->transaction(function () use (
                $familiar,
                $personaActual,
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
                $documentosPorTipo
            ): array {
                $personaActual->fill([
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
                $personaActual->save();

                $familiar->fill([
                    'parentesco_id' => $parentescoId,
                    'tomo' => $tomo,
                    'folio' => $folio,
                    'genero' => $genero,
                ]);
                $familiar->save();

                $procesados = [];
                if ($this->gestorDocumentos !== null) {
                    foreach ($documentosPorTipo as $tipoDocId => $archivoDoc) {
                        $nuevo = $this->gestorDocumentos->subir(
                            $archivoDoc,
                            (int) $tipoDocId,
                            (int) $familiar->empleado_id,
                            (int) $familiar->familia_id
                        );
                        $procesados[] = (int) $nuevo->documentos_id;
                    }
                }

                return $procesados;
            });

            $this->jsonOk([
                'familia_id' => (int) $familiar->familia_id,
                'documentos_ids' => $documentosIds,
            ], 'Familiar actualizado correctamente.');
        } catch (Throwable $e) {
            $codigo = (int) $e->getCode();
            $this->jsonError(
                $codigo >= 400 && $codigo < 500 ? $e->getMessage() : 'No se pudo actualizar el familiar.',
                $codigo >= 400 && $codigo < 600 ? $codigo : 500
            );
        }
    }
}
