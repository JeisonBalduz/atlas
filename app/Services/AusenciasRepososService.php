<?php

declare(strict_types=1);

namespace App\Services;

use App\Model\Asistencia\AusenciaJustificadaModel;
use App\Model\Asistencia\DetallePermisoModel;
use App\Model\Asistencia\DetalleReposoModel;
use DateTimeImmutable;
use Illuminate\Database\Capsule\Manager as DB;
use InvalidArgumentException;

/**
 * Servicio para consultas, edición, reanudación y funciones PL/pgSQL de Ausencias, Permisos y Reposos Médicos.
 */
final class AusenciasRepososService extends BaseVistaService
{
    /**
     * Conteos reales desde PostgreSQL para las cards superiores del módulo de ausencias.
     *
     * @return array{permisos_total: int, reposos_total: int, reposos_vigentes_hoy: int}
     */
    public function resumenConteos(): array
    {
        $hoy = date('Y-m-d');

        try {
            return [
                'permisos_total' => (int) DB::table('ausencia_justificada')->where('tipo_ausencia', 1)->count(),
                'reposos_total' => (int) DB::table('ausencia_justificada')->where('tipo_ausencia', 2)->count(),
                'reposos_vigentes_hoy' => (int) DB::table('ausencia_justificada')
                    ->where('tipo_ausencia', 2)
                    ->where('fecha_inicio', '<=', $hoy)
                    ->where('fecha_fin', '>=', $hoy)
                    ->count(),
            ];
        } catch (\Throwable) {
            return ['permisos_total' => 0, 'reposos_total' => 0, 'reposos_vigentes_hoy' => 0];
        }
    }

    /**
     * Verifica si un empleado tiene un reposo médico activo en la fecha dada (`YYYY-MM-DD`).
     *
     * @return array{activo: bool, fecha_inicio?: string, fecha_fin?: string}|null
     */
    public function obtenerReposoActivoEnFecha(int $empleadoId, string $fecha): ?array
    {
        if ($empleadoId <= 0 || trim($fecha) === '') {
            return null;
        }

        $fila = DB::table('ausencia_justificada')
            ->where('empleado_id', $empleadoId)
            ->where('tipo_ausencia', 2)
            ->where('fecha_inicio', '<=', $fecha)
            ->where('fecha_fin', '>=', $fecha)
            ->orderByDesc('fecha_fin')
            ->first(['ausencia_id', 'fecha_inicio', 'fecha_fin']);

        if ($fila === null) {
            return null;
        }

        return [
            'activo' => true,
            'fecha_inicio' => substr((string) $fila->fecha_inicio, 0, 10),
            'fecha_fin' => substr((string) $fila->fecha_fin, 0, 10),
        ];
    }

    /**
     * Consulta la vista de solo lectura `public.vw_ausencias_justificadas`.
     *
     * @param array<string, mixed> $filtros
     * @return list<object>
     */
    public function listarAusenciasJustificadas(array $filtros = []): array
    {
        $query = DB::table('public.vw_ausencias_justificadas');
        $this->aplicarFiltros(
            $query,
            $filtros,
            camposTexto: ['cedula', 'nombre_completo'],
            camposExactos: ['tipo_ausencia_descripcion'],
            camposEnteros: ['ausencia_id', 'empleado_id'],
        );
        $this->aplicarOrdenYLimites($query, $filtros, 'ausencia_id', 'desc');

        return $query->get()->all();
    }

    /**
     * Consulta la vista de solo lectura `public.vw_detalle_permisos`.
     *
     * @param array<string, mixed> $filtros
     * @return list<object>
     */
    public function listarDetallePermisos(array $filtros = []): array
    {
        $query = DB::table('public.vw_detalle_permisos');
        $this->aplicarFiltros(
            $query,
            $filtros,
            camposTexto: ['cedula', 'permiso'],
            camposExactos: ['remunerable_descripcion'],
            camposEnteros: ['dtpermiso_id', 'ausencia_id', 'empleado_id'],
        );
        $this->aplicarOrdenYLimites($query, $filtros, 'dtpermiso_id', 'desc');

        return $query->get()->all();
    }

