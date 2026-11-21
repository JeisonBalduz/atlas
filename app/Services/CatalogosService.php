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
use App\Model\Personas\PersonasModel;
use App\Model\Ubicacion\EstadosModel;
use App\Model\Ubicacion\MunicipiosModel;
use App\Model\Ubicacion\ParroquiasModel;
use Illuminate\Database\Query\Builder as QueryBuilder;

/**
 * Servicio centralizado de consulta de catálogos mediante Eloquent ORM.
 * Optimizado con proyecciones mínimas, consultas paginadas para selects remotos y memoización por petición.
 */
final class CatalogosService
{
    /** @var array<string, mixed> */
    private array $memo = [];

    /**
     * @return array<int|string, string>
     */
    public function estatus(bool $soloActivos = true): array
    {
        $clave = 'estatus_' . ($soloActivos ? '1' : '0');
        if (isset($this->memo[$clave])) {
            /** @var array<int|string, string> */
            return $this->memo[$clave];
        }

        if (!$soloActivos) {
            return $this->memo[$clave] = EstatusModel::todoDatosEstatus();
        }

        /** @var array<int|string, string> $datos */
        $datos = EstatusModel::query()
            ->where('activo', true)
            ->orderBy('estatus')
            ->pluck('estatus', 'estatus_id')
            ->all();

        return $this->memo[$clave] = $datos;
    }

    /**
     * @return array<int|string, string>
     */
    public function estadosCiviles(): array
    {
        if (isset($this->memo['estados_civiles'])) {
            /** @var array<int|string, string> */
            return $this->memo['estados_civiles'];
        }

        /** @var array<int|string, string> $datos */
        $datos = EstadoCivilModel::query()
            ->orderBy('estado_civil')
            ->pluck('estado_civil', 'estado_civil_id')
            ->all();

        return $this->memo['estados_civiles'] = $datos;
    }

    /**
     * @return array<int|string, string>
     */
    public function cargos(bool $soloActivos = true): array
    {
        $clave = 'cargos_' . ($soloActivos ? '1' : '0');
        if (isset($this->memo[$clave])) {
            /** @var array<int|string, string> */
            return $this->memo[$clave];
        }

        $query = CargoModel::query()->orderBy('cargo');
        if ($soloActivos) {
            $query->where('activo', true);
        }

        /** @var array<int|string, string> $datos */
        $datos = $query->pluck('cargo', 'cargo_id')->all();

        return $this->memo[$clave] = $datos;
    }

    /**
     * @return array<int|string, string>
     */
    public function departamentos(bool $soloActivos = true): array
    {
        $clave = 'departamentos_' . ($soloActivos ? '1' : '0');
        if (isset($this->memo[$clave])) {
            /** @var array<int|string, string> */
            return $this->memo[$clave];
        }

        $query = DepartamentosModel::query()->orderBy('departamento');
        if ($soloActivos) {
            $query->where('activo', true);
        }

        /** @var array<int|string, string> $datos */
        $datos = $query->pluck('departamento', 'departamento_id')->all();

        return $this->memo[$clave] = $datos;
    }

    /**
     * @return array<int|string, string>|list<array{value: string, label: string, sublabel: string}>
     */
    public function dependencias(bool $soloActivos = true, bool $conSubetiqueta = true): array
    {
        $clave = 'dependencias_' . ($soloActivos ? '1' : '0') . '_' . ($conSubetiqueta ? '1' : '0');
        if (isset($this->memo[$clave])) {
            /** @var array<int|string, string>|list<array{value: string, label: string, sublabel: string}> */
            return $this->memo[$clave];
        }

        $query = DependenciasModel::query()->orderBy('dependencia');
        if ($soloActivos) {
            $query->where('activo', true);
        }

        if (!$conSubetiqueta) {
            /** @var array<int|string, string> $datos */
            $datos = $query->pluck('dependencia', 'dependencia_id')->all();
            return $this->memo[$clave] = $datos;
        }

        $datos = $query->select(['dependencia_id', 'dependencia', 'codigo'])
            ->toBase()
            ->get()
            ->map(static fn (object $dep): array => [
                'value' => (string) $dep->dependencia_id,
                'label' => (string) $dep->dependencia,
                'sublabel' => (string) ($dep->codigo ?? ''),
            ])
            ->values()
            ->all();

        return $this->memo[$clave] = $datos;
    }

