<?php

declare(strict_types=1);

namespace App\Services;

use App\Model\Vacaciones\SolicitudVacacionesModel;
use DateTimeImmutable;
use Illuminate\Database\Capsule\Manager as DB;
use InvalidArgumentException;

/**
 * Servicio para consultas, validaciones INCES y funciones PL/pgSQL de Vacaciones e Interrupciones por Reposo.
 */
final class VacacionesService extends BaseVistaService
{
    /** @var array<int, list<string>> */
    private array $feriadosPorAnioCache = [];

    private const FERIADOS_FIJOS_MM_DD = [
        '01-01',
        '03-19',
        '04-19',
        '05-01',
        '06-24',
        '07-05',
        '07-24',
        '10-12',
        '11-20',
        '12-17',
        '12-24',
        '12-25',
        '12-31',
    ];

    /**
     * Indica si una fecha `YYYY-MM-DD` es día hábil laborable según normativa INCES (lunes a viernes no feriado).
     */
    public function esDiaHabilInces(string $fecha): bool
    {
        $fechaObj = DateTimeImmutable::createFromFormat('!Y-m-d', trim($fecha));
        if (!$fechaObj instanceof DateTimeImmutable) {
            return false;
        }

        if ((int) $fechaObj->format('N') > 5) {
            return false;
        }

        $anio = (int) $fechaObj->format('Y');
        $feriados = $this->feriadosIncesPorAnio($anio);

        return !in_array($fechaObj->format('Y-m-d'), $feriados, true);
    }

    /**
     * Calcula la fecha final (último día hábil de disfrute inclusive) y la fecha de reincorporación
     * (siguiente día hábil) a partir de una fecha de inicio hábil y una cantidad de días hábiles >= 1.
     *
     * @return array{fecha_final: string, fecha_reincorporacion: string}
     */
    public function calcularFechaFinYReincorporacion(string $fechaInicio, int $diasHabiles): array
    {
        $cursor = DateTimeImmutable::createFromFormat('!Y-m-d', trim($fechaInicio));
        if (!$cursor instanceof DateTimeImmutable || $diasHabiles < 1 || !$this->esDiaHabilInces($cursor->format('Y-m-d'))) {
            return [
                'fecha_final' => '',
                'fecha_reincorporacion' => '',
            ];
        }

        $contados = 1;
        while ($contados < $diasHabiles) {
            $cursor = $cursor->modify('+1 day');
            if ($this->esDiaHabilInces($cursor->format('Y-m-d'))) {
                $contados++;
            }
        }

        $fechaFinal = $cursor->format('Y-m-d');

        $reincorporacion = $cursor->modify('+1 day');
        while (!$this->esDiaHabilInces($reincorporacion->format('Y-m-d'))) {
            $reincorporacion = $reincorporacion->modify('+1 day');
        }

        return [
            'fecha_final' => $fechaFinal,
            'fecha_reincorporacion' => $reincorporacion->format('Y-m-d'),
        ];
    }

    /**
     * Devuelve los feriados aplicables (desde `dias_feriados` con respaldo offline) para un rango de años.
     *
     * @return array{
     *     fechas: list<string>,
     *     detalle: list<array{fecha: string, tipo: string, descripcion: string}>
     * }
     */
    public function obtenerCatalogoFeriados(int $anioInicio, int $anioFin): array
    {
        $desde = max(1930, min($anioInicio, $anioFin));
        $hasta = min($desde + 5, max($anioInicio, $anioFin));

        $mapaDetalle = [];
        for ($anio = $desde; $anio <= $hasta; $anio++) {
            foreach ($this->feriadosDetalladosPorAnio($anio) as $item) {
                $mapaDetalle[$item['fecha']] = $item;
            }
        }

        ksort($mapaDetalle);
        $fechas = array_keys($mapaDetalle);

        return [
            'fechas' => $fechas,
            'detalle' => array_values($mapaDetalle),
        ];
    }