    /**
     * Consulta la vista de solo lectura `public.vw_detalle_reposos`.
     *
     * @param array<string, mixed> $filtros
     * @return list<object>
     */
    public function listarDetalleReposos(array $filtros = []): array
    {
        $query = DB::table('public.vw_detalle_reposos');
        $this->aplicarFiltros(
            $query,
            $filtros,
            camposTexto: ['cedula', 'tipo_reposo', 'institucion_emisora'],
            camposEnteros: ['dtreposo_id', 'ausencia_id', 'empleado_id'],
        );
        $this->aplicarOrdenYLimites($query, $filtros, 'dtreposo_id', 'desc');

        return $query->get()->all();
    }

    /**
     * Ejecuta `public.fn_obtener_reposo_validado(p_dtreposo_id)`.
     *
     * @return array<string, mixed>
     */
    public function obtenerReposoValidado(int $detalleReposoId): array
    {
        /** @var array<string, mixed> */
        return $this->ejecutarFuncionJson('public.fn_obtener_reposo_validado', $detalleReposoId);
    }

    /**
     * Ejecuta `public.fn_registrar_ausencia_justificada(...)` y retorna el `ausencia_id` generado.
     */
    public function registrarAusenciaJustificada(
        int $empleadoId,
        string $fechaInicio,
        string $fechaFin,
        int $diasAusencia,
        int $tipoAusencia
    ): int {
        if (trim($fechaFin) < trim($fechaInicio)) {
            throw new InvalidArgumentException('La fecha fin no puede ser menor que la fecha de inicio.', 422);
        }

        $row = DB::connection()->selectOne(
            'SELECT public.fn_registrar_ausencia_justificada(?, ?, ?, ?, ?) AS resultado',
            [$empleadoId, $fechaInicio, $fechaFin, $diasAusencia, $tipoAusencia]
        );

        return (int) ($row->resultado ?? 0);
    }

    /**
     * Ejecuta `public.fn_registrar_detalle_permiso(...)`.
     */
    public function registrarDetallePermiso(
        int $ausenciaId,
        int $tipoPermisoId,
        string $motivo,
        bool $remunerable,
        string $fechaSolicitud
    ): string {
        $row = DB::connection()->selectOne(
            'SELECT public.fn_registrar_detalle_permiso(?, ?, ?, ?, ?) AS resultado',
            [$ausenciaId, $tipoPermisoId, $motivo, $remunerable, $fechaSolicitud]
        );

        return (string) ($row->resultado ?? '');
    }

    /**
     * Ejecuta `public.fn_registrar_detalle_reposo(...)` y retorna el `dtreposo_id` generado.
     */
    public function registrarDetalleReposo(
        int $ausenciaId,
        int $tipoReposoId,
        string $descripcion,
        ?int $tipoInstitucionEmisoraId,
        string $fechaSolicitud,
        ?string $doctor
    ): int {
        $row = DB::connection()->selectOne(
            'SELECT public.fn_registrar_detalle_reposo(?, ?, ?, ?, ?, ?) AS resultado',
            [$ausenciaId, $tipoReposoId, $descripcion, $tipoInstitucionEmisoraId, $fechaSolicitud, $doctor]
        );

        return (int) ($row->resultado ?? 0);
    }

    /**
     * Ejecuta `public.fn_registrar_detalle_reposo_valido(...)` y retorna el `detalle_reposo_validado_id` generado.
     */
    public function registrarDetalleReposoValido(
        int $detalleReposoId,
        int $tipoInstitucionEmisoraId,
        string $codigo,
        int $numAseguradora,
        string $fechaExpide,
        string $medico,
        int $clave
    ): int {
        $row = DB::connection()->selectOne(
            'SELECT public.fn_registrar_detalle_reposo_valido(?, ?, ?, ?, ?, ?, ?) AS resultado',
            [$detalleReposoId, $tipoInstitucionEmisoraId, $codigo, $numAseguradora, $fechaExpide, $medico, $clave]
        );

        return (int) ($row->resultado ?? 0);
    }

