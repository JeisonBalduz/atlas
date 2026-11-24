<?php

declare(strict_types=1);

namespace App\Services;

use App\Model\Asistencia\DetalleAsistenciaModel;
use App\Model\Personas\EmpleadosModel;
use App\Model\Personas\PersonasModel;
use Illuminate\Database\Capsule\Manager as DB;
use InvalidArgumentException;
use Throwable;

/**
 * Servicio de consulta y registro de Asistencia Diaria (Entrada/Salida) con bloqueo obligatorio por reposo médico.
 */
final class AsistenciaService extends BaseVistaService
{
    /**
     * Consulta la vista de solo lectura `public.vw_asistencia_empleados`.
     *
     * @param array<string, mixed> $filtros
     * @return list<object>
     */
    public function listarAsistencias(array $filtros = []): array
    {
        $query = DB::table('public.vw_asistencia_empleados');
        $this->aplicarFiltros(
            $query,
            $filtros,
            camposTexto: ['cedula', 'nombre_completo', 'departamento'],
            camposExactos: ['turno', 'fecha'],
            camposEnteros: ['empleado_id'],
        );
        $this->aplicarOrdenYLimites($query, $filtros, 'empleado_id');

        return $query->get()->all();
    }

    /**
     * Métricas reales del día consultadas desde PostgreSQL.
     *
     * @return array{presentes_hoy: int, en_jornada_hoy: int, cerradas_hoy: int, en_reposo_hoy: int, nomina_activa: int}
     */
    public function resumenDia(?string $fecha = null): array
    {
        $dia = $fecha !== null && trim($fecha) !== '' ? trim($fecha) : date('Y-m-d');

        try {
            $nominaActiva = (int) DB::table('empleados')->where('estatus_actual', 1)->count();
            $presentes = (int) DB::table('detalle_asistencia')
                ->whereDate('fecha', $dia)
                ->distinct()
                ->count('empleado_id');
            $enJornada = (int) DB::table('detalle_asistencia')
                ->whereDate('fecha', $dia)
                ->whereNull('hora_salida')
                ->count();
            $cerradas = (int) DB::table('detalle_asistencia')
                ->whereDate('fecha', $dia)
                ->whereNotNull('hora_salida')
                ->count();
            $enReposo = (int) DB::table('ausencia_justificada')
                ->where('tipo_ausencia', 2)
                ->whereDate('fecha_inicio', '<=', $dia)
                ->whereDate('fecha_fin', '>=', $dia)
                ->distinct()
                ->count('empleado_id');

            return [
                'presentes_hoy' => $presentes,
                'en_jornada_hoy' => $enJornada,
                'cerradas_hoy' => $cerradas,
                'en_reposo_hoy' => $enReposo,
                'nomina_activa' => $nominaActiva,
            ];
        } catch (Throwable) {
            return [
                'presentes_hoy' => 0,
                'en_jornada_hoy' => 0,
                'cerradas_hoy' => 0,
                'en_reposo_hoy' => 0,
                'nomina_activa' => 0,
            ];
        }
    }

    /**
     * Lista registros reales de `detalle_asistencia` enriquecidos con datos del trabajador para DataTables.
     *
     * @return list<array<string, mixed>>
     */
    public function listarRegistrosDetallados(?string $fecha = null, int $limite = 250): array
    {
        $query = DB::table('detalle_asistencia')
            ->join('empleados', 'empleados.empleado_id', '=', 'detalle_asistencia.empleado_id')
            ->join('personas', 'personas.persona_id', '=', 'empleados.persona_id')
            ->leftJoin('departamentos', 'departamentos.departamento_id', '=', 'empleados.departamento_id')
            ->leftJoin('cargo', 'cargo.cargo_id', '=', 'empleados.cargo_id')
            ->select([
                'detalle_asistencia.dtasistencia_id',
                'detalle_asistencia.empleado_id',
                'detalle_asistencia.turno',
                'detalle_asistencia.fecha',
                'detalle_asistencia.hora_entrada',
                'detalle_asistencia.hora_salida',
                'empleados.codigo_empleado',
                'personas.nacionalidad',
                'personas.cedula',
                'personas.primer_nombre',
                'personas.segundo_nombre',
                'personas.primer_apellido',
                'personas.segundo_apellido',
                'departamentos.departamento',
                'cargo.cargo',
            ])
            ->orderByDesc('detalle_asistencia.fecha')
            ->orderByDesc('detalle_asistencia.dtasistencia_id')
            ->limit(max(1, min($limite, 500)));

        if ($fecha !== null && trim($fecha) !== '') {
            $query->whereDate('detalle_asistencia.fecha', trim($fecha));
        }

        $salida = [];
        foreach ($query->get() as $filaObj) {
            /** @var array<string, mixed> $f */
            $f = (array) $filaObj;
            $partesNombre = array_filter([
                trim((string) ($f['primer_nombre'] ?? '')),
                trim((string) ($f['segundo_nombre'] ?? '')),
                trim((string) ($f['primer_apellido'] ?? '')),
                trim((string) ($f['segundo_apellido'] ?? '')),
            ]);
            $nombre = implode(' ', $partesNombre);
            $nombreMostrar = function_exists('mb_convert_case')
                ? mb_convert_case($nombre, MB_CASE_TITLE, 'UTF-8')
                : ucwords(strtolower($nombre));

            $horaEntrada = substr((string) ($f['hora_entrada'] ?? ''), 0, 5);
            $horaSalida = !empty($f['hora_salida']) ? substr((string) $f['hora_salida'], 0, 5) : '';

            $salida[] = [
                'dtasistencia_id' => (int) ($f['dtasistencia_id'] ?? 0),
                'empleado_id' => (int) ($f['empleado_id'] ?? 0),
                'codigo_empleado' => (string) ($f['codigo_empleado'] ?? '—'),
                'cedula' => strtoupper((string) ($f['nacionalidad'] ?? 'V')) . '-' . (string) ($f['cedula'] ?? ''),
                'nombre_completo' => $nombreMostrar,
                'departamento' => (string) ($f['departamento'] ?? 'Sin asignar'),
                'cargo' => (string) ($f['cargo'] ?? 'Sin cargo'),
                'turno' => (string) ($f['turno'] ?? 'Diurno'),
                'fecha' => substr((string) ($f['fecha'] ?? ''), 0, 10),
                'hora_entrada' => $horaEntrada,
                'hora_salida' => $horaSalida,
                'estado_jornada' => $horaSalida !== '' ? 'Completada' : 'En jornada',
            ];
        }

        return $salida;
    }

