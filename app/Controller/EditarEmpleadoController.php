<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\Personas\EmpleadosModel;
use App\Model\Personas\PersonasModel;
use App\Services\CatalogosService;
use App\Services\ContextoSesion;
use App\Utils\Config;
use flight\Engine;

/**
 * Controlador para la vista de edición de empleado.
 */
class EditarEmpleadoController
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
     * [GET] Renderiza el formulario de edición de empleado con datos y catálogos preseleccionados desde Eloquent.
     */
    public function render(?string $id = null): void
    {
        $idRaw = $id ?? ($this->app->request()->query['id'] ?? null);
        if (!is_numeric($idRaw) || (int) $idRaw <= 0) {
            $this->app->redirect('/empleado/registros');
            return;
        }

        $empleadoId = (int) $idRaw;
        $empleado = EmpleadosModel::query()
            ->with([
                'persona.estadoCivil',
                'persona.telefonos',
                'persona.documentos.tipoDocumento',
                'direccion.parroquia.municipio.estado',
                'dependencia',
                'departamento',
                'cargo',
                'estatus',
                'nivelAcademico',
                'carrera',
            ])
            ->find($empleadoId);

        if (!$empleado instanceof EmpleadosModel || !$empleado->persona instanceof PersonasModel) {
            $this->app->halt(404, 'Empleado no encontrado');
            return;
        }

        $persona = $empleado->persona;
        $telPrincipal = $persona->telefonos->firstWhere('emergencia', false);
        $telEmergencia = $persona->telefonos->firstWhere('emergencia', true);

        $this->app->render('editar_empleado', [
            'env' => $this->config->env(),
            'debug' => $this->config->isDebug(),
            'usuario' => (object) ContextoSesion::todo(),
            'empleado' => $empleado,
            'persona' => $persona,
            'direccion' => $empleado->direccion,
            'telefono_principal' => $telPrincipal !== null ? (string) $telPrincipal->numero_telefono : '',
            'telefono_emergencia' => $telEmergencia !== null ? (string) $telEmergencia->numero_telefono : '',
            'documentos' => $persona->documentos,
            'estados_civiles' => $this->catalogos->estadosCiviles(),
            'dependencias' => $this->catalogos->dependencias(false),
            'departamentos' => $this->catalogos->departamentos(false),
            'cargos' => $this->catalogos->cargos(false),
            'estatus' => $this->catalogos->estatus(false),
            'niveles_academicos' => $this->catalogos->nivelesAcademicos(),
            'carreras' => $this->catalogos->carreras(),
            'parroquias' => $this->catalogos->parroquiasConUbicacion(),
            'tipos_documentos' => $this->catalogos->tiposDocumentos(),
            'generos' => $this->catalogos->generos((string) $persona->genero),
        ]);
    }
}
