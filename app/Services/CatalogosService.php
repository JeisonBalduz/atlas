<?php

declare(strict_types=1);

namespace App\Services;

use App\Model\Asistencia\TipoInstitucionEmisoraModel;
use App\Model\Asistencia\TipoPermisosModel;
use App\Model\Asistencia\TipoRepososModel;
use App\Model\Beneficios\DetalleDiscapacidadModel;
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
 * Filtra por `activo = true` de forma global para todos los selectores de vistas,
 * permitiendo `$soloActivos = false` cuando la vista de Tablas Maestras requiere todos.
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
    public function estadosCiviles(bool $soloActivos = true): array
    {
        $clave = 'estados_civiles_' . ($soloActivos ? '1' : '0');
        if (isset($this->memo[$clave])) {
            /** @var array<int|string, string> */
            return $this->memo[$clave];
        }

        $query = EstadoCivilModel::query()->orderBy('estado_civil');
        if ($soloActivos) {
            $query->where('activo', true);
        }

        /** @var array<int|string, string> $datos */
        $datos = $query->pluck('estado_civil', 'estado_civil_id')->all();

        return $this->memo[$clave] = $datos;
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

        $datos = [];
        foreach ($query->get(['dependencia_id', 'dependencia', 'codigo']) as $dep) {
            $datos[] = [
                'value' => (string) $dep->dependencia_id,
                'label' => (string) $dep->dependencia,
                'sublabel' => (string) ($dep->codigo ?? ''),
            ];
        }

        return $this->memo[$clave] = $datos;
    }

    /**
     * @return array<int|string, string>
     */
    public function nivelesAcademicos(bool $soloActivos = true): array
    {
        $clave = 'niveles_' . ($soloActivos ? '1' : '0');
        if (isset($this->memo[$clave])) {
            /** @var array<int|string, string> */
            return $this->memo[$clave];
        }

        $query = NivelAcademicosModel::query()
            ->orderBy('jerarquia')
            ->orderBy('nivel_academico');
        if ($soloActivos) {
            $query->where('activo', true);
        }

        /** @var array<int|string, string> $datos */
        $datos = $query->pluck('nivel_academico', 'nivel_academico_id')->all();

        return $this->memo[$clave] = $datos;
    }

    /**
     * @return array<int|string, string>
     */
    public function carreras(bool $soloActivos = true): array
    {
        $clave = 'carreras_' . ($soloActivos ? '1' : '0');
        if (isset($this->memo[$clave])) {
            /** @var array<int|string, string> */
            return $this->memo[$clave];
        }

        $query = CarreraModel::query()->orderBy('carrera');
        if ($soloActivos) {
            $query->where('activo', true);
        }

        /** @var array<int|string, string> $datos */
        $datos = $query->pluck('carrera', 'carrera_id')->all();

        return $this->memo[$clave] = $datos;
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
    public function estados(bool $soloActivos = true): array
    {
        $clave = 'estados_' . ($soloActivos ? '1' : '0');
        if (isset($this->memo[$clave])) {
            /** @var array<int|string, string> */
            return $this->memo[$clave];
        }

        $query = EstadosModel::query()->orderBy('estado');
        if ($soloActivos) {
            $query->where('activo', true);
        }

        /** @var array<int|string, string> $datos */
        $datos = $query->pluck('estado', 'estado_id')->all();

        return $this->memo[$clave] = $datos;
    }

    /**
     * @return array<int|string, string>
     */
    public function municipiosPorEstado(int $estadoId, bool $soloActivos = true): array
    {
        if ($estadoId <= 0) {
            return [];
        }

        $query = MunicipiosModel::query()
            ->where('estado_id', $estadoId)
            ->orderBy('municipio');
        if ($soloActivos) {
            $query->where('activo', true);
        }

        /** @var array<int|string, string> */
        return $query->pluck('municipio', 'municipio_id')->all();
    }

    /**
     * @return array<int|string, string>
     */
    public function parroquiasPorMunicipio(int $municipioId, bool $soloActivos = true): array
    {
        if ($municipioId <= 0) {
            return [];
        }

        $query = ParroquiasModel::query()
            ->where('municipio_id', $municipioId)
            ->orderBy('parroquia');
        if ($soloActivos) {
            $query->where('activo', true);
        }

        /** @var array<int|string, string> */
        return $query->pluck('parroquia', 'parroquia_id')->all();
    }

    /**
     * @return list<array{value: string, label: string, sublabel: string}>
     */
    public function parroquiasConUbicacion(bool $soloActivos = true): array
    {
        $query = ParroquiasModel::query()
            ->join('municipios', 'municipios.municipio_id', '=', 'parroquias.municipio_id')
            ->join('estados', 'estados.estado_id', '=', 'municipios.estado_id')
            ->select([
                'parroquias.parroquia_id',
                'parroquias.parroquia',
                'municipios.municipio',
                'estados.estado',
            ])
            ->orderBy('parroquias.parroquia');

        if ($soloActivos) {
            $query->where('parroquias.activo', true)
                ->where('municipios.activo', true)
                ->where('estados.activo', true);
        }

        return $query
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
    public function tiposPermiso(bool $soloActivos = true): array
    {
        $clave = 'tipos_permiso_' . ($soloActivos ? '1' : '0');
        if (isset($this->memo[$clave])) {
            /** @var array<int|string, string> */
            return $this->memo[$clave];
        }

        $query = TipoPermisosModel::query()->orderBy('permiso');
        if ($soloActivos) {
            $query->where('activo', true);
        }

        /** @var array<int|string, string> $datos */
        $datos = $query->pluck('permiso', 'tppermiso_id')->all();

        return $this->memo[$clave] = $datos;
    }

    /**
     * @return array<int|string, string>
     */
    public function tiposReposo(bool $soloActivos = true): array
    {
        $clave = 'tipos_reposo_' . ($soloActivos ? '1' : '0');
        if (isset($this->memo[$clave])) {
            /** @var array<int|string, string> */
            return $this->memo[$clave];
        }

        $query = TipoRepososModel::query()->orderBy('reposo');
        if ($soloActivos) {
            $query->where('activo', true);
        }

        /** @var array<int|string, string> $datos */
        $datos = $query->pluck('reposo', 'tpreposo_id')->all();

        return $this->memo[$clave] = $datos;
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

    public function contarEmpleadosActivos(): int
    {
        return EmpleadosModel::query()->where('estatus_actual', 1)->count();
    }

    public function contarPersonasElegibles(): int
    {
        return PersonasModel::query()->count();
    }

    /**
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
     * @return list<array{value: string, label: string, sublabel: string}>
     */
    public function buscarEmpleadosActivosParaSelect(string $busqueda = '', int $limite = 30, bool $incluirPendientes = false): array
    {
        $limiteSeguro = max(1, min($limite, 50));
        $query = EmpleadosModel::query()
            ->join('personas', 'personas.persona_id', '=', 'empleados.persona_id')
            ->whereIn('empleados.estatus_actual', $incluirPendientes ? [1, 3] : [1])
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
     * Resuelve los recaudos documentales vigentes del trabajador según cargo, nivel académico y estado civil
     * conforme al Contrato Colectivo INCES y Procesos RRHH.
     *
     * @return list<array{
     *     tipo_documento_id: int,
     *     nombre: string,
     *     etiqueta: string,
     *     obligatorio: bool,
     *     condicion: string,
     *     motivo: string,
     *     grupo: string,
     *     es_titular: bool,
     *     cargado: bool,
     *     documento: array{documentos_id: int, nombre_original: string, url_descarga: string}|null,
     *     nombre_original: string|null,
     *     url_descarga: string|null
     * }>
     */
    public function recaudosParaEmpleado(
        int $estadoCivilId,
        int $nivelAcademicoId,
        int $personaId = 0,
        ?int $cargoId = null
    ): array {
        $tipos = $this->tiposDocumentos();
        $vigentes = $this->documentosVigentesPorPersona($personaId);

        $idContrato = $this->buscarTipoPorPalabras($tipos, ['contrato laboral', 'contrato']) ?? 15;
        $idNotificacion = $this->buscarTipoPorPalabras($tipos, ['notificación del contrato', 'notificacion del contrato', 'notificacion']) ?? 2;
        $idCurriculum = $this->buscarTipoPorPalabras($tipos, ['currículum', 'curriculum']);
        $idBachiller = $this->buscarTipoPorPalabras($tipos, ['título de bachiller', 'titulo de bachiller', 'bachiller']);
        $idDefuncion = $this->buscarTipoPorPalabras($tipos, ['acta de defunción', 'acta de defuncion', 'defunción', 'defuncion']) ?? 14;

        // 1. Documentos fijos obligatorios para TODO trabajador
        $reglas = [
            [
                'id' => $idContrato,
                'etiqueta' => 'Contrato Laboral',
                'obligatorio' => true,
                'condicion' => 'Obligatorio fijo para todo trabajador',
                'grupo' => 'general',
            ],
            [
                'id' => $idNotificacion,
                'etiqueta' => 'Notificación del Contrato',
                'obligatorio' => true,
                'condicion' => 'Obligatorio fijo para todo trabajador',
                'grupo' => 'general',
            ],
            [
                'id' => 1,
                'etiqueta' => 'Cédula de Identidad',
                'obligatorio' => true,
                'condicion' => 'Identificación personal obligatoria',
                'grupo' => 'general',
            ],
        ];

        // 2. Condicionales por Nivel Académico (Cláusula 24 - Prima de Profesionalización)
        if (in_array($nivelAcademicoId, [2, 3], true) && $idBachiller !== null) {
            $reglas[] = [
                'id' => $idBachiller,
                'etiqueta' => 'Título de Bachiller',
                'obligatorio' => true,
                'condicion' => 'Nivel Secundaria / Bachiller / Técnico Medio',
                'grupo' => 'academico',
            ];
        } elseif ($nivelAcademicoId === 4) {
            $reglas[] = [
                'id' => 8,
                'etiqueta' => 'Título de Técnico Superior Universitario (TSU)',
                'obligatorio' => true,
                'condicion' => 'Nivel TSU (Cláusula 24)',
                'grupo' => 'academico',
            ];
        } elseif (in_array($nivelAcademicoId, [5, 6], true)) {
            $reglas[] = [
                'id' => 8,
                'etiqueta' => 'Título Profesional Universitario',
                'obligatorio' => true,
                'condicion' => 'Nivel Licenciatura / Ingeniería (Cláusula 24)',
                'grupo' => 'academico',
            ];
        } elseif ($nivelAcademicoId >= 7) {
            $reglas[] = [
                'id' => 8,
                'etiqueta' => 'Título Profesional Universitario',
                'obligatorio' => true,
                'condicion' => 'Soporte universitario base (Cláusula 24)',
                'grupo' => 'academico',
            ];
            $reglas[] = [
                'id' => 9,
                'etiqueta' => 'Certificado de Especialización, Maestría o Doctorado',
                'obligatorio' => true,
                'condicion' => 'Nivel Postgrado (Cláusula 24)',
                'grupo' => 'academico',
            ];
        }

        // 3. Condicionales por Estado Civil (6 estados de BD + Contrato Colectivo Cláusulas 1.14, 41, 45, 92)
        if ($estadoCivilId === 2) {
            $reglas[] = [
                'id' => 4,
                'etiqueta' => 'Acta de Matrimonio',
                'obligatorio' => true,
                'condicion' => 'Estado civil: Casado/a (Cláusula 45)',
                'grupo' => 'estado_civil',
            ];
        } elseif ($estadoCivilId === 3) {
            $reglas[] = [
                'id' => 14,
                'etiqueta' => 'Acta o Sentencia de Divorcio',
                'obligatorio' => true,
                'condicion' => 'Estado civil: Divorciado/a',
                'grupo' => 'estado_civil',
            ];
        } elseif ($estadoCivilId === 4) {
            $reglas[] = [
                'id' => $idDefuncion,
                'etiqueta' => 'Acta de Defunción del Cónyuge',
                'obligatorio' => true,
                'condicion' => 'Estado civil: Viudo/a (Cláusula 41 / 92)',
                'grupo' => 'estado_civil',
            ];
        } elseif ($estadoCivilId === 5) {
            $reglas[] = [
                'id' => 5,
                'etiqueta' => 'Declaración de Unión Estable de Hecho',
                'obligatorio' => true,
                'condicion' => 'Estado civil: Unión Estable de Hecho (Cláusula 45)',
                'grupo' => 'estado_civil',
            ];
        }

        // 4. Condicionales por Cargo (Formadores / Docentes)
        if ($cargoId !== null && $cargoId > 0) {
            $todosCargos = $this->cargos(false);
            $nombreCargo = strtolower((string) ($todosCargos[$cargoId] ?? ''));
            if (
                str_contains($nombreCargo, 'formador')
                || str_contains($nombreCargo, 'instructor')
                || str_contains($nombreCargo, 'maestro')
                || str_contains($nombreCargo, 'docente')
            ) {
                $reglas[] = [
                    'id' => 7,
                    'etiqueta' => 'Calendario / Acreditación Docente Certificada',
                    'obligatorio' => true,
                    'condicion' => 'Requerido por perfil de cargo formativo',
                    'grupo' => 'cargo',
                ];
            }
        }

        // 5. Siempre Opcionales (Currículum + Caja de Ahorros Cláusula 34)
        if ($idCurriculum !== null) {
            $reglas[] = [
                'id' => $idCurriculum,
                'etiqueta' => 'Currículum Vitae',
                'obligatorio' => false,
                'condicion' => 'Opcional · Síntesis curricular del trabajador',
                'grupo' => 'opcional',
            ];
        }
        $reglas[] = [
            'id' => 13,
            'etiqueta' => 'Constancia o Comprobante de Caja de Ahorros',
            'obligatorio' => false,
            'condicion' => 'Opcional · Aporte Caja de Ahorros (Cláusula 34)',
            'grupo' => 'opcional',
        ];

        // 6. Condicional por Discapacidad registrada (recaudo unificado INCES, opcional)
        if ($personaId > 0 && $this->personaTieneDiscapacidad($personaId)) {
            $reglas[] = [
                'id' => 10,
                'etiqueta' => 'Certificado CONAPDIS / Informe Médico IVSS',
                'obligatorio' => false,
                'condicion' => 'Opcional · Soporte único de discapacidad registrada (CONAPDIS / IVSS)',
                'grupo' => 'discapacidad',
            ];
        }

        return $this->construirListaRecaudos($reglas, $tipos, $vigentes);
    }

    /**
     * Comprueba si una persona posee al menos un registro en `detalle_discapacidad`.
     * Recibe el ID de persona y retorna true cuando tiene discapacidad registrada.
     */
    public function personaTieneDiscapacidad(int $personaId): bool
    {
        if ($personaId <= 0) {
            return false;
        }

        try {
            return DetalleDiscapacidadModel::query()
                ->where('persona_id', $personaId)
                ->exists();
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Construye el descriptor del recaudo único opcional de discapacidad (ID 10) para una persona.
     * Recibe el ID de persona opcional y retorna el recaudo con su estado vigente actual.
     *
     * @return array<string, mixed>|null
     */
    public function recaudoDiscapacidadUnificado(int $personaId = 0): ?array
    {
        $tipos = $this->tiposDocumentos();
        $vigentes = $this->documentosVigentesPorPersona($personaId);
        $lista = $this->construirListaRecaudos([
            [
                'id' => 10,
                'etiqueta' => 'Certificado CONAPDIS / Informe Médico IVSS',
                'obligatorio' => false,
                'condicion' => 'Opcional · Soporte único de discapacidad registrada (CONAPDIS / IVSS)',
                'grupo' => 'discapacidad',
            ],
        ], $tipos, $vigentes);

        return $lista[0] ?? null;
    }

    /**
     * @param list<array<string, mixed>> $recaudos
     * @return array{
     *     total: int,
     *     cargados_total: int,
     *     obligatorios_total: int,
     *     obligatorios_cargados: int,
     *     opcionales_total: int,
     *     opcionales_cargados: int,
     *     completos: bool
     * }
     */
    public function resumenRecaudos(array $recaudos): array
    {
        $obligatoriosTotal = 0;
        $obligatoriosCargados = 0;
        $opcionalesTotal = 0;
        $opcionalesCargados = 0;

        foreach ($recaudos as $rec) {
            $esObligatorio = (bool) ($rec['obligatorio'] ?? false);
            $cargado = (bool) ($rec['cargado'] ?? false);

            if ($esObligatorio) {
                $obligatoriosTotal++;
                if ($cargado) {
                    $obligatoriosCargados++;
                }
            } else {
                $opcionalesTotal++;
                if ($cargado) {
                    $opcionalesCargados++;
                }
            }
        }

        return [
            'total' => count($recaudos),
            'cargados_total' => $obligatoriosCargados + $opcionalesCargados,
            'obligatorios_total' => $obligatoriosTotal,
            'obligatorios_cargados' => $obligatoriosCargados,
            'opcionales_total' => $opcionalesTotal,
            'opcionales_cargados' => $opcionalesCargados,
            'completos' => $obligatoriosTotal > 0 && $obligatoriosCargados === $obligatoriosTotal,
        ];
    }

    public function recaudosObligatoriosCompletosEmpleado(
        int $estadoCivilId,
        int $nivelAcademicoId,
        int $personaId,
        ?int $cargoId = null
    ): bool {
        $recaudos = $this->recaudosParaEmpleado($estadoCivilId, $nivelAcademicoId, $personaId, $cargoId);
        return $this->resumenRecaudos($recaudos)['completos'];
    }

    /**
     * @return list<array{
     *     tipo_documento_id: int,
     *     nombre: string,
     *     etiqueta: string,
     *     obligatorio: bool,
     *     condicion: string,
     *     motivo: string,
     *     grupo: string,
     *     es_titular: bool,
     *     cargado: bool,
     *     documento: array{documentos_id: int, nombre_original: string, url_descarga: string}|null,
     *     nombre_original: string|null,
     *     url_descarga: string|null
     * }>
     */
    public function recaudosParaFamiliar(int $parentescoId, int $personaFamiliarId = 0, int $personaTitularId = 0): array
    {
        $tipos = $this->tiposDocumentos();
        $vigentesFamiliar = $this->documentosVigentesPorPersona($personaFamiliarId);
        $vigentesTitular = $personaTitularId > 0 ? $this->documentosVigentesPorPersona($personaTitularId) : [];

        $reglas = [];

        if (in_array($parentescoId, [3, 4], true)) {
            $reglas[] = ['id' => 3, 'obligatorio' => true, 'condicion' => 'Obligatorio · Filiación de hijo/hija', 'grupo' => 'familiar'];
            $reglas[] = ['id' => 1, 'obligatorio' => false, 'condicion' => 'Opcional si es menor sin cédula', 'grupo' => 'familiar'];
            $reglas[] = ['id' => 6, 'obligatorio' => false, 'condicion' => 'Condicional · Estudios / Becas / HCM (hasta 27 años)', 'grupo' => 'familiar'];
            $reglas[] = ['id' => 10, 'etiqueta' => 'Certificado CONAPDIS / Informe Médico IVSS', 'obligatorio' => false, 'condicion' => 'Opcional · Soporte de discapacidad (CONAPDIS / IVSS)', 'grupo' => 'discapacidad'];
            $reglas[] = ['id' => 14, 'obligatorio' => false, 'condicion' => 'Condicional · Sentencia de adopción o tutela (Cláusula 46)', 'grupo' => 'familiar'];
        } elseif (in_array($parentescoId, [1, 2], true)) {
            $reglas[] = ['id' => 1, 'obligatorio' => true, 'condicion' => 'Obligatorio · Cédula del padre/madre', 'grupo' => 'familiar'];
            $reglas[] = ['id' => 2, 'obligatorio' => true, 'condicion' => 'Filiación con el trabajador titular (HCM)', 'grupo' => 'titular'];
        } elseif ($parentescoId === 6) {
            $reglas[] = ['id' => 1, 'obligatorio' => true, 'condicion' => 'Obligatorio · Cédula del cónyuge o pareja', 'grupo' => 'familiar'];
            $reglas[] = ['id' => 4, 'obligatorio' => false, 'condicion' => 'Requerido si existe matrimonio civil (Cláusula 45)', 'grupo' => 'familiar'];
            $reglas[] = ['id' => 5, 'obligatorio' => false, 'condicion' => 'Requerido si es Unión Estable de Hecho (Cláusula 45)', 'grupo' => 'familiar'];
        } elseif ($parentescoId === 5) {
            $reglas[] = ['id' => 1, 'obligatorio' => true, 'condicion' => 'Obligatorio · Cédula del familiar', 'grupo' => 'familiar'];
            $reglas[] = ['id' => 14, 'obligatorio' => true, 'condicion' => 'Decisión judicial de tutela / dependencia (Cláusula 49)', 'grupo' => 'familiar'];
            $reglas[] = ['id' => 10, 'etiqueta' => 'Certificado CONAPDIS / Informe Médico IVSS', 'obligatorio' => false, 'condicion' => 'Opcional · Soporte de discapacidad (CONAPDIS / IVSS)', 'grupo' => 'discapacidad'];
        } else {
            $reglas[] = ['id' => 1, 'obligatorio' => true, 'condicion' => 'Identificación del familiar', 'grupo' => 'familiar'];
        }

        $mapa = $vigentesFamiliar;
        if (in_array($parentescoId, [1, 2], true) && !isset($mapa[2]) && isset($vigentesTitular[2])) {
            $mapa[2] = $vigentesTitular[2];
        }

        return $this->construirListaRecaudos($reglas, $tipos, $mapa);
    }

    /**
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
            $norm = function_exists('mb_strtolower') ? mb_strtolower($nombre, 'UTF-8') : strtolower($nombre);
            foreach ($palabras as $palabra) {
                $aguja = function_exists('mb_strtolower') ? mb_strtolower($palabra, 'UTF-8') : strtolower($palabra);
                if (str_contains($norm, $aguja)) {
                    return (int) $id;
                }
            }
        }

        return null;
    }

    /**
     * @param list<array{id: int, etiqueta?: string, obligatorio: bool, condicion: string, grupo: string}> $reglas
     * @param array<int|string, string> $tipos
     * @param array<int, array{documentos_id: int, nombre_original: string, url_descarga: string}> $vigentes
     * @return list<array{
     *     tipo_documento_id: int,
     *     nombre: string,
     *     etiqueta: string,
     *     obligatorio: bool,
     *     condicion: string,
     *     motivo: string,
     *     grupo: string,
     *     es_titular: bool,
     *     cargado: bool,
     *     documento: array{documentos_id: int, nombre_original: string, url_descarga: string}|null,
     *     nombre_original: string|null,
     *     url_descarga: string|null
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
            $etiqueta = isset($regla['etiqueta']) && $regla['etiqueta'] !== ''
                ? $regla['etiqueta']
                : (string) $tipos[$id];

            $lista[] = [
                'tipo_documento_id' => $id,
                'nombre' => (string) $tipos[$id],
                'etiqueta' => $etiqueta,
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