    /**
     * Consulta la ficha del trabajador, si tiene reposo médico vigente en la fecha y su asistencia abierta/cerrada.
     *
     * @return array<string, mixed>
     */
    public function consultarEstadoEmpleado(int $empleadoId, ?string $fecha = null): array
    {
        $dia = $fecha !== null && trim($fecha) !== '' ? trim($fecha) : date('Y-m-d');

        $empleado = EmpleadosModel::query()
            ->with(['persona', 'cargo', 'departamento'])
            ->find($empleadoId);

        if (!$empleado instanceof EmpleadosModel) {
            throw new InvalidArgumentException('Trabajador no encontrado.', 404);
        }

        $persona = $empleado->getRelationValue('persona');
        if (!$persona instanceof PersonasModel) {
            throw new InvalidArgumentException('Trabajador no encontrado.', 404);
        }

        $cargoRel = $empleado->getRelationValue('cargo');
        $depRel = $empleado->getRelationValue('departamento');

        $reposo = $this->buscarReposoActivo($empleadoId, $dia);
        $asistenciaDia = DetalleAsistenciaModel::query()
            ->where('empleado_id', $empleadoId)
            ->whereDate('fecha', $dia)
            ->orderByDesc('dtasistencia_id')
            ->first();

        return [
            'empleado_id' => (int) $empleado->empleado_id,
            'codigo_empleado' => (string) ($empleado->codigo_empleado ?? ''),
            'cedula' => strtoupper((string) $persona->nacionalidad) . '-' . (string) $persona->cedula,
            'nombre_completo' => CatalogosService::nombreCompletoPersona($persona),
            'cargo' => $cargoRel instanceof \App\Model\Catalogos\CargoModel ? (string) $cargoRel->cargo : 'Sin cargo',
            'departamento' => $depRel instanceof \App\Model\Catalogos\DepartamentosModel ? (string) $depRel->departamento : 'Sin departamento',
            'fecha' => $dia,
            'en_reposo' => $reposo !== null,
            'reposo' => $reposo,
            'asistencia_hoy' => $asistenciaDia instanceof DetalleAsistenciaModel ? [
                'dtasistencia_id' => (int) $asistenciaDia->dtasistencia_id,
                'turno' => (string) $asistenciaDia->turno,
                'hora_entrada' => substr((string) $asistenciaDia->hora_entrada, 0, 5),
                'hora_salida' => $asistenciaDia->hora_salida !== null && $asistenciaDia->hora_salida !== ''
                    ? substr((string) $asistenciaDia->hora_salida, 0, 5)
                    : null,
            ] : null,
        ];
    }

