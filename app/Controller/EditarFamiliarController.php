<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\Personas\EmpleadosModel;
use App\Model\Personas\FamiliaresModel;
use App\Model\Personas\PersonasModel;
use App\Services\CatalogosService;
use App\Services\ContextoSesion;
use App\Utils\Config;
use flight\Engine;

/**
 * Controlador para la vista de edición de familiar.
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
                'persona.estadoCivil',
                'persona.documentos.tipoDocumento',
                'tipoParentesco',
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
            'documentos' => $familiar->persona->documentos,
            'parentescos' => $this->catalogos->tiposParentesco(),
            'estados_civiles' => $this->catalogos->estadosCiviles(),
            'tipos_documentos' => $this->catalogos->tiposDocumentos(),
            'generos' => $this->catalogos->generos($generoActual),
            'genero_actual' => $generoActual,
        ]);
    }
}
