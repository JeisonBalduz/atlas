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
use App\Model\Personas\DocumentosModel;
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

    /**
     * Resuelve los recaudos documentales de un trabajador (obligatorios y opcionales)
     * según Contrato Colectivo, Procesos RRHH y `tipo_documentos`, cruzando con `documentos` vigentes.
     *
     * @return list<array{
     *     tipo_documento_id: int,
     *     nombre: string,
     *     obligatorio: bool,
     *     condicion: string,
     *     grupo: string,
     *     cargado: bool,
     *     documento: array{documentos_id: int, nombre_original: string, url_descarga: string}|null
     * }>
     */
    public function recaudosParaEmpleado(int $estadoCivilId, int $nivelAcademicoId, int $personaId = 0): array
    {
        $tipos = $this->tiposDocumentos();
        $vigentes = $this->documentosVigentesPorPersona($personaId);

        // Generales obligatorios (IDs existentes + los nuevos cuando estén en tipo_documentos)
        $reglas = [
            ['id' => 1, 'obligatorio' => true, 'condicion' => 'Obligatorio para todo trabajador', 'grupo' => 'general'],
            ['id' => 2, 'obligatorio' => true, 'condicion' => 'Obligatorio para todo trabajador', 'grupo' => 'general'],
            ['id' => 15, 'obligatorio' => true, 'condicion' => 'Constancia de trabajo / solicitud de empleo', 'grupo' => 'general'],
        ];

        $extrasObligatorios = [
            ['palabras' => ['curriculum', 'currículum'], 'condicion' => 'Ingreso de personal (Procesos RRHH)'],
            ['palabras' => ['rif', 'informacion fiscal', 'información fiscal'], 'condicion' => 'Ingreso y Prima de Transporte'],
            ['palabras' => ['referencia bancaria', 'bdt'], 'condicion' => 'Ingreso de personal (Procesos RRHH)'],
            ['palabras' => ['contrato laboral', 'nombramiento'], 'condicion' => 'Formalización de ingreso'],
            ['palabras' => ['notificacion de ingreso', 'notificación de ingreso', 'designacion', 'designación'], 'condicion' => 'Formalización de ingreso'],
            ['palabras' => ['14-04', 'asegurado ivss'], 'condicion' => 'Registro de asegurado IVSS'],
            ['palabras' => ['afiliacion hcm', 'afiliación hcm'], 'condicion' => 'Inclusión titular HCM'],
        ];
        foreach ($extrasObligatorios as $extra) {
            $idExtra = $this->buscarTipoPorPalabras($tipos, $extra['palabras']);
            if ($idExtra !== null) {
                $reglas[] = ['id' => $idExtra, 'obligatorio' => true, 'condicion' => $extra['condicion'], 'grupo' => 'general'];
            }
        }

        // Condicionales por Estado Civil (Cláusula 45)
        if ($estadoCivilId === 2) {
            $reglas[] = ['id' => 4, 'obligatorio' => true, 'condicion' => 'Estado civil: Casado/a (Cláusula 45)', 'grupo' => 'estado_civil'];
        } elseif ($estadoCivilId === 5) {
            $reglas[] = ['id' => 5, 'obligatorio' => true, 'condicion' => 'Estado civil: Unión Estable de Hecho (Cláusula 45)', 'grupo' => 'estado_civil'];
        }

        // Condicionales por Nivel Académico (Cláusula 24 - Prima de Profesionalización)
        if ($nivelAcademicoId >= 4) {
            $reglas[] = ['id' => 8, 'obligatorio' => true, 'condicion' => 'Nivel TSU / Universitario / Ingeniería (Cláusula 24)', 'grupo' => 'academico'];
        }
        if ($nivelAcademicoId >= 7) {
            $reglas[] = ['id' => 9, 'obligatorio' => true, 'condicion' => 'Especialización / Maestría / Doctorado (Cláusula 24)', 'grupo' => 'academico'];
        }

        // Opcionales / complementarios
        $reglas[] = ['id' => 13, 'obligatorio' => false, 'condicion' => 'Opcional · Caja de Ahorros', 'grupo' => 'opcional'];
        $idCursos = $this->buscarTipoPorPalabras($tipos, ['cursos realizados', 'certificados de cursos']);
        if ($idCursos !== null) {
            $reglas[] = ['id' => $idCursos, 'obligatorio' => false, 'condicion' => 'Opcional · Cursos realizados', 'grupo' => 'opcional'];
        }

        return $this->construirListaRecaudos($reglas, $tipos, $vigentes);
    }

    /**
     * Indica si el trabajador tiene cargados todos los recaudos obligatorios según su perfil.
     */
    public function recaudosObligatoriosCompletosEmpleado(int $estadoCivilId, int $nivelAcademicoId, int $personaId): bool
    {
        $recaudos = $this->recaudosParaEmpleado($estadoCivilId, $nivelAcademicoId, $personaId);
        foreach ($recaudos as $item) {
            if ($item['obligatorio'] && !$item['cargado']) {
                return false;
            }
        }

        return $recaudos !== [];
    }

    /**
     * Resuelve los recaudos documentales de un familiar según `parentesco_id` y Contrato Colectivo.
     *
     * @return list<array{
     *     tipo_documento_id: int,
     *     nombre: string,
     *     obligatorio: bool,
     *     condicion: string,
     *     grupo: string,
     *     cargado: bool,
     *     documento: array{documentos_id: int, nombre_original: string, url_descarga: string}|null
     * }>
     */
    public function recaudosParaFamiliar(int $parentescoId, int $personaFamiliarId = 0, int $personaTitularId = 0): array
    {
        $tipos = $this->tiposDocumentos();
        $vigentesFamiliar = $this->documentosVigentesPorPersona($personaFamiliarId);
        $vigentesTitular = $personaTitularId > 0 ? $this->documentosVigentesPorPersona($personaTitularId) : [];

        $reglas = [];

        if (in_array($parentescoId, [3, 4], true)) {
            // Hijo / Hija
            $reglas[] = ['id' => 3, 'obligatorio' => true, 'condicion' => 'Obligatorio · Filiación de hijo/hija', 'grupo' => 'familiar'];
            $reglas[] = ['id' => 1, 'obligatorio' => false, 'condicion' => 'Opcional si es menor sin cédula', 'grupo' => 'familiar'];
            $reglas[] = ['id' => 6, 'obligatorio' => false, 'condicion' => 'Condicional · Estudios / Becas / HCM (hasta 27 años)', 'grupo' => 'familiar'];
            $idExpensas = $this->buscarTipoPorPalabras($tipos, ['expensas', 'dependencia economica', 'dependencia económica']);
            if ($idExpensas !== null) {
                $reglas[] = ['id' => $idExpensas, 'obligatorio' => false, 'condicion' => 'Alternativa a constancia de estudios (HCM)', 'grupo' => 'familiar'];
            }
            $reglas[] = ['id' => 10, 'obligatorio' => false, 'condicion' => 'Condicional · Discapacidad sin límite de edad (Cláusula 49)', 'grupo' => 'familiar'];
            $reglas[] = ['id' => 14, 'obligatorio' => false, 'condicion' => 'Condicional · Sentencia de adopción o tutela (Cláusula 46)', 'grupo' => 'familiar'];
        } elseif (in_array($parentescoId, [1, 2], true)) {
            // Madre / Padre
            $reglas[] = ['id' => 1, 'obligatorio' => true, 'condicion' => 'Obligatorio · Cédula del padre/madre', 'grupo' => 'familiar'];
            $reglas[] = ['id' => 2, 'obligatorio' => true, 'condicion' => 'Filiación con el trabajador titular (HCM)', 'grupo' => 'titular'];
        } elseif ($parentescoId === 6) {
            // Cónyuge
            $reglas[] = ['id' => 1, 'obligatorio' => true, 'condicion' => 'Obligatorio · Cédula del cónyuge o pareja', 'grupo' => 'familiar'];
            $reglas[] = ['id' => 4, 'obligatorio' => false, 'condicion' => 'Requerido si existe matrimonio civil (Cláusula 45)', 'grupo' => 'familiar'];
            $reglas[] = ['id' => 5, 'obligatorio' => false, 'condicion' => 'Requerido si es Unión Estable de Hecho (Cláusula 45)', 'grupo' => 'familiar'];
        } elseif ($parentescoId === 5) {
            // Hermano
            $reglas[] = ['id' => 1, 'obligatorio' => true, 'condicion' => 'Obligatorio · Cédula del familiar', 'grupo' => 'familiar'];
            $reglas[] = ['id' => 14, 'obligatorio' => true, 'condicion' => 'Decisión judicial de tutela / dependencia (Cláusula 49)', 'grupo' => 'familiar'];
            $reglas[] = ['id' => 10, 'obligatorio' => true, 'condicion' => 'Certificación CONAPDIS (Cláusula 49)', 'grupo' => 'familiar'];
        } else {
            $reglas[] = ['id' => 1, 'obligatorio' => true, 'condicion' => 'Identificación del familiar', 'grupo' => 'familiar'];
        }

        // Para Madre/Padre, si el titular ya cargó su Partida de Nacimiento (ID 2), la marcamos como cubierta
        $mapa = $vigentesFamiliar;
        if (in_array($parentescoId, [1, 2], true) && !isset($mapa[2]) && isset($vigentesTitular[2])) {
            $mapa[2] = $vigentesTitular[2];
        }

        return $this->construirListaRecaudos($reglas, $tipos, $mapa);
    }

    /**
     * Busca el ID de un tipo de documento por palabras clave (con respaldo seguro a un ID existente).
     *
     * @param list<string> $palabras
     */
    public function resolverTipoDocumentoPorPalabras(array $palabras, int $fallbackId = 15): int
    {
        $tipos = $this->tiposDocumentos();
        $encontrado = $this->buscarTipoPorPalabras($tipos, $palabras);
        if ($encontrado !== null) {
            return $encontrado;
        }
        if (isset($tipos[$fallbackId])) {
            return $fallbackId;
        }

        $primeraClave = array_key_first($tipos);

        return $primeraClave !== null ? (int) $primeraClave : 1;
    }

    /**
     * Normaliza `$_FILES['documentos']` con estructura `name="documentos[{tipo_id}]"` a `[tipo_id => array]`.
     *
     * @param array<string, mixed> $archivosRequest
     * @return array<int, array<string, mixed>>
     */
    public static function normalizarArchivosPorTipo(array $archivosRequest): array
    {
        $resultado = [];
        $bloque = $archivosRequest['documentos'] ?? null;

        if (!is_array($bloque) || !isset($bloque['name']) || !is_array($bloque['name'])) {
            return $resultado;
        }

        foreach ($bloque['name'] as $tipoIdRaw => $nombre) {
            if (!is_numeric($tipoIdRaw) || (int) $tipoIdRaw <= 0) {
                continue;
            }
            $error = (int) ($bloque['error'][$tipoIdRaw] ?? UPLOAD_ERR_NO_FILE);
            if ($error === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            $resultado[(int) $tipoIdRaw] = [
                'name' => (string) $nombre,
                'type' => (string) ($bloque['type'][$tipoIdRaw] ?? ''),
                'tmp_name' => (string) ($bloque['tmp_name'][$tipoIdRaw] ?? ''),
                'error' => $error,
                'size' => (int) ($bloque['size'][$tipoIdRaw] ?? 0),
            ];
        }

        return $resultado;
    }

    /**
     * @return array<int, array{documentos_id: int, nombre_original: string, url_descarga: string}>
     */
    private function documentosVigentesPorPersona(int $personaId): array
    {
        if ($personaId <= 0) {
            return [];
        }

        $docs = DocumentosModel::query()
            ->where('persona_id', $personaId)
            ->where('estatus_documento', 'Vigente')
            ->orderBy('documentos_id', 'desc')
            ->get(['documentos_id', 'tipo_documento_id', 'nombre_original']);

        $mapa = [];
        foreach ($docs as $doc) {
            $tipoId = (int) $doc->tipo_documento_id;
            if (!isset($mapa[$tipoId])) {
                $mapa[$tipoId] = [
                    'documentos_id' => (int) $doc->documentos_id,
                    'nombre_original' => (string) $doc->nombre_original,
                    'url_descarga' => '/documentos/' . (int) $doc->documentos_id,
                ];
            }
        }

        return $mapa;
    }

    /**
     * @param array<int|string, string> $tipos
     * @param list<string> $palabras
     */
    private function buscarTipoPorPalabras(array $tipos, array $palabras): ?int
    {
        foreach ($tipos as $id => $nombre) {
            $norm = strtolower($nombre);
            foreach ($palabras as $palabra) {
                if (str_contains($norm, strtolower($palabra))) {
                    return (int) $id;
                }
            }
        }

        return null;
    }

    /**
     * @param list<array{id: int, obligatorio: bool, condicion: string, grupo: string}> $reglas
     * @param array<int|string, string> $tipos
     * @param array<int, array{documentos_id: int, nombre_original: string, url_descarga: string}> $vigentes
     * @return list<array{
     *     tipo_documento_id: int,
     *     nombre: string,
     *     obligatorio: bool,
     *     condicion: string,
     *     grupo: string,
     *     cargado: bool,
     *     documento: array{documentos_id: int, nombre_original: string, url_descarga: string}|null
     * }>
     */
    private function construirListaRecaudos(array $reglas, array $tipos, array $vigentes): array
    {
        $lista = [];
        $vistos = [];

        foreach ($reglas as $regla) {
            $id = (int) $regla['id'];
            if (isset($vistos[$id]) || !isset($tipos[$id])) {
                continue;
            }
            $vistos[$id] = true;
            $doc = $vigentes[$id] ?? null;

            $lista[] = [
                'tipo_documento_id' => $id,
                'nombre' => (string) $tipos[$id],
                'etiqueta' => (string) $tipos[$id],
                'obligatorio' => (bool) $regla['obligatorio'],
                'condicion' => (string) $regla['condicion'],
                'motivo' => (string) $regla['condicion'],
                'grupo' => (string) $regla['grupo'],
                'es_titular' => $regla['grupo'] === 'titular',
                'cargado' => $doc !== null,
                'documento' => $doc,
                'nombre_original' => $doc['nombre_original'] ?? null,
                'url_descarga' => $doc['url_descarga'] ?? null,
            ];
        }

        return $lista;
    }
}