    /**
     * Registra la Entrada de un trabajador bloqueando obligatoriamente si tiene un reposo médico activo.
     */
    public function registrarEntrada(
        int $empleadoId,
        string $turno = 'Diurno',
        ?string $fecha = null,
        ?string $hora = null
    ): DetalleAsistenciaModel {
        $dia = $fecha !== null && trim($fecha) !== '' ? trim($fecha) : date('Y-m-d');
        $horaMarca = $hora !== null && trim($hora) !== '' ? trim($hora) : date('H:i:s');
        $turnoLimpio = trim($turno) !== '' ? substr(trim($turno), 0, 30) : 'Diurno';

        $empleado = EmpleadosModel::query()->find($empleadoId);
        if (!$empleado instanceof EmpleadosModel) {
            throw new InvalidArgumentException('El trabajador seleccionado no existe.', 404);
        }

        if ((int) $empleado->estatus_actual !== 1) {
            throw new InvalidArgumentException('Solo el personal activo en nómina puede registrar asistencia.', 422);
        }

        // REGLA OBLIGATORIA: Bloquear asistencia si el trabajador tiene un reposo médico activo en la fecha
        $reposoActivo = $this->buscarReposoActivo($empleadoId, $dia);
        if ($reposoActivo !== null) {
            throw new InvalidArgumentException(
                sprintf(
                    'Registro bloqueado: el trabajador posee un reposo médico vigente (%s) del %s al %s. Debe cumplir su reposo y solo podrá marcar asistencia después del %s.',
                    $reposoActivo['tipo_reposo'],
                    $reposoActivo['fecha_inicio'],
                    $reposoActivo['fecha_fin'],
                    $reposoActivo['fecha_fin']
                ),
                409
            );
        }

        $abierta = DetalleAsistenciaModel::query()
            ->where('empleado_id', $empleadoId)
            ->whereDate('fecha', $dia)
            ->whereNull('hora_salida')
            ->first();

        if ($abierta instanceof DetalleAsistenciaModel) {
            throw new InvalidArgumentException(
                'El trabajador ya posee una entrada abierta el día ' . $dia . ' a las ' . substr((string) $abierta->hora_entrada, 0, 5) . '.',
                409
            );
        }

        /** @var DetalleAsistenciaModel $creada */
        $creada = DetalleAsistenciaModel::query()->create([
            'empleado_id' => $empleadoId,
            'turno' => $turnoLimpio,
            'fecha' => $dia,
            'hora_entrada' => $horaMarca,
            'hora_salida' => null,
        ]);

        return $creada;
    }

    /**
     * Registra la Salida de la jornada abierta del trabajador en la fecha indicada.
     */
    public function registrarSalida(
        int $empleadoId,
        ?string $fecha = null,
        ?string $hora = null
    ): DetalleAsistenciaModel {
        $dia = $fecha !== null && trim($fecha) !== '' ? trim($fecha) : date('Y-m-d');
        $horaMarca = $hora !== null && trim($hora) !== '' ? trim($hora) : date('H:i:s');

        $abierta = DetalleAsistenciaModel::query()
            ->where('empleado_id', $empleadoId)
            ->whereDate('fecha', $dia)
            ->whereNull('hora_salida')
            ->orderByDesc('dtasistencia_id')
            ->first();

        if (!$abierta instanceof DetalleAsistenciaModel) {
            throw new InvalidArgumentException(
                'El trabajador no tiene una entrada abierta en la fecha ' . $dia . ' para registrar salida.',
                422
            );
        }

        $entradaCorto = substr((string) $abierta->hora_entrada, 0, 5);
        $salidaCorto = substr($horaMarca, 0, 5);
        if ($salidaCorto < $entradaCorto) {
            throw new InvalidArgumentException(
                "La hora de salida ({$salidaCorto}) no puede ser anterior a la hora de entrada ({$entradaCorto}).",
                422
            );
        }

        $abierta->fill(['hora_salida' => $horaMarca]);
        $abierta->save();

        return $abierta;
    }

    /**
     * @return array{ausencia_id: int, fecha_inicio: string, fecha_fin: string, tipo_reposo: string}|null
     */
    private function buscarReposoActivo(int $empleadoId, string $fecha): ?array
    {
        $fila = DB::table('ausencia_justificada')
            ->leftJoin('detalle_reposo', 'detalle_reposo.ausencia_id', '=', 'ausencia_justificada.ausencia_id')
            ->leftJoin('tipo_reposos', 'tipo_reposos.tpreposo_id', '=', 'detalle_reposo.tpreposo_id')
            ->where('ausencia_justificada.empleado_id', $empleadoId)
            ->where('ausencia_justificada.tipo_ausencia', 2)
            ->whereDate('ausencia_justificada.fecha_inicio', '<=', $fecha)
            ->whereDate('ausencia_justificada.fecha_fin', '>=', $fecha)
            ->orderByDesc('ausencia_justificada.fecha_fin')
            ->first([
                'ausencia_justificada.ausencia_id',
                'ausencia_justificada.fecha_inicio',
                'ausencia_justificada.fecha_fin',
                'tipo_reposos.reposo',
            ]);

        if ($fila === null) {
            return null;
        }

        return [
            'ausencia_id' => (int) $fila->ausencia_id,
            'fecha_inicio' => substr((string) $fila->fecha_inicio, 0, 10),
            'fecha_fin' => substr((string) $fila->fecha_fin, 0, 10),
            'tipo_reposo' => (string) ($fila->reposo ?? 'Reposo médico'),
        ];
    }
}