    /**
     * Actualiza fechas, días y detalle de una ausencia justificada existente.
     *
     * @param array<string, mixed> $datosDetalle
     */
    public function actualizarAusencia(
        int $ausenciaId,
        string $fechaInicio,
        string $fechaFin,
        int $diasAusencia,
        array $datosDetalle,
        VacacionesService $vacacionesService
    ): AusenciaJustificadaModel {
        $ausencia = AusenciaJustificadaModel::query()->find($ausenciaId);
        if (!$ausencia instanceof AusenciaJustificadaModel) {
            throw new InvalidArgumentException('Ausencia justificada no encontrada.', 404);
        }

        if (trim($fechaFin) < trim($fechaInicio) || $diasAusencia <= 0) {
            throw new InvalidArgumentException('El rango de fechas es inválido (fecha fin debe ser mayor o igual a fecha inicio).', 422);
        }

        DB::connection()->transaction(function () use (
            $ausencia,
            $ausenciaId,
            $fechaInicio,
            $fechaFin,
            $diasAusencia,
            $datosDetalle,
            $vacacionesService
        ): void {
            $ausencia->fill([
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'dias_ausencia' => $diasAusencia,
            ]);
            $ausencia->save();

            if ((int) $ausencia->tipo_ausencia === 1) {
                $detallePerm = DetallePermisoModel::query()->where('ausencia_id', $ausenciaId)->first();
                if ($detallePerm instanceof DetallePermisoModel) {
                    if (!empty($datosDetalle['tppermiso_id'])) {
                        $detallePerm->tppermiso_id = (int) $datosDetalle['tppermiso_id'];
                    }
                    if (isset($datosDetalle['motivo']) && trim((string) $datosDetalle['motivo']) !== '') {
                        $detallePerm->motivo = substr(trim((string) $datosDetalle['motivo']), 0, 255);
                    }
                    if (array_key_exists('remunerable', $datosDetalle)) {
                        $detallePerm->remunerable = (bool) $datosDetalle['remunerable'];
                    }
                    $detallePerm->save();
                }
                return;
            }

            $detalleRep = DetalleReposoModel::query()->where('ausencia_id', $ausenciaId)->first();
            if ($detalleRep instanceof DetalleReposoModel) {
                if (!empty($datosDetalle['tpreposo_id'])) {
                    $detalleRep->tpreposo_id = (int) $datosDetalle['tpreposo_id'];
                }
                if (isset($datosDetalle['descripcion']) && trim((string) $datosDetalle['descripcion']) !== '') {
                    $detalleRep->descripcion = substr(trim((string) $datosDetalle['descripcion']), 0, 255);
                }
                if (array_key_exists('doctor', $datosDetalle)) {
                    $doc = trim((string) ($datosDetalle['doctor'] ?? ''));
                    $detalleRep->doctor = $doc !== '' ? substr($doc, 0, 100) : null;
                }
                $detalleRep->save();

                $vacacionesService->reintegrarVacacionesPorReposo(
                    (int) $ausencia->empleado_id,
                    $fechaInicio,
                    $fechaFin,
                    (int) $detalleRep->dtreposo_id
                );
            }
        });

        return $ausencia;
    }

