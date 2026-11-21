<?php

declare(strict_types=1);

namespace App\Controller;

use App\Http\LimpiarInputs;
use App\Model\Personas\EmpleadosModel;
use App\Model\Personas\FamiliaresModel;
use App\Model\Personas\PersonasModel;
use App\Services\CatalogosService;
use App\Services\ContextoSesion;
use App\Services\GestorDocumentos;
use App\Utils\Config;
use flight\Engine;
use Throwable;

/**
 * Controlador general para la vista y actualización de carga familiar.
 */
class EditarFamiliarController
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
     * [GET] Renderiza el formulario de edición de familiar con datos y catálogos desde Eloquent.
     */
    public function render(?string $id = null): void
    {
        $idRaw = $id ?? ($this->app->request()->query['id'] ?? null);
        if (!is_numeric($idRaw) || (int) $idRaw <= 0) {
            $this->app->redirect('/empleado/registros/familiar');
            return;
        }

        $familiaId = (int) $idRaw;
        $familiar = FamiliaresModel::query()
            ->with([
                'persona',
                'empleado.persona',
                'empleado.cargo',
                'empleado.departamento',
            ])
            ->find($familiaId);

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

        $this->app->render('editar_familiar', [
            'env' => $this->config->env(),
            'debug' => $this->config->isDebug(),
            'usuario' => (object) ContextoSesion::todo(),
            'familiar' => $familiar,
            'persona' => $familiar->persona,
            'titular' => (object) [
                'empleado_id' => $titular?->empleado_id,
                'nombre' => $titularNombre,
                'cedula' => $titularCedula,
                'cargo' => $titularCargo,
            ],
            'parentescos' => $this->catalogos->tiposParentesco(),
            'generos' => $this->catalogos->generos($generoActual),
            'genero_actual' => $generoActual,
        ]);
    }

    /**
     * [PUT|POST] Actualiza un familiar usando LimpiarInputs con nacionalidad y cédula separadas.
     */
    public function update(string $id): void
    {
        if (!ctype_digit($id) || (int) $id <= 0) {
            $this->app->json(['ok' => false, 'message' => 'Identificador de familiar inválido.'], 400);
            return;
        }

        $familiar = FamiliaresModel::query()->with('persona')->find((int) $id);
        if (!$familiar instanceof FamiliaresModel || !$familiar->persona instanceof PersonasModel) {
            $this->app->json(['ok' => false, 'message' => 'Familiar no encontrado.'], 404);
            return;
        }

        $input = new LimpiarInputs();
        $personaActual = $familiar->persona;

        // Nacionalidad y cédula separadas
        $nacionalidad = substr(strtoupper((string) $input->get('nacionalidad', $personaActual->nacionalidad)), 0, 1);
        $cedula = (int) $input->get('cedula', (string) $personaActual->cedula);

        if (!in_array($nacionalidad, ['V', 'E'], true) || $cedula <= 0) {
            $this->app->json(['ok' => false, 'message' => 'Nacionalidad o cédula del familiar inválida.'], 422);
            return;
        }

        $duplicada = PersonasModel::query()
            ->where('nacionalidad', $nacionalidad)
            ->where('cedula', $cedula)
            ->where('persona_id', '!=', (int) $personaActual->persona_id)
            ->exists();

        if ($duplicada) {
            $this->app->json(['ok' => false, 'message' => 'Ya existe otra persona con esa nacionalidad y cédula.'], 422);
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

        try {
            $familiar->getConnection()->transaction(function () use (
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
                $folio
            ): void {
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
            });

            $this->app->json([
                'ok' => true,
                'message' => 'Familiar actualizado correctamente.',
                'data' => ['familia_id' => (int) $familiar->familia_id],
            ], 200);
        } catch (Throwable) {
            $this->app->json(['ok' => false, 'message' => 'No se pudo actualizar el familiar.'], 500);
        }
    }
}
