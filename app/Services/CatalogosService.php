<?php

declare(strict_types=1);

namespace App\Services;

use App\Model\Asistencia\TipoInstitucionEmisoraModel;
use App\Model\Asistencia\TipoPermisosModel;
use App\Model\Asistencia\TipoRepososModel;
use App\Model\Beneficios\GradoDiscapacidadModel;
use App\Model\Beneficios\TipoBeneficiosModel;
use App\Model\Beneficios\TipoDiscapacidadModel;
use App\Model\Catalogos\CargoModel;
use App\Model\Catalogos\CarreraModel;
use App\Model\Catalogos\DepartamentosModel;
use App\Model\Catalogos\DependenciasModel;
use App\Model\Catalogos\EstadoCivilModel;
use App\Model\Catalogos\EstatusModel;
use App\Model\Catalogos\NivelAcademicosModel;
use App\Model\Catalogos\TipoDocumentosModel;
use App\Model\Catalogos\TipoParentescoModel;
use App\Model\Personas\EmpleadosModel;
use App\Model\Personas\FamiliaresModel;
use App\Model\Personas\PersonasModel;
use App\Model\Ubicacion\EstadosModel;
use App\Model\Ubicacion\MunicipiosModel;
use App\Model\Ubicacion\ParroquiasModel;

/**
 * Servicio centralizado de consulta de catálogos mediante Eloquent ORM.
 * Reutiliza los modelos y métodos de `app/Model/` para hidratar vistas BladeOne y APIs.
 */
final class CatalogosService
{
    /**
     * @return array<int|string, string>
     */
    public function estatus(bool $soloActivos = true): array
    {
        if (!$soloActivos) {
            return EstatusModel::todoDatosEstatus();
        }

        /** @var array<int|string, string> */
        return EstatusModel::query()
            ->where('activo', true)
            ->orderBy('estatus')
            ->pluck('estatus', 'estatus_id')
            ->all();
    }

    /**
     * @return array<int|string, string>
     */
    public function estadosCiviles(): array
    {
        /** @var array<int|string, string> */
        return EstadoCivilModel::query()
            ->orderBy('estado_civil')
            ->pluck('estado_civil', 'estado_civil_id')
            ->all();
    }

    /**
     * @return array<int|string, string>
     */
    public function cargos(bool $soloActivos = true): array
    {
        $query = CargoModel::query()->orderBy('cargo');
        if ($soloActivos) {
            $query->where('activo', true);
        }

        /** @var array<int|string, string> */
        return $query->pluck('cargo', 'cargo_id')->all();
    }

    /**
     * @return array<int|string, string>
     */
    public function departamentos(bool $soloActivos = true): array
    {
        $query = DepartamentosModel::query()->orderBy('departamento');
        if ($soloActivos) {
            $query->where('activo', true);
        }

        /** @var array<int|string, string> */
        return $query->pluck('departamento', 'departamento_id')->all();
    }