    /**
     * Calcula los días hábiles INCES (Cláusulas 6, 7 y 29) entre dos fechas `YYYY-MM-DD`.
     */
    public function calcularDiasHabilesInces(string $fechaInicio, string $fechaFinal): int
    {
        $inicio = DateTimeImmutable::createFromFormat('!Y-m-d', trim($fechaInicio));
        $fin = DateTimeImmutable::createFromFormat('!Y-m-d', trim($fechaFinal));

        if (!$inicio instanceof DateTimeImmutable || !$fin instanceof DateTimeImmutable || $fin < $inicio) {
            return 0;
        }

        $feriadosCache = [];
        $habiles = 0;
        $cursor = $inicio;

        while ($cursor <= $fin) {
            $diaSemana = (int) $cursor->format('N'); // 1 (Lun) .. 7 (Dom)
            if ($diaSemana <= 5) {
                $anio = (int) $cursor->format('Y');
                if (!isset($feriadosCache[$anio])) {
                    $feriadosCache[$anio] = $this->feriadosIncesPorAnio($anio);
                }
                if (!in_array($cursor->format('Y-m-d'), $feriadosCache[$anio], true)) {
                    $habiles++;
                }
            }
            $cursor = $cursor->modify('+1 day');
        }

        return $habiles;
    }

    /**
     * Devuelve el total de días disponibles acumulados en períodos abiertos para un empleado.
     */
    public function obtenerDiasDisponibles(int $empleadoId): int
    {
        if ($empleadoId <= 0) {
            return 0;
        }

        return (int) DB::table('periodo_vacaciones')
            ->where('empleado_id', $empleadoId)
            ->where('estado_vacaciones', 'Abiertas')
            ->sum('dias_pendientes');
    }

    /**
     * Resumen real de conteos de solicitudes de vacaciones para las cards de la vista.
     *
     * @return array{pendientes: int, aprobadas: int, suspendidas: int, total: int}
     */
    public function resumenConteos(): array
    {
        try {
            return [
                'pendientes' => (int) DB::table('solicitud_vacaciones')->where('estatus', 1)->count(),
                'aprobadas' => (int) DB::table('solicitud_vacaciones')->whereIn('estatus', [2, 4])->count(),
                'suspendidas' => (int) DB::table('solicitud_vacaciones')->where('estatus', 6)->count(),
                'total' => (int) DB::table('solicitud_vacaciones')->count(),
            ];
        } catch (\Throwable) {
            return ['pendientes' => 0, 'aprobadas' => 0, 'suspendidas' => 0, 'total' => 0];
        }
    }

    /**
     * Consulta la vista de solo lectura `public.vw_balance_vacaciones`.
     *
     * @return list<object>
     */
    public function listarBalanceVacaciones(?int $empleadoId = null, ?string $estadoVacaciones = null): array
    {
        $query = DB::table('public.vw_balance_vacaciones');
        $this->aplicarFiltros(
            $query,
            [
                'empleado_id' => $empleadoId,
                'estado_vacaciones' => $estadoVacaciones,
            ],
            camposExactos: ['estado_vacaciones'],
            camposEnteros: ['empleado_id'],
        );

        return $query->orderBy('empleado_id', 'asc')->orderBy('periodo', 'asc')->get()->all();
    }

    /**
     * Consulta la vista de solo lectura `public.vw_resumen_vacaciones`.
     *
     * @return list<object>
     */
    public function listarResumenVacaciones(?int $empleadoId = null): array
    {
        $query = DB::table('public.vw_resumen_vacaciones');
        if ($empleadoId !== null) {
            $query->where('empleado_id', $empleadoId);
        }

        return $query->orderBy('empleado_id', 'asc')->get()->all();
    }

    /**
     * Consulta la vista de solo lectura `public.vw_solicitudes_vacaciones`.
     *
     * @param array<string, mixed> $filtros
     * @return list<object>
     */
    public function listarSolicitudesVacaciones(array $filtros = []): array
    {
        $query = DB::table('public.vw_solicitudes_vacaciones');
        $this->aplicarFiltros(
            $query,
            $filtros,
            camposTexto: ['cedula', 'nombre_completo'],
            camposExactos: ['estatus_descripcion'],
            camposEnteros: ['solicitud_vacaciones_id', 'empleado_id'],
        );
        $this->aplicarOrdenYLimites($query, $filtros, 'solicitud_vacaciones_id', 'desc');

        return $query->get()->all();
    }

    /**
     * Ejecuta `public.fn_obtener_detalle_vacacion(p_solicitud_id)`.
     *
     * @return array<int|string, mixed>
     */
    public function obtenerDetalleVacacion(int $solicitudId): array
    {
        return $this->ejecutarFuncionJson('public.fn_obtener_detalle_vacacion', $solicitudId);
    }