    /**
     * @return array<int|string, string>
     */
    public function nivelesAcademicos(): array
    {
        if (isset($this->memo['niveles'])) {
            /** @var array<int|string, string> */
            return $this->memo['niveles'];
        }

        /** @var array<int|string, string> $datos */
        $datos = NivelAcademicosModel::query()
            ->orderBy('jerarquia')
            ->orderBy('nivel_academico')
            ->pluck('nivel_academico', 'nivel_academico_id')
            ->all();

        return $this->memo['niveles'] = $datos;
    }

    /**
     * @return array<int|string, string>
     */
    public function carreras(): array
    {
        if (isset($this->memo['carreras'])) {
            /** @var array<int|string, string> */
            return $this->memo['carreras'];
        }

        /** @var array<int|string, string> $datos */
        $datos = CarreraModel::query()
            ->orderBy('carrera')
            ->pluck('carrera', 'carrera_id')
            ->all();

        return $this->memo['carreras'] = $datos;
    }

    /**
     * @return array<int|string, string>
     */
    public function tiposParentesco(): array
    {
        if (isset($this->memo['parentescos'])) {
            /** @var array<int|string, string> */
            return $this->memo['parentescos'];
        }

        /** @var array<int|string, string> $datos */
        $datos = TipoParentescoModel::query()
            ->orderBy('parentesco')
            ->pluck('parentesco', 'parentesco_id')
            ->all();

        return $this->memo['parentescos'] = $datos;
    }

    /**
     * @return array<int|string, string>
     */
    public function tiposDocumentos(): array
    {
        if (isset($this->memo['tipos_docs'])) {
            /** @var array<int|string, string> */
            return $this->memo['tipos_docs'];
        }

        /** @var array<int|string, string> $datos */
        $datos = TipoDocumentosModel::query()
            ->orderBy('tpdocumentos_id')
            ->pluck('tipo_documentos', 'tpdocumentos_id')
            ->all();

        return $this->memo['tipos_docs'] = $datos;
    }

    /**
     * @return array<int|string, string>
     */
    public function tiposDiscapacidad(): array
    {
        if (isset($this->memo['tipos_disc'])) {
            /** @var array<int|string, string> */
            return $this->memo['tipos_disc'];
        }

        /** @var array<int|string, string> $datos */
        $datos = TipoDiscapacidadModel::query()
            ->orderBy('tipo_discapacidad')
            ->pluck('tipo_discapacidad', 'tpdiscapacidad_id')
            ->all();

        return $this->memo['tipos_disc'] = $datos;
    }

    /**
     * @return array<int|string, string>
     */
    public function gradosDiscapacidad(): array
    {
        if (isset($this->memo['grados_disc'])) {
            /** @var array<int|string, string> */
            return $this->memo['grados_disc'];
        }

        /** @var array<int|string, string> $datos */
        $datos = GradoDiscapacidadModel::query()
            ->orderBy('gradodisca_id')
            ->pluck('nombre_gradodis', 'gradodisca_id')
            ->all();

        return $this->memo['grados_disc'] = $datos;
    }