    /**
     * Reanuda anticipadamente una ausencia (cuando se cortó o no se llegó a disfrutar por completo),
     * ajustando la fecha de cierre/días consumidos y reintegrando días vacacionales si aplica.
     *
     * @return array{dias_devueltos: int, dias_efectivos: int, mensaje: string}
     */
    public function reanudarAusencia(
        int $ausenciaId,
        string $fechaReincorporacion,
        VacacionesService $vacacionesService
    ): array {
        $ausencia = AusenciaJustificadaModel::query()->find($ausenciaId);
        if (!$ausencia instanceof AusenciaJustificadaModel) {
            throw new InvalidArgumentException('Ausencia justificada no encontrada.', 404);
        }

        $strInicio = $ausencia->fecha_inicio instanceof \DateTimeInterface ? $ausencia->fecha_inicio->format('Y-m-d') : '';
        $strFin = $ausencia->fecha_fin instanceof \DateTimeInterface ? $ausencia->fecha_fin->format('Y-m-d') : '';
        $inicio = DateTimeImmutable::createFromFormat('!Y-m-d', $strInicio);
        $finOriginal = DateTimeImmutable::createFromFormat('!Y-m-d', $strFin);
        $reincorp = DateTimeImmutable::createFromFormat('!Y-m-d', trim($fechaReincorporacion));

        if (!$inicio instanceof DateTimeImmutable || !$finOriginal instanceof DateTimeImmutable || !$reincorp instanceof DateTimeImmutable) {
            throw new InvalidArgumentException('Indica una fecha válida de reincorporación.', 422);
        }

        $diasOriginales = max(0, (int) $ausencia->dias_ausencia);

        if ($reincorp <= $inicio) {
            $diasEfectivos = 0;
            $nuevaFechaFin = $inicio->format('Y-m-d');
        } elseif ($reincorp > $finOriginal) {
            throw new InvalidArgumentException(
                'La fecha de reincorporación debe ser anterior o igual al fin actual de la ausencia para devolver días.',
                422
            );
        } else {
            $ultimoDiaAusente = $reincorp->modify('-1 day');
            $diasEfectivos = (int) $inicio->diff($ultimoDiaAusente)->days + 1;
            $nuevaFechaFin = $ultimoDiaAusente->format('Y-m-d');
        }

        $diasDevueltos = max(0, $diasOriginales - $diasEfectivos);
        if ($diasDevueltos <= 0) {
            throw new InvalidArgumentException('No existen días pendientes por devolver con esa fecha de reincorporación.', 422);
        }

        return DB::connection()->transaction(function () use (
            $ausencia,
            $nuevaFechaFin,
            $diasEfectivos,
            $diasDevueltos,
            $vacacionesService
        ): array {
            $ausencia->fill([
                'fecha_fin' => $nuevaFechaFin,
                'dias_ausencia' => $diasEfectivos,
            ]);
            $ausencia->save();

            // Si el trabajador tenía una solicitud de vacaciones aprobada solapada no suspendida aún, reintegramos días hábiles
            $empleadoId = (int) $ausencia->empleado_id;
            $inicioAusenciaStr = $ausencia->fecha_inicio instanceof \DateTimeInterface
                ? $ausencia->fecha_inicio->format('Y-m-d')
                : $nuevaFechaFin;
            $solicitudesSolapadas = \App\Model\Vacaciones\SolicitudVacacionesModel::query()
                ->where('empleado_id', $empleadoId)
                ->whereIn('estatus', [2, 4])
                ->where('fecha_inicio', '<=', $nuevaFechaFin)
                ->where('fecha_final', '>=', $inicioAusenciaStr)
                ->get();

            foreach ($solicitudesSolapadas as $solVac) {
                if (!$solVac instanceof \App\Model\Vacaciones\SolicitudVacacionesModel) {
                    continue;
                }
                $consumidosVac = max(0, (int) $solVac->dias_solicitado - $diasDevueltos);
                $vacacionesService->procesarCambioEstatus(
                    $solVac,
                    6,
                    $consumidosVac,
                    'Reanudación anticipada de labores: devolución de ' . $diasDevueltos . ' día(s)'
                );
            }

            return [
                'dias_devueltos' => $diasDevueltos,
                'dias_efectivos' => $diasEfectivos,
                'mensaje' => "Ausencia reanudada correctamente. Se devolvieron {$diasDevueltos} día(s) no disfrutados al trabajador.",
            ];
        });
    }
}