    /**
     * Ejecuta `public.fn_obtener_historial_vacacion(p_solicitud_id)`.
     *
     * @return array<int|string, mixed>
     */
    public function obtenerHistorialVacacion(int $solicitudId): array
    {
        return $this->ejecutarFuncionJson('public.fn_obtener_historial_vacacion', $solicitudId);
    }

    /**
     * Ejecuta `public.fn_obtener_detalle_solicitud_reposo(p_solicitud_id)`.
     *
     * @return array<int|string, mixed>
     */
    public function obtenerDetalleSolicitudReposo(int $solicitudId): array
    {
        return $this->ejecutarFuncionJson('public.fn_obtener_detalle_solicitud_reposo', $solicitudId);
    }

    /**
     * Ejecuta `public.fn_registrar_solicitud_vacaciones(...)` que retorna `TABLE(descripcion, disponibles, solicitados)`.
     *
     * @return array{descripcion: string, disponibles: int, solicitados: int}
     */
    public function registrarSolicitudVacaciones(
        int $empleadoId,
        int $diasSolicitados,
        string $fechaInicio,
        string $fechaFinal,
        bool $gerencia
    ): array {
        if (trim($fechaFinal) < trim($fechaInicio)) {
            throw new InvalidArgumentException('La fecha final no puede ser menor que la fecha de inicio.', 422);
        }

        $row = DB::connection()->selectOne(
            'SELECT * FROM public.fn_registrar_solicitud_vacaciones(?, ?, ?, ?, ?)',
            [$empleadoId, $diasSolicitados, $fechaInicio, $fechaFinal, $gerencia]
        );

        return [
            'descripcion' => (string) ($row->descripcion ?? ''),
            'disponibles' => (int) ($row->disponibles ?? 0),
            'solicitados' => (int) ($row->solicitados ?? 0),
        ];
    }

    /**
     * Procesa el cambio de estatus (Aprobar RRHH = 2, Aprobar Gerencia = 4, Suspender = 6, Negar = 3/5)
     * respetando el trigger `tg_procesar_descuento_vacaciones` y reintegrando días al suspender.
     */
    public function procesarCambioEstatus(
        SolicitudVacacionesModel $solicitud,
        int $nuevoEstatus,
        int $diasDisfrutados = 0,
        string $observacionExtra = ''
    ): void {
        $estatusActual = (int) $solicitud->estatus;
        $solicitudId = (int) $solicitud->solicitud_vacaciones_id;
        $empleadoId = (int) $solicitud->empleado_id;
        $diasSolicitados = (int) $solicitud->dias_solicitado;

        DB::connection()->transaction(function () use (
            $solicitud,
            $estatusActual,
            $nuevoEstatus,
            $solicitudId,
            $empleadoId,
            $diasSolicitados,
            $diasDisfrutados,
            $observacionExtra
        ): void {
            $yaTieneDescuento = DB::table('detalle_vacaciones')
                ->where('solicitud_vacaciones_id', $solicitudId)
                ->exists();

            // Aprobación por RRHH (estatus 2) o por Gerencia (estatus 4)
            if (in_array($nuevoEstatus, [2, 4], true)) {
                if (!$yaTieneDescuento) {
                    $disponibles = $this->obtenerDiasDisponibles($empleadoId);
                    if ($disponibles < $diasSolicitados) {
                        throw new InvalidArgumentException(
                            "Días disponibles insuficientes ({$disponibles} disponibles vs. {$diasSolicitados} solicitados).",
                            422
                        );
                    }
                    // Sincronizar bandera gerencia para que el trigger PL/pgSQL descuente exactamente una vez
                    $solicitud->gerencia = ($nuevoEstatus === 4);
                } else {
                    // Ya fue descontada previamente (ej. pasa de Aprobada RRHH a Aprobada Gerencia): evitar doble descuento del trigger
                    if ($nuevoEstatus === 4) {
                        DB::table('solicitud_vacaciones')
                            ->where('solicitud_vacaciones_id', $solicitudId)
                            ->update(['estatus' => 4]);

                        DB::table('historial_vacaciones')->insert([
                            'solicitud_vacaciones_id' => $solicitudId,
                            'estatus_id' => 4,
                            'observacion' => 'Ratificado / Aprobado por la Gerencia Regional',
                        ]);

                        $solicitud->estatus = 4;
                        return;
                    }
                }

                $solicitud->estatus = $nuevoEstatus;
                $solicitud->save();
                return;
            }

            // Suspensión manual (estatus 6): devolver días descontados no disfrutados a `periodo_vacaciones`
            if ($nuevoEstatus === 6) {
                $detalles = DB::table('detalle_vacaciones')
                    ->where('solicitud_vacaciones_id', $solicitudId)
                    ->orderByDesc('dt_vacaciones_id')
                    ->get();

                $totalDescontado = 0;
                foreach ($detalles as $det) {
                    $totalDescontado += (int) $det->dia_descontados;
                }

                $consumidos = max(0, min($diasDisfrutados, $totalDescontado));
                $porDevolver = max(0, $totalDescontado - $consumidos);

                foreach ($detalles as $det) {
                    if ($porDevolver <= 0) {
                        break;
                    }
                    $descontadoFila = (int) $det->dia_descontados;
                    $devolverAqui = min($porDevolver, $descontadoFila);

                    if ($devolverAqui > 0) {
                        $perActual = DB::table('periodo_vacaciones')
                            ->where('periodo_vacaciones_id', (int) $det->periodo_vacaciones_id)
                            ->first(['dias_pendientes', 'dias_disfrutados']);

                        if ($perActual !== null) {
                            DB::table('periodo_vacaciones')
                                ->where('periodo_vacaciones_id', (int) $det->periodo_vacaciones_id)
                                ->update([
                                    'dias_pendientes' => ((int) $perActual->dias_pendientes) + $devolverAqui,
                                    'dias_disfrutados' => max(0, ((int) $perActual->dias_disfrutados) - $devolverAqui),
                                    'estado_vacaciones' => 'Abiertas',
                                ]);
                        }

                        $restanteFila = $descontadoFila - $devolverAqui;
                        if ($restanteFila <= 0) {
                            DB::table('detalle_vacaciones')
                                ->where('dt_vacaciones_id', (int) $det->dt_vacaciones_id)
                                ->delete();
                        } else {
                            DB::table('detalle_vacaciones')
                                ->where('dt_vacaciones_id', (int) $det->dt_vacaciones_id)
                                ->update(['dia_descontados' => $restanteFila]);
                        }

                        $porDevolver -= $devolverAqui;
                    }
                }

                $solicitud->dias_afectados = $consumidos;
                $solicitud->estatus = 6;
                $solicitud->save();

                if ($observacionExtra !== '' && $estatusActual !== 6) {
                    DB::table('historial_vacaciones')->insert([
                        'solicitud_vacaciones_id' => $solicitudId,
                        'estatus_id' => 6,
                        'observacion' => $observacionExtra,
                    ]);
                }

                return;
            }

            $solicitud->estatus = $nuevoEstatus;
            $solicitud->save();
        });
    }

