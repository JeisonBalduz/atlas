<?php

declare(strict_types=1);

namespace App\Services;

use App\Model\Beneficios\DetalleBeneficioModel;
use App\Model\Beneficios\TipoBeneficiosModel;
use App\Model\Personas\PersonasModel;
use Illuminate\Database\Capsule\Manager as DB;
use InvalidArgumentException;

/**
 * Servicio directo de asignación, consulta y aprobación de beneficios socioeconómicos.
 */
final class BeneficiosService
{
    /**
     * @return list<array<string, mixed>>
     */
    public function listar(): array
    {
        $filas = DB::table('detalle_beneficio')
            ->join('personas', 'personas.persona_id', '=', 'detalle_beneficio.persona_id')
            ->join('tipo_beneficios', 'tipo_beneficios.tpbeneficio_id', '=', 'detalle_beneficio.beneficio_id')
            ->leftJoin('empleados', 'empleados.persona_id', '=', 'personas.persona_id')
            ->leftJoin('familiares', 'familiares.persona_id', '=', 'personas.persona_id')
            ->leftJoin('tipo_parentesco', 'tipo_parentesco.parentesco_id', '=', 'familiares.parentesco_id')
            ->select([
                'detalle_beneficio.dtbeneficio_id',
                'detalle_beneficio.persona_id',
                'detalle_beneficio.beneficio_id',
                'detalle_beneficio.fecha_solicitud',
                'detalle_beneficio.fecha_aprobada',
                'personas.nacionalidad',
                'personas.cedula',
                'personas.primer_nombre',
                'personas.segundo_nombre',
                'personas.primer_apellido',
                'personas.segundo_apellido',
                'tipo_beneficios.tipo_beneficio',
                'empleados.empleado_id',
                'tipo_parentesco.parentesco',
            ])
            ->orderByDesc('detalle_beneficio.fecha_solicitud')
            ->orderByDesc('detalle_beneficio.dtbeneficio_id')
            ->get();

        $resultado = [];
        $vistos = [];

        foreach ($filas as $filaObj) {
            /** @var array<string, mixed> $fila */
            $fila = (array) $filaObj;
            $dtId = (int) ($fila['dtbeneficio_id'] ?? 0);
            if ($dtId <= 0 || isset($vistos[$dtId])) {
                continue;
            }
            $vistos[$dtId] = true;

            $partes = array_filter([
                trim((string) ($fila['primer_nombre'] ?? '')),
                trim((string) ($fila['segundo_nombre'] ?? '')),
                trim((string) ($fila['primer_apellido'] ?? '')),
                trim((string) ($fila['segundo_apellido'] ?? '')),
            ]);
            $nombre = implode(' ', $partes);
            $beneficiario = function_exists('mb_convert_case')
                ? mb_convert_case($nombre, MB_CASE_TITLE, 'UTF-8')
                : ucwords(strtolower($nombre));

            $cedula = strtoupper((string) ($fila['nacionalidad'] ?? 'V')) . '-' . (string) ($fila['cedula'] ?? '');
            $rol = !empty($fila['empleado_id'])
                ? 'Titular'
                : (!empty($fila['parentesco']) ? (string) $fila['parentesco'] : 'Beneficiario');

            $fechaAprobada = !empty($fila['fecha_aprobada']) ? substr((string) $fila['fecha_aprobada'], 0, 10) : '';

            $resultado[] = [
                'dtbeneficio_id' => $dtId,
                'persona_id' => (int) ($fila['persona_id'] ?? 0),
                'beneficio_id' => (int) ($fila['beneficio_id'] ?? 0),
                'cedula' => $cedula,
                'beneficiario' => $beneficiario,
                'rol_parentesco' => $rol,
                'tipo_beneficio' => (string) ($fila['tipo_beneficio'] ?? 'Beneficio'),
                'fecha_solicitud' => substr((string) ($fila['fecha_solicitud'] ?? ''), 0, 10),
                'fecha_aprobada' => $fechaAprobada,
                'estatus' => $fechaAprobada !== '' ? 'Aprobado' : 'Pendiente',
            ];
        }

        return $resultado;
    }

    public function registrar(
        int $personaId,
        int $beneficioId,
        string $fechaSolicitud,
        ?string $fechaAprobada = null
    ): DetalleBeneficioModel {
        if ($personaId <= 0 || !PersonasModel::query()->where('persona_id', $personaId)->exists()) {
            throw new InvalidArgumentException('La persona seleccionada no existe.', 422);
        }

        if ($beneficioId <= 0 || !TipoBeneficiosModel::query()->where('tpbeneficio_id', $beneficioId)->exists()) {
            throw new InvalidArgumentException('El tipo de beneficio seleccionado no existe.', 422);
        }

        $solicitudLimpia = trim($fechaSolicitud);
        if ($solicitudLimpia === '') {
            throw new InvalidArgumentException('La fecha de solicitud es obligatoria.', 422);
        }

        $aprobadaLimpia = $fechaAprobada !== null && trim($fechaAprobada) !== '' ? trim($fechaAprobada) : null;

        /** @var DetalleBeneficioModel $registro */
        $registro = DetalleBeneficioModel::query()->create([
            'persona_id' => $personaId,
            'beneficio_id' => $beneficioId,
            'fecha_solicitud' => $solicitudLimpia,
            'fecha_aprobada' => $aprobadaLimpia,
        ]);

        return $registro;
    }

    public function aprobar(int $dtBeneficioId, ?string $fechaAprobada = null): DetalleBeneficioModel
    {
        $registro = DetalleBeneficioModel::query()->find($dtBeneficioId);
        if (!$registro instanceof DetalleBeneficioModel) {
            throw new InvalidArgumentException('El registro de beneficio no existe.', 404);
        }

        $fechaFinal = $fechaAprobada !== null && trim($fechaAprobada) !== ''
            ? trim($fechaAprobada)
            : date('Y-m-d');

        $registro->fill(['fecha_aprobada' => $fechaFinal]);
        $registro->save();

        return $registro;
    }
}