    /**
     * @return array<int|string, string>|list<array{value: string, label: string, sublabel: string}>
     */
    public function dependencias(bool $soloActivos = true, bool $conSubetiqueta = true): array
    {
        $query = DependenciasModel::query()->orderBy('dependencia');
        if ($soloActivos) {
            $query->where('activo', true);
        }

        if (!$conSubetiqueta) {
            /** @var array<int|string, string> */
            return $query->pluck('dependencia', 'dependencia_id')->all();
        }

        return $query->get()
            ->map(static function (DependenciasModel $dep): array {
                return [
                    'value' => (string) $dep->dependencia_id,
                    'label' => (string) $dep->dependencia,
                    'sublabel' => (string) ($dep->codigo ?? ''),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array<int|string, string>
     */
    public function nivelesAcademicos(): array
    {
        /** @var array<int|string, string> */
        return NivelAcademicosModel::query()
            ->orderBy('jerarquia')
            ->orderBy('nivel_academico')
            ->pluck('nivel_academico', 'nivel_academico_id')
            ->all();
    }

    /**
     * @return array<int|string, string>
     */
    public function carreras(): array
    {
        /** @var array<int|string, string> */
        return CarreraModel::query()
            ->orderBy('carrera')
            ->pluck('carrera', 'carrera_id')
            ->all();
    }

    /**
     * @return array<int|string, string>
     */
    public function tiposParentesco(): array
    {
        /** @var array<int|string, string> */
        return TipoParentescoModel::query()
            ->orderBy('parentesco')
            ->pluck('parentesco', 'parentesco_id')
            ->all();
    }

    /**
     * @return array<int|string, string>
     */
    public function tiposDocumentos(): array
    {
        /** @var array<int|string, string> */
        return TipoDocumentosModel::query()
            ->orderBy('tpdocumentos_id')
            ->pluck('tipo_documentos', 'tpdocumentos_id')
            ->all();
    }

    /**
     * @return array<int|string, string>
     */
    public function tiposDiscapacidad(): array
    {
        /** @var array<int|string, string> */
        return TipoDiscapacidadModel::query()
            ->orderBy('tipo_discapacidad')
            ->pluck('tipo_discapacidad', 'tpdiscapacidad_id')
            ->all();
    }

    /**
     * @return array<int|string, string>
     */
    public function gradosDiscapacidad(): array
    {
        /** @var array<int|string, string> */
        return GradoDiscapacidadModel::query()
            ->orderBy('gradodisca_id')
            ->pluck('nombre_gradodis', 'gradodisca_id')
            ->all();
    }

    /**
     * @return array<int|string, string>
     */
    public function estados(): array
    {
        /** @var array<int|string, string> */
        return EstadosModel::query()
            ->orderBy('estado')
            ->pluck('estado', 'estado_id')
            ->all();
    }

    /**
     * @return array<int|string, string>
     */
    public function municipiosPorEstado(int $estadoId): array
    {
        if ($estadoId <= 0) {
            return [];
        }

        /** @var array<int|string, string> */
        return MunicipiosModel::query()
            ->where('estado_id', $estadoId)
            ->orderBy('municipio')
            ->pluck('municipio', 'municipio_id')
            ->all();
    }

    /**
     * @return array<int|string, string>
     */
    public function parroquiasPorMunicipio(int $municipioId): array
    {
        if ($municipioId <= 0) {
            return [];
        }

        /** @var array<int|string, string> */
        return ParroquiasModel::query()
            ->where('municipio_id', $municipioId)
            ->orderBy('parroquia')
            ->pluck('parroquia', 'parroquia_id')
            ->all();
    }

    /**
     * @return list<array{value: string, label: string, sublabel: string}>
     */
    public function parroquiasConUbicacion(): array
    {
        return ParroquiasModel::query()
            ->with('municipio.estado')
            ->orderBy('parroquia')
            ->get()
            ->map(static function (ParroquiasModel $parroquia): array {
                $municipio = $parroquia->municipio;
                $estado = $municipio?->estado;
                $ubicacion = array_filter([
                    $municipio?->municipio,
                    $estado?->estado,
                ]);

                return [
                    'value' => (string) $parroquia->parroquia_id,
                    'label' => (string) $parroquia->parroquia,
                    'sublabel' => implode(' · ', $ubicacion),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array<int|string, string>
     */
    public function tiposPermiso(): array
    {
        /** @var array<int|string, string> */
        return TipoPermisosModel::query()
            ->orderBy('permiso')
            ->pluck('permiso', 'tppermiso_id')
            ->all();
    }

    /**
     * @return array<int|string, string>
     */
    public function tiposReposo(): array
    {
        /** @var array<int|string, string> */
        return TipoRepososModel::query()
            ->orderBy('reposo')
            ->pluck('reposo', 'tpreposo_id')
            ->all();
    }

    /**
     * @return array<int|string, string>
     */
    public function tiposInstitucionEmisora(): array
    {
        /** @var array<int|string, string> */
        return TipoInstitucionEmisoraModel::query()
            ->orderBy('institucion')
            ->pluck('institucion', 'tpinstitucion_emisora_id')
            ->all();
    }

    /**
     * @return array<int|string, string>
     */
    public function tiposBeneficio(): array
    {
        /** @var array<int|string, string> */
        return TipoBeneficiosModel::query()
            ->orderBy('tipo_beneficio')
            ->pluck('tipo_beneficio', 'tpbeneficio_id')
            ->all();
    }

    /**
     * Listado de empleados activos (con metadatos de cédula) para hidratar selects de PHP.
     *
     * @return list<array{value: string, label: string, sublabel: string}>
     */
    public function empleadosActivosParaSelect(): array
    {
        $items = EmpleadosModel::query()
            ->with('persona')
            ->where('estatus_actual', 1)
            ->get()
            ->map(static function (EmpleadosModel $empleado): array {
                $persona = $empleado->persona;
                $nombre = self::nombreCompletoPersona($persona);
                $cedula = $persona instanceof PersonasModel
                    ? strtoupper((string) $persona->nacionalidad) . '-' . (string) $persona->cedula
                    : (string) ($empleado->codigo_empleado ?? '');

                return [
                    'value' => (string) $empleado->empleado_id,
                    'label' => $nombre !== '' ? $nombre : "Empleado #{$empleado->empleado_id}",
                    'sublabel' => $cedula,
                ];
            })
            ->all();

        usort($items, static fn (array $a, array $b): int => strcmp($a['label'], $b['label']));

        return array_values($items);
    }

    /**
     * Listado de personas (trabajadores y familiares) para selects de beneficios.
     *
     * @return list<array{value: string, label: string, sublabel: string}>
     */
    public function personasParaSelect(): array
    {
        return PersonasModel::query()
            ->with(['empleado', 'familiares.tipoParentesco'])
            ->orderBy('primer_apellido')
            ->orderBy('primer_nombre')
            ->get()
            ->map(static function (PersonasModel $persona): array {
                $cedula = strtoupper((string) $persona->nacionalidad) . '-' . (string) $persona->cedula;
                $rol = 'Persona';
                if ($persona->empleado instanceof EmpleadosModel) {
                    $rol = 'Titular';
                } else {
                    $primerFamiliar = $persona->familiares->first();
                    if ($primerFamiliar instanceof FamiliaresModel && $primerFamiliar->tipoParentesco !== null) {
                        $rol = (string) $primerFamiliar->tipoParentesco->parentesco;
                    }
                }

                return [
                    'value' => (string) $persona->persona_id,
                    'label' => self::nombreCompletoPersona($persona),
                    'sublabel' => "{$cedula} · {$rol}",
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Opciones de género preservando un valor histórico guardado si difiere del formato estándar.
     *
     * @return array<string, string>
     */
    public function generos(?string $valorActual = null): array
    {
        $base = [
            'Masculino' => 'Masculino',
            'Femenino' => 'Femenino',
        ];

        $valor = trim((string) ($valorActual ?? ''));
        if ($valor !== '' && !isset($base[$valor])) {
            $etiqueta = match (strtoupper($valor)) {
                'M' => 'Masculino (M)',
                'F' => 'Femenino (F)',
                default => $valor,
            };
            $base[$valor] = $etiqueta;
        }

        return $base;
    }

    public static function nombreCompletoPersona(?PersonasModel $persona): string
    {
        if (!$persona instanceof PersonasModel) {
            return '';
        }

        $partes = array_filter([
            trim((string) $persona->primer_nombre),
            trim((string) $persona->segundo_nombre),
            trim((string) $persona->primer_apellido),
            trim((string) $persona->segundo_apellido),
        ], static fn (string $parte): bool => $parte !== '');

        $unido = implode(' ', $partes);

        return function_exists('mb_convert_case')
            ? mb_convert_case($unido, MB_CASE_TITLE, 'UTF-8')
            : ucwords(strtolower($unido));
    }
}