    /**
     * Edita fechas y días hábiles de una solicitud de vacaciones validando saldo disponible.
     */
    public function actualizarSolicitud(
        SolicitudVacacionesModel $solicitud,
        string $fechaInicio,
        string $fechaFinal,
        int $diasSolicitados,
        bool $gerencia
    ): SolicitudVacacionesModel {
        if (trim($fechaFinal) < trim($fechaInicio)) {
            throw new InvalidArgumentException('La fecha final no puede ser menor que la fecha de inicio.', 422);
        }

        if ($diasSolicitados <= 0) {
            throw new InvalidArgumentException('La solicitud debe contemplar al menos un (1) día hábil.', 422);
        }

        $solicitudId = (int) $solicitud->solicitud_vacaciones_id;
        $empleadoId = (int) $solicitud->empleado_id;

        DB::connection()->transaction(function () use (
            $solicitud,
            $solicitudId,
            $empleadoId,
            $fechaInicio,
            $fechaFinal,
            $diasSolicitados,
            $gerencia
        ): void {
            $yaDescontados = (int) DB::table('detalle_vacaciones')
                ->where('solicitud_vacaciones_id', $solicitudId)
                ->sum('dia_descontados');

            $disponibles = $this->obtenerDiasDisponibles($empleadoId) + $yaDescontados;
            if ($disponibles < $diasSolicitados) {
                throw new InvalidArgumentException(
                    "El trabajador solo dispone de {$disponibles} días acumulables para ajustar esta solicitud.",
                    422
                );
            }

            // Si ya tenía descuentos previos y cambian los días, devolvemos el diferencial o descontamos el faltante
            if ($yaDescontados > 0 && $yaDescontados !== $diasSolicitados) {
                if ($diasSolicitados < $yaDescontados) {
                    $porDevolver = $yaDescontados - $diasSolicitados;
                    $detalles = DB::table('detalle_vacaciones')
                        ->where('solicitud_vacaciones_id', $solicitudId)
                        ->orderByDesc('dt_vacaciones_id')
                        ->get();

                    foreach ($detalles as $det) {
                        if ($porDevolver <= 0) {
                            break;
                        }
                        $dev = min($porDevolver, (int) $det->dia_descontados);
                        $perActual = DB::table('periodo_vacaciones')
                            ->where('periodo_vacaciones_id', (int) $det->periodo_vacaciones_id)
                            ->first(['dias_pendientes', 'dias_disfrutados']);

                        if ($perActual !== null) {
                            DB::table('periodo_vacaciones')
                                ->where('periodo_vacaciones_id', (int) $det->periodo_vacaciones_id)
                                ->update([
                                    'dias_pendientes' => ((int) $perActual->dias_pendientes) + $dev,
                                    'dias_disfrutados' => max(0, ((int) $perActual->dias_disfrutados) - $dev),
                                    'estado_vacaciones' => 'Abiertas',
                                ]);
                        }

                        $restante = (int) $det->dia_descontados - $dev;
                        if ($restante <= 0) {
                            DB::table('detalle_vacaciones')->where('dt_vacaciones_id', (int) $det->dt_vacaciones_id)->delete();
                        } else {
                            DB::table('detalle_vacaciones')->where('dt_vacaciones_id', (int) $det->dt_vacaciones_id)->update(['dia_descontados' => $restante]);
                        }
                        $porDevolver -= $dev;
                    }
                } else {
                    $porDescontar = $diasSolicitados - $yaDescontados;
                    $periodos = DB::table('periodo_vacaciones')
                        ->where('empleado_id', $empleadoId)
                        ->where('estado_vacaciones', 'Abiertas')
                        ->where('dias_pendientes', '>', 0)
                        ->orderBy('periodo', 'asc')
                        ->get();

                    foreach ($periodos as $per) {
                        if ($porDescontar <= 0) {
                            break;
                        }
                        $restar = min($porDescontar, (int) $per->dias_pendientes);
                        $nuevosPend = (int) $per->dias_pendientes - $restar;

                        DB::table('periodo_vacaciones')
                            ->where('periodo_vacaciones_id', (int) $per->periodo_vacaciones_id)
                            ->update([
                                'dias_pendientes' => $nuevosPend,
                                'dias_disfrutados' => ((int) $per->dias_disfrutados) + $restar,
                                'estado_vacaciones' => $nuevosPend === 0 ? 'Cerrado' : 'Abiertas',
                            ]);

                        DB::table('detalle_vacaciones')->insert([
                            'solicitud_vacaciones_id' => $solicitudId,
                            'periodo_vacaciones_id' => (int) $per->periodo_vacaciones_id,
                            'dia_descontados' => $restar,
                        ]);

                        $porDescontar -= $restar;
                    }
                }
            }

            $solicitud->fill([
                'fecha_inicio' => $fechaInicio,
                'fecha_final' => $fechaFinal,
                'dias_solicitado' => $diasSolicitados,
                'dias_afectados' => $diasSolicitados,
                'gerencia' => $gerencia,
            ]);
            $solicitud->save();
        });

        return $solicitud;
    }