    /**
     * @return array<int|string, string>
     */
    public function estados(): array
    {
        if (isset($this->memo['estados'])) {
            /** @var array<int|string, string> */
            return $this->memo['estados'];
        }

        /** @var array<int|string, string> $datos */
        $datos = EstadosModel::query()
            ->orderBy('estado')
            ->pluck('estado', 'estado_id')
            ->all();

        return $this->memo['estados'] = $datos;
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
            ->join('municipios', 'municipios.municipio_id', '=', 'parroquias.municipio_id')
            ->join('estados', 'estados.estado_id', '=', 'municipios.estado_id')
            ->select([
                'parroquias.parroquia_id',
                'parroquias.parroquia',
                'municipios.municipio',
                'estados.estado',
            ])
            ->orderBy('parroquias.parroquia')
            ->toBase()
            ->get()
            ->map(static function (object $fila): array {
                $ubicacion = array_filter([
                    (string) ($fila->municipio ?? ''),
                    (string) ($fila->estado ?? ''),
                ]);

                return [
                    'value' => (string) $fila->parroquia_id,
                    'label' => (string) $fila->parroquia,
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
        if (isset($this->memo['tipos_permiso'])) {
            /** @var array<int|string, string> */
            return $this->memo['tipos_permiso'];
        }

        /** @var array<int|string, string> $datos */
        $datos = TipoPermisosModel::query()
            ->orderBy('permiso')
            ->pluck('permiso', 'tppermiso_id')
            ->all();

        return $this->memo['tipos_permiso'] = $datos;
    }

    /**
     * @return array<int|string, string>
     */
    public function tiposReposo(): array
    {
        if (isset($this->memo['tipos_reposo'])) {
            /** @var array<int|string, string> */
            return $this->memo['tipos_reposo'];
        }

        /** @var array<int|string, string> $datos */
        $datos = TipoRepososModel::query()
            ->orderBy('reposo')
            ->pluck('reposo', 'tpreposo_id')
            ->all();

        return $this->memo['tipos_reposo'] = $datos;
    }

    /**
     * @return array<int|string, string>
     */
    public function tiposInstitucionEmisora(): array
    {
        if (isset($this->memo['tipos_institucion'])) {
            /** @var array<int|string, string> */
            return $this->memo['tipos_institucion'];
        }

        /** @var array<int|string, string> $datos */
        $datos = TipoInstitucionEmisoraModel::query()
            ->orderBy('institucion')
            ->pluck('institucion', 'tpinstitucion_emisora_id')
            ->all();

        return $this->memo['tipos_institucion'] = $datos;
    }

    /**
     * @return array<int|string, string>
     */
    public function tiposBeneficio(): array
    {
        if (isset($this->memo['tipos_beneficio'])) {
            /** @var array<int|string, string> */
            return $this->memo['tipos_beneficio'];
        }

        /** @var array<int|string, string> $datos */
        $datos = TipoBeneficiosModel::query()
            ->orderBy('tipo_beneficio')
            ->pluck('tipo_beneficio', 'tpbeneficio_id')
            ->all();

        return $this->memo['tipos_beneficio'] = $datos;
    }

    /**
     * Conteo rápido de trabajadores activos sin hidratar modelos ni cargar selects completos.
     */
    public function contarEmpleadosActivos(): int
    {
        return EmpleadosModel::query()->where('estatus_actual', 1)->count();
    }

    /**
     * Conteo rápido de personas elegibles (titulares y familiares) sin hidratar modelos.
     */
    public function contarPersonasElegibles(): int
    {
        return PersonasModel::query()->count();
    }

    /**
     * Obtiene un único trabajador formateado para preselección inicial de selects remotos.
     *
     * @return list<array{value: string, label: string, sublabel: string}>
     */
    public function empleadoParaSelectPorId(int $empleadoId): array
    {
        if ($empleadoId <= 0) {
            return [];
        }

        $fila = EmpleadosModel::query()
            ->join('personas', 'personas.persona_id', '=', 'empleados.persona_id')
            ->where('empleados.empleado_id', $empleadoId)
            ->select([
                'empleados.empleado_id',
                'empleados.codigo_empleado',
                'personas.primer_nombre',
                'personas.segundo_nombre',
                'personas.primer_apellido',
                'personas.segundo_apellido',
                'personas.nacionalidad',
                'personas.cedula',
            ])
            ->toBase()
            ->first();

        if ($fila === null) {
            return [];
        }

        return [self::formatearEmpleadoFila($fila)];
    }

    /**
     * Búsqueda paginada en servidor de trabajadores activos para selects remotos (máx. 50).
     *
     * @return list<array{value: string, label: string, sublabel: string}>
     */
    public function buscarEmpleadosActivosParaSelect(string $busqueda = '', int $limite = 30): array
    {
        $limiteSeguro = max(1, min($limite, 50));
        $query = EmpleadosModel::query()
            ->join('personas', 'personas.persona_id', '=', 'empleados.persona_id')
            ->where('empleados.estatus_actual', 1)
            ->select([
                'empleados.empleado_id',
                'empleados.codigo_empleado',
                'personas.primer_nombre',
                'personas.segundo_nombre',
                'personas.primer_apellido',
                'personas.segundo_apellido',
                'personas.nacionalidad',
                'personas.cedula',
            ]);

        $op = $query->getConnection()->getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';
        $terminos = preg_split('/\s+/', trim($busqueda)) ?: [];

        $baseQuery = $query->toBase();
        foreach ($terminos as $termino) {
            if ($termino === '') {
                continue;
            }
            $patron = '%' . $termino . '%';
            $baseQuery->where(static function (QueryBuilder $q) use ($patron, $termino, $op): void {
                $q->where('personas.primer_nombre', $op, $patron)
                    ->orWhere('personas.segundo_nombre', $op, $patron)
                    ->orWhere('personas.primer_apellido', $op, $patron)
                    ->orWhere('personas.segundo_apellido', $op, $patron)
                    ->orWhere('empleados.codigo_empleado', $op, $patron)
                    ->orWhereRaw("CAST(personas.cedula AS TEXT) {$op} ?", [$patron]);

                if (ctype_digit($termino)) {
                    $q->orWhere('personas.cedula', (int) $termino);
                }
            });
        }

        return $baseQuery
            ->orderBy('personas.primer_apellido')
            ->orderBy('personas.primer_nombre')
            ->limit($limiteSeguro)
            ->get()
            ->map(static fn (object $fila): array => self::formatearEmpleadoFila($fila))
            ->values()
            ->all();
    }

    /**
     * Listado completo de empleados activos optimizado con JOIN + proyección mínima.
     *
     * @return list<array{value: string, label: string, sublabel: string}>
     */
    public function empleadosActivosParaSelect(): array
    {
        $items = EmpleadosModel::query()
            ->join('personas', 'personas.persona_id', '=', 'empleados.persona_id')
            ->where('empleados.estatus_actual', 1)
            ->select([
                'empleados.empleado_id',
                'empleados.codigo_empleado',
                'personas.primer_nombre',
                'personas.segundo_nombre',
                'personas.primer_apellido',
                'personas.segundo_apellido',
                'personas.nacionalidad',
                'personas.cedula',
            ])
            ->toBase()
            ->get()
            ->map(static fn (object $fila): array => self::formatearEmpleadoFila($fila))
            ->all();

        usort($items, static fn (array $a, array $b): int => strcmp($a['label'], $b['label']));

        return array_values($items);
    }

    /**
     * Búsqueda paginada en servidor de personas (titulares y familiares) para selects remotos (máx. 50).
     *
     * @return list<array{value: string, label: string, sublabel: string}>
     */
    public function buscarPersonasParaSelect(string $busqueda = '', int $limite = 30): array
    {
        $limiteSeguro = max(1, min($limite, 50));
        $query = PersonasModel::query()
            ->leftJoin('empleados', 'empleados.persona_id', '=', 'personas.persona_id')
            ->leftJoin('familiares', 'familiares.persona_id', '=', 'personas.persona_id')
            ->leftJoin('tipo_parentesco', 'tipo_parentesco.parentesco_id', '=', 'familiares.parentesco_id')
            ->select([
                'personas.persona_id',
                'personas.primer_nombre',
                'personas.segundo_nombre',
                'personas.primer_apellido',
                'personas.segundo_apellido',
                'personas.nacionalidad',
                'personas.cedula',
                'empleados.empleado_id',
                'tipo_parentesco.parentesco',
            ]);

        $op = $query->getConnection()->getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';
        $terminos = preg_split('/\s+/', trim($busqueda)) ?: [];

        $baseQuery = $query->toBase();
        foreach ($terminos as $termino) {
            if ($termino === '') {
                continue;
            }
            $patron = '%' . $termino . '%';
            $baseQuery->where(static function (QueryBuilder $q) use ($patron, $termino, $op): void {
                $q->where('personas.primer_nombre', $op, $patron)
                    ->orWhere('personas.segundo_nombre', $op, $patron)
                    ->orWhere('personas.primer_apellido', $op, $patron)
                    ->orWhere('personas.segundo_apellido', $op, $patron)
                    ->orWhereRaw("CAST(personas.cedula AS TEXT) {$op} ?", [$patron]);

                if (ctype_digit($termino)) {
                    $q->orWhere('personas.cedula', (int) $termino);
                }
            });
        }

        return $baseQuery
            ->orderBy('personas.primer_apellido')
            ->orderBy('personas.primer_nombre')
            ->limit($limiteSeguro)
            ->get()
            ->map(static fn (object $fila): array => self::formatearPersonaFila($fila))
            ->unique('value')
            ->values()
            ->all();
    }

    /**
     * Listado completo de personas optimizado con JOIN + proyección mínima.
     *
     * @return list<array{value: string, label: string, sublabel: string}>
     */
    public function personasParaSelect(): array
    {
        return PersonasModel::query()
            ->leftJoin('empleados', 'empleados.persona_id', '=', 'personas.persona_id')
            ->leftJoin('familiares', 'familiares.persona_id', '=', 'personas.persona_id')
            ->leftJoin('tipo_parentesco', 'tipo_parentesco.parentesco_id', '=', 'familiares.parentesco_id')
            ->select([
                'personas.persona_id',
                'personas.primer_nombre',
                'personas.segundo_nombre',
                'personas.primer_apellido',
                'personas.segundo_apellido',
                'personas.nacionalidad',
                'personas.cedula',
                'empleados.empleado_id',
                'tipo_parentesco.parentesco',
            ])
            ->orderBy('personas.primer_apellido')
            ->orderBy('personas.primer_nombre')
            ->toBase()
            ->get()
            ->map(static fn (object $fila): array => self::formatearPersonaFila($fila))
            ->unique('value')
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

        return self::formatearNombre(
            (string) $persona->primer_nombre,
            (string) $persona->segundo_nombre,
            (string) $persona->primer_apellido,
            (string) $persona->segundo_apellido
        );
    }

    /**
     * @return array{value: string, label: string, sublabel: string}
     */
    private static function formatearEmpleadoFila(object $fila): array
    {
        $nombre = self::formatearNombre(
            (string) ($fila->primer_nombre ?? ''),
            (string) ($fila->segundo_nombre ?? ''),
            (string) ($fila->primer_apellido ?? ''),
            (string) ($fila->segundo_apellido ?? '')
        );
        $cedula = isset($fila->cedula)
            ? strtoupper((string) ($fila->nacionalidad ?? 'V')) . '-' . (string) $fila->cedula
            : (string) ($fila->codigo_empleado ?? '');

        return [
            'value' => (string) $fila->empleado_id,
            'label' => $nombre !== '' ? $nombre : "Empleado #{$fila->empleado_id}",
            'sublabel' => $cedula,
        ];
    }

    /**
     * @return array{value: string, label: string, sublabel: string}
     */
    private static function formatearPersonaFila(object $fila): array
    {
        $cedula = strtoupper((string) ($fila->nacionalidad ?? 'V')) . '-' . (string) ($fila->cedula ?? '');
        $rol = 'Persona';
        if (!empty($fila->empleado_id)) {
            $rol = 'Titular';
        } elseif (!empty($fila->parentesco)) {
            $rol = (string) $fila->parentesco;
        }

        $nombre = self::formatearNombre(
            (string) ($fila->primer_nombre ?? ''),
            (string) ($fila->segundo_nombre ?? ''),
            (string) ($fila->primer_apellido ?? ''),
            (string) ($fila->segundo_apellido ?? '')
        );

        return [
            'value' => (string) $fila->persona_id,
            'label' => $nombre,
            'sublabel' => "{$cedula} · {$rol}",
        ];
    }

    private static function formatearNombre(string $pNom, string $sNom, string $pApe, string $sApe): string
    {
        $partes = array_filter([
            trim($pNom),
            trim($sNom),
            trim($pApe),
            trim($sApe),
        ], static fn (string $parte): bool => $parte !== '');

        $unido = implode(' ', $partes);

        return function_exists('mb_convert_case')
            ? mb_convert_case($unido, MB_CASE_TITLE, 'UTF-8')
            : ucwords(strtolower($unido));
    }
}