    /**
     * Ejecuta `public.fn_registrar_detalle_solicitud_reposo(...)`.
     */
    public function registrarDetalleSolicitudReposo(
        int $solicitudVacacionesId,
        int $detalleReposoId,
        int $diasAfectados
    ): string {
        $row = DB::connection()->selectOne(
            'SELECT public.fn_registrar_detalle_solicitud_reposo(?, ?, ?) AS resultado',
            [$solicitudVacacionesId, $detalleReposoId, $diasAfectados]
        );

        return (string) ($row->resultado ?? '');
    }

    /**
     * Ejecuta `public.fn_reintegrar_vacaciones_por_reposo(...)`.
     */
    public function reintegrarVacacionesPorReposo(
        int $empleadoId,
        string $fechaInicioReposo,
        string $fechaFinReposo,
        int $detalleReposoId
    ): string {
        $row = DB::connection()->selectOne(
            'SELECT public.fn_reintegrar_vacaciones_por_reposo(?, ?, ?, ?) AS resultado',
            [$empleadoId, $fechaInicioReposo, $fechaFinReposo, $detalleReposoId]
        );

        return (string) ($row->resultado ?? '');
    }

    /**
     * @return list<string>
     */
    private function feriadosIncesPorAnio(int $anio): array
    {
        if (isset($this->feriadosPorAnioCache[$anio])) {
            return $this->feriadosPorAnioCache[$anio];
        }

        $fechas = array_map(
            static fn (array $item): string => $item['fecha'],
            $this->feriadosDetalladosPorAnio($anio)
        );

        return $this->feriadosPorAnioCache[$anio] = array_values(array_unique($fechas));
    }

    /**
     * Consulta `dias_feriados` para un año específico (recurrentes + fechas específicas),
     * manteniendo cálculo local de respaldo si la tabla aún no existe en entornos de prueba.
     *
     * @return list<array{fecha: string, tipo: string, descripcion: string}>
     */
    private function feriadosDetalladosPorAnio(int $anio): array
    {
        $items = [];
        $tieneMovilesEnTabla = false;

        try {
            $filas = DB::table('dias_feriados')
                ->where('activo', true)
                ->where(static function ($q) use ($anio): void {
                    $q->where('es_recurrente', true)
                        ->orWhere(static function ($sub) use ($anio): void {
                            $sub->where('es_recurrente', false)
                                ->where('fecha', '>=', sprintf('%04d-01-01', $anio))
                                ->where('fecha', '<=', sprintf('%04d-12-31', $anio));
                        });
                })
                ->get(['fecha', 'mes', 'dia', 'tipo', 'descripcion', 'es_recurrente']);

            foreach ($filas as $fila) {
                $esRecurrente = (bool) ($fila->es_recurrente ?? false);
                if ($esRecurrente) {
                    $mes = (int) ($fila->mes ?? 0);
                    $dia = (int) ($fila->dia ?? 0);
                    if ($mes < 1 || $mes > 12 || $dia < 1 || $dia > 31) {
                        continue;
                    }
                    $fechaIso = sprintf('%04d-%02d-%02d', $anio, $mes, $dia);
                } else {
                    $fechaRaw = substr((string) ($fila->fecha ?? ''), 0, 10);
                    if ($fechaRaw === '') {
                        continue;
                    }
                    $fechaIso = $fechaRaw;
                    $tieneMovilesEnTabla = true;
                }

                $items[$fechaIso] = [
                    'fecha' => $fechaIso,
                    'tipo' => (string) ($fila->tipo ?? 'Nacional'),
                    'descripcion' => (string) ($fila->descripcion ?? 'Día feriado'),
                ];
            }
        } catch (\Throwable) {
            $items = [];
        }

        if ($items === []) {
            foreach (self::FERIADOS_FIJOS_MM_DD as $mmdd) {
                $fechaIso = sprintf('%04d-%s', $anio, $mmdd);
                $items[$fechaIso] = [
                    'fecha' => $fechaIso,
                    'tipo' => 'Fijo',
                    'descripcion' => 'Día feriado INCES',
                ];
            }
        }

        if (!$tieneMovilesEnTabla) {
            $pascua = $this->calcularDomingoPascua($anio);
            $moviles = [
                -48 => 'Lunes de Carnaval',
                -47 => 'Martes de Carnaval',
                -3 => 'Jueves Santo',
                -2 => 'Viernes Santo',
            ];
            foreach ($moviles as $offset => $desc) {
                $fechaIso = $pascua->modify("{$offset} days")->format('Y-m-d');
                if (!isset($items[$fechaIso])) {
                    $items[$fechaIso] = [
                        'fecha' => $fechaIso,
                        'tipo' => 'Móvil',
                        'descripcion' => $desc,
                    ];
                }
            }
        }

        ksort($items);

        return array_values($items);
    }

    private function calcularDomingoPascua(int $anio): DateTimeImmutable
    {
        $a = $anio % 19;
        $b = intdiv($anio, 100);
        $c = $anio % 100;
        $d = intdiv($b, 4);
        $e = $b % 4;
        $f = intdiv($b + 8, 25);
        $g = intdiv($b - $f + 1, 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = intdiv($c, 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = intdiv($a + 11 * $h + 22 * $l, 451);
        $mes = intdiv($h + $l - 7 * $m + 114, 31);
        $dia = (($h + $l - 7 * $m + 114) % 31) + 1;

        return new DateTimeImmutable(sprintf('%04d-%02d-%02d', $anio, $mes, $dia));
    }
}
