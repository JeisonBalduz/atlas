<?php

declare(strict_types=1);

namespace App\Services;

use App\Model\Beneficios\DetalleBeneficioModel;
use App\Model\Beneficios\TipoBeneficiosModel;
use App\Model\Personas\DocumentosModel;
use App\Model\Personas\EmpleadosModel;
use App\Model\Personas\FamiliaresModel;
use App\Model\Personas\PersonasModel;
use DateTimeImmutable;
use Illuminate\Database\Capsule\Manager as DB;
use InvalidArgumentException;

/**
 * Servicio directo de asignación, validación contractual (Convención Colectiva INCES 2015–2017)
 * y aprobación de beneficios socioeconómicos.
 */
final class BeneficiosService
{
    /**
     * Reglas de los 37 `tipo_beneficios` según las Cláusulas de la Convención Colectiva INCES:
     * - `periodicidad`: 'anual' | 'semestral' | 'mensual' | 'evento' | 'escolar' | 'quinquenal'
     * - `max_anio`: máximo de entregas permitidas en un mismo año calendario (0 = múltiple por evento/comisión)
     * - `documentos`: lista de IDs `tpdocumentos_id` aceptados/requeridos ([] = sin soporte documental obligatorio)
     *
     * @var array<int, array{clausula: string, periodicidad: string, etiqueta_periodicidad: string, max_anio: int, documentos: list<int>, nota: string}>
     */
    private const REGLAS_CONTRATO = [
        1  => ['clausula' => 'Cláusula 11', 'periodicidad' => 'anual',      'etiqueta_periodicidad' => 'Dotación (hasta 3 entregas al año)',         'max_anio' => 3,  'documentos' => [],       'nota' => 'Dotación de uniformes según cargo y área de labores.'],
        2  => ['clausula' => 'Cláusula 12', 'periodicidad' => 'evento',     'etiqueta_periodicidad' => 'Por visita diaria o asignación mensual',     'max_anio' => 0,  'documentos' => [],       'nota' => 'Movilización local autorizada fuera de la sede natural.'],
        3  => ['clausula' => 'Cláusula 13', 'periodicidad' => 'evento',     'etiqueta_periodicidad' => 'Múltiple por comisión de viaje',             'max_anio' => 0,  'documentos' => [],       'nota' => 'Viáticos conforme al Manual de Viáticos vigente.'],
        4  => ['clausula' => 'Cláusula 14', 'periodicidad' => 'evento',     'etiqueta_periodicidad' => 'Múltiple según causal justificada',          'max_anio' => 0,  'documentos' => [],       'nota' => 'Permisos remunerados contractuales.'],
        5  => ['clausula' => 'Cláusula 24', 'periodicidad' => 'escolar',    'etiqueta_periodicidad' => 'Por nivel académico acreditado',             'max_anio' => 2,  'documentos' => [8, 9],   'nota' => 'Exige consignar Título TSU, Universitario o Postgrado.'],
        6  => ['clausula' => 'Cláusula 25', 'periodicidad' => 'mensual',    'etiqueta_periodicidad' => 'Mensual por escala de años',                 'max_anio' => 12, 'documentos' => [],       'nota' => 'Prima salarial mensual automática por antigüedad.'],
        7  => ['clausula' => 'Cláusula 26', 'periodicidad' => 'semestral',  'etiqueta_periodicidad' => 'Semestral (2 evaluaciones al año)',          'max_anio' => 2,  'documentos' => [],       'nota' => 'Evaluación de desempeño funcionarial o eficiencia obrera.'],
        8  => ['clausula' => 'Cláusula 27', 'periodicidad' => 'anual',      'etiqueta_periodicidad' => 'Anual (primer trimestre)',                   'max_anio' => 1,  'documentos' => [],       'nota' => 'Compensación anual de producción y recaudación.'],
        9  => ['clausula' => 'Cláusula 28', 'periodicidad' => 'mensual',    'etiqueta_periodicidad' => 'Mensual',                                    'max_anio' => 12, 'documentos' => [],       'nota' => 'Compensación mensual por sustitución adquirida.'],
        10 => ['clausula' => 'Cláusula 29', 'periodicidad' => 'anual',      'etiqueta_periodicidad' => 'Anual',                                      'max_anio' => 1,  'documentos' => [],       'nota' => 'Disfrute vacacional y bono vacacional (85 días).'],
        11 => ['clausula' => 'Cláusula 30', 'periodicidad' => 'anual',      'etiqueta_periodicidad' => 'Anual (primera quincena de noviembre)',      'max_anio' => 1,  'documentos' => [],       'nota' => 'Bonificación anual de fin de año (140 días).'],
        12 => ['clausula' => 'Cláusula 31', 'periodicidad' => 'semestral',  'etiqueta_periodicidad' => 'Trimestral / Anual (LOTTT)',                 'max_anio' => 4,  'documentos' => [],       'nota' => 'Depósitos de garantía e intereses de prestaciones sociales.'],
        13 => ['clausula' => 'Cláusula 32', 'periodicidad' => 'mensual',    'etiqueta_periodicidad' => 'Mensual',                                    'max_anio' => 12, 'documentos' => [],       'nota' => 'Beneficio de alimentación y complemento mensual.'],
        14 => ['clausula' => 'Cláusula 33', 'periodicidad' => 'mensual',    'etiqueta_periodicidad' => 'Servicio continuo mensual',                  'max_anio' => 12, 'documentos' => [],       'nota' => 'Acceso gratuito al servicio institucional de comedores.'],
        15 => ['clausula' => 'Cláusulas 34–35', 'periodicidad' => 'anual',  'etiqueta_periodicidad' => 'Aporte mensual e incentivo anual en junio',  'max_anio' => 12, 'documentos' => [13],     'nota' => 'Exige constancia de afiliación a Caja de Ahorros.'],
        16 => ['clausula' => 'Cláusula 36', 'periodicidad' => 'quinquenal', 'etiqueta_periodicidad' => 'Por quinquenio cumplido (cada 5 años)',      'max_anio' => 1,  'documentos' => [],       'nota' => 'Bonificación única por cada quinquenio de servicio.'],
        17 => ['clausula' => 'Cláusula 37', 'periodicidad' => 'evento',     'etiqueta_periodicidad' => 'Por dotación de repuestos / mensual',        'max_anio' => 12, 'documentos' => [16],     'nota' => 'Exige comprobante o solicitud de equipamiento para motorizados.'],
        18 => ['clausula' => 'Cláusula 38', 'periodicidad' => 'evento',     'etiqueta_periodicidad' => 'Por solicitud habitacional',                 'max_anio' => 2,  'documentos' => [16],     'nota' => 'Exige comprobante de solicitud del Plan de Vivienda y Hábitat.'],
        19 => ['clausula' => 'Cláusula 39', 'periodicidad' => 'evento',     'etiqueta_periodicidad' => 'Múltiple por consulta preventiva',           'max_anio' => 0,  'documentos' => [],       'nota' => 'Atención médica primaria y preventiva sin límite de consultas.'],
        20 => ['clausula' => 'Cláusula 40', 'periodicidad' => 'evento',     'etiqueta_periodicidad' => 'Múltiple por prescripción médica / anual',   'max_anio' => 4,  'documentos' => [11],     'nota' => 'Lentes, prótesis, sillas de rueda u odontología; exige Informe Médico.'],
        21 => ['clausula' => 'Cláusula 41', 'periodicidad' => 'evento',     'etiqueta_periodicidad' => 'Múltiple por evento médico / año-póliza',    'max_anio' => 0,  'documentos' => [11, 22], 'nota' => 'Cobertura HCM, Vida o Funerarios; exige Informe Médico o Acta de Defunción.'],
        22 => ['clausula' => 'Cláusulas 43–44', 'periodicidad' => 'anual',  'etiqueta_periodicidad' => 'Anual / por evento recreativo',              'max_anio' => 4,  'documentos' => [],       'nota' => 'Participación en juegos deportivos regionales/nacionales y recreación.'],
        23 => ['clausula' => 'Cláusula 45', 'periodicidad' => 'evento',     'etiqueta_periodicidad' => 'Por evento nupcial (1 unión estable)',       'max_anio' => 1,  'documentos' => [4, 5],   'nota' => 'Exige Acta de Matrimonio o Declaración de Unión Estable de Hecho.'],
        24 => ['clausula' => 'Cláusula 46', 'periodicidad' => 'evento',     'etiqueta_periodicidad' => 'Múltiple (por cada hijo nacido o adoptado)', 'max_anio' => 4,  'documentos' => [3, 14],  'nota' => 'Exige Partida de Nacimiento o Sentencia Judicial de Adopción.'],
        25 => ['clausula' => 'Cláusula 47', 'periodicidad' => 'mensual',    'etiqueta_periodicidad' => 'Matrícula y mensualidades (hijos ≤ 6 años)', 'max_anio' => 13, 'documentos' => [6, 3],   'nota' => 'Exige Constancia de Inscripción o Partida de Nacimiento.'],
        26 => ['clausula' => 'Cláusula 48', 'periodicidad' => 'mensual',    'etiqueta_periodicidad' => 'Mensual por hijo registrado (≤ 6 años)',     'max_anio' => 12, 'documentos' => [3],      'nota' => 'Exige Partida de Nacimiento del hijo o hija registrado.'],
        27 => ['clausula' => 'Cláusula 49', 'periodicidad' => 'mensual',    'etiqueta_periodicidad' => 'Contribución social mensual',                'max_anio' => 12, 'documentos' => [10, 14], 'nota' => 'Exige Certificación CONAPDIS (y decisión judicial si es hermano).'],
        28 => ['clausula' => 'Cláusula 50', 'periodicidad' => 'escolar',    'etiqueta_periodicidad' => 'Anual por inicio escolar (hijos ≤ 25 años)', 'max_anio' => 1,  'documentos' => [6],      'nota' => 'Exige Comprobante de Inscripción de la institución educativa.'],
        29 => ['clausula' => 'Cláusula 51', 'periodicidad' => 'mensual',    'etiqueta_periodicidad' => 'Mensual / renovación escolar (12 meses)',    'max_anio' => 12, 'documentos' => [6],      'nota' => 'Exige Constancia de Estudio vigente para hijos hasta 25 años.'],
        30 => ['clausula' => 'Cláusula 52', 'periodicidad' => 'semestral',  'etiqueta_periodicidad' => 'Hasta 2 entregas al año (Julio y Noviembre)','max_anio' => 2,  'documentos' => [],       'nota' => 'Día del Niño (julio) y Juguetes Navideños (noviembre) para hijos ≤ 12 años.'],
        31 => ['clausula' => 'Cláusula 53', 'periodicidad' => 'anual',      'etiqueta_periodicidad' => 'Anual en vacaciones escolares (3 a 15 años)','max_anio' => 1,  'documentos' => [],       'nota' => 'Plan vacacional anual para hijos e hijas registrados.'],
        32 => ['clausula' => 'Cláusula 54', 'periodicidad' => 'anual',      'etiqueta_periodicidad' => 'Anual (fin de año)',                         'max_anio' => 1,  'documentos' => [],       'nota' => 'Actividades recreativas y navideñas de fin de año.'],
        33 => ['clausula' => 'Cláusula 59', 'periodicidad' => 'mensual',    'etiqueta_periodicidad' => 'Por jornada / mensual',                      'max_anio' => 12, 'documentos' => [],       'nota' => 'Suministro de leche a personal expuesto a sustancias nocivas o talleres.'],
        34 => ['clausula' => 'Cláusula 63', 'periodicidad' => 'semestral',  'etiqueta_periodicidad' => 'Mínimo 1 curso anual / semestral',           'max_anio' => 6,  'documentos' => [],       'nota' => 'Formación, autoformación y mejoramiento técnico-profesional.'],
        35 => ['clausula' => 'Cláusula 67', 'periodicidad' => 'escolar',    'etiqueta_periodicidad' => 'Por período escolar/académico',              'max_anio' => 3,  'documentos' => [6, 7],   'nota' => 'Exige Constancia de Inscripción y Horario Académico Certificado.'],
        36 => ['clausula' => 'Cláusula 68', 'periodicidad' => 'mensual',    'etiqueta_periodicidad' => 'Mensual durante el lapso de estudios',       'max_anio' => 12, 'documentos' => [6, 7],   'nota' => 'Beca universitaria del trabajador; exige Constancia de Estudio/Notas.'],
        37 => ['clausula' => 'Cláusulas 87–89', 'periodicidad' => 'mensual','etiqueta_periodicidad' => 'Mensual (bonos) y anual (esparcimiento)',    'max_anio' => 12, 'documentos' => [],       'nota' => 'Bono familiar, medicinas y recreación para jubilados y pensionados.'],
    ];

    /**
     * Devuelve el mapa completo de reglas contractuales enriquecido con los nombres de `tipo_documentos`.
     *
     * @param array<int|string, string> $catalogoTiposDocumento
     * @return array<int, array<string, mixed>>
     */
    public function obtenerReglasParaCliente(array $catalogoTiposDocumento): array
    {
        $salida = [];
        foreach (self::REGLAS_CONTRATO as $beneficioId => $regla) {
            $docsDetalle = [];
            foreach ($regla['documentos'] as $docId) {
                $docsDetalle[] = [
                    'id' => $docId,
                    'nombre' => (string) ($catalogoTiposDocumento[$docId] ?? "Documento #{$docId}"),
                ];
            }

            $salida[$beneficioId] = [
                'clausula' => $regla['clausula'],
                'periodicidad' => $regla['periodicidad'],
                'etiqueta_periodicidad' => $regla['etiqueta_periodicidad'],
                'max_anio' => $regla['max_anio'],
                'requiere_documento' => $docsDetalle !== [],
                'documentos' => $docsDetalle,
                'nota' => $regla['nota'],
            ];
        }

        return $salida;
    }

    /**
     * Consulta qué documentos requeridos ya tiene cargados como `'Vigente'` una persona para un beneficio dado.
     *
     * @return array{requiere_documento: bool, tiene_vigente: bool, documentos_vigentes: list<array{id: int, nombre_original: string, url: string}>}
     */
    public function estadoDocumentalPersonaBeneficio(int $personaId, int $beneficioId): array
    {
        $regla = self::REGLAS_CONTRATO[$beneficioId] ?? null;
        $idsDoc = $regla['documentos'] ?? [];
        if ($idsDoc === [] || $personaId <= 0) {
            return [
                'requiere_documento' => false,
                'tiene_vigente' => true,
                'documentos_vigentes' => [],
            ];
        }

        $vigentes = DocumentosModel::query()
            ->where('persona_id', $personaId)
            ->whereIn('tipo_documento_id', $idsDoc)
            ->where('estatus_documento', 'Vigente')
            ->get(['documentos_id', 'tipo_documento_id', 'nombre_original']);

        $lista = [];
        foreach ($vigentes as $doc) {
            $lista[] = [
                'id' => (int) $doc->tipo_documento_id,
                'nombre_original' => (string) $doc->nombre_original,
                'url' => '/documentos/' . (int) $doc->documentos_id,
            ];
        }

        return [
            'requiere_documento' => true,
            'tiene_vigente' => $lista !== [],
            'documentos_vigentes' => $lista,
        ];
    }

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

            $beneficioId = (int) ($fila['beneficio_id'] ?? 0);
            $regla = self::REGLAS_CONTRATO[$beneficioId] ?? null;
            $fechaAprobada = !empty($fila['fecha_aprobada']) ? substr((string) $fila['fecha_aprobada'], 0, 10) : '';

            $resultado[] = [
                'dtbeneficio_id' => $dtId,
                'persona_id' => (int) ($fila['persona_id'] ?? 0),
                'beneficio_id' => $beneficioId,
                'cedula' => $cedula,
                'beneficiario' => $beneficiario,
                'rol_parentesco' => $rol,
                'tipo_beneficio' => (string) ($fila['tipo_beneficio'] ?? 'Beneficio'),
                'clausula' => $regla['clausula'] ?? 'Contrato Colectivo',
                'periodicidad' => $regla['etiqueta_periodicidad'] ?? 'Según normativa',
                'fecha_solicitud' => substr((string) ($fila['fecha_solicitud'] ?? ''), 0, 10),
                'fecha_aprobada' => $fechaAprobada,
                'estatus' => $fechaAprobada !== '' ? 'Aprobado' : 'Pendiente',
            ];
        }

        return $resultado;
    }

    /**
     * Registra un beneficio validando periodicidad contractual y soporte documental obligatorio.
     *
     * @param array<string, mixed>|null $archivoSoporte
     */
    public function registrar(
        int $personaId,
        int $beneficioId,
        string $fechaSolicitud,
        ?string $fechaAprobada = null,
        ?array $archivoSoporte = null,
        ?int $tipoDocumentoId = null,
        ?GestorDocumentos $gestorDocumentos = null,
        bool $requiereUploadHttp = true
    ): DetalleBeneficioModel {
        if ($personaId <= 0 || !PersonasModel::query()->where('persona_id', $personaId)->exists()) {
            throw new InvalidArgumentException('La persona seleccionada no existe.', 422);
        }

        if ($beneficioId <= 0 || !TipoBeneficiosModel::query()->where('tpbeneficio_id', $beneficioId)->exists()) {
            throw new InvalidArgumentException('El tipo de beneficio seleccionado no existe.', 422);
        }

        $solicitudLimpia = trim($fechaSolicitud);
        $fechaObj = DateTimeImmutable::createFromFormat('!Y-m-d', $solicitudLimpia);
        if (!$fechaObj instanceof DateTimeImmutable) {
            throw new InvalidArgumentException('La fecha de solicitud es obligatoria y debe tener formato válido.', 422);
        }

        $aprobadaLimpia = $fechaAprobada !== null && trim($fechaAprobada) !== '' ? trim($fechaAprobada) : null;
        if ($aprobadaLimpia !== null && $aprobadaLimpia < $solicitudLimpia) {
            throw new InvalidArgumentException('La fecha de aprobación no puede ser menor que la fecha de solicitud.', 422);
        }

        $regla = self::REGLAS_CONTRATO[$beneficioId] ?? null;
        if ($regla !== null) {
            $this->validarPeriodicidadContractual($personaId, $beneficioId, $fechaObj, $regla);
            $this->procesarSoporteDocumental($personaId, $regla, $archivoSoporte, $tipoDocumentoId, $gestorDocumentos, $requiereUploadHttp);
        }

        /** @var DetalleBeneficioModel $registro */
        $registro = DetalleBeneficioModel::query()->create([
            'persona_id' => $personaId,
            'beneficio_id' => $beneficioId,
            'fecha_solicitud' => $solicitudLimpia,
            'fecha_aprobada' => $aprobadaLimpia,
        ]);

        return $registro;
    }

    /**
     * Recibe dtBeneficioId y nuevos datos; actualiza un beneficio asignado excluyéndolo del conteo de periodicidad.
     *
     * @param array<string, mixed>|null $archivoSoporte
     */
    public function actualizar(
        int $dtBeneficioId,
        int $personaId,
        int $beneficioId,
        string $fechaSolicitud,
        ?string $fechaAprobada = null,
        ?array $archivoSoporte = null,
        ?int $tipoDocumentoId = null,
        ?GestorDocumentos $gestorDocumentos = null,
        bool $requiereUploadHttp = true
    ): DetalleBeneficioModel {
        $registro = DetalleBeneficioModel::query()->find($dtBeneficioId);
        if (!$registro instanceof DetalleBeneficioModel) {
            throw new InvalidArgumentException('El registro de beneficio no existe.', 404);
        }

        if ($personaId <= 0 || !PersonasModel::query()->where('persona_id', $personaId)->exists()) {
            throw new InvalidArgumentException('La persona seleccionada no existe.', 422);
        }

        if ($beneficioId <= 0 || !TipoBeneficiosModel::query()->where('tpbeneficio_id', $beneficioId)->exists()) {
            throw new InvalidArgumentException('El tipo de beneficio seleccionado no existe.', 422);
        }

        $solicitudLimpia = trim($fechaSolicitud);
        $fechaObj = DateTimeImmutable::createFromFormat('!Y-m-d', $solicitudLimpia);
        if (!$fechaObj instanceof DateTimeImmutable) {
            throw new InvalidArgumentException('La fecha de solicitud es obligatoria y debe tener formato válido.', 422);
        }

        $aprobadaLimpia = $fechaAprobada !== null && trim($fechaAprobada) !== '' ? trim($fechaAprobada) : null;
        if ($aprobadaLimpia !== null && $aprobadaLimpia < $solicitudLimpia) {
            throw new InvalidArgumentException('La fecha de aprobación no puede ser menor que la fecha de solicitud.', 422);
        }

        $regla = self::REGLAS_CONTRATO[$beneficioId] ?? null;
        if ($regla !== null) {
            $this->validarPeriodicidadContractual($personaId, $beneficioId, $fechaObj, $regla, $dtBeneficioId);
            $this->procesarSoporteDocumental($personaId, $regla, $archivoSoporte, $tipoDocumentoId, $gestorDocumentos, $requiereUploadHttp);
        }

        $registro->fill([
            'persona_id' => $personaId,
            'beneficio_id' => $beneficioId,
            'fecha_solicitud' => $solicitudLimpia,
            'fecha_aprobada' => $aprobadaLimpia,
        ]);
        $registro->save();

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

        $fechaSolicitud = $registro->fecha_solicitud instanceof \DateTimeInterface
            ? $registro->fecha_solicitud->format('Y-m-d')
            : '';
        if ($fechaSolicitud !== '' && $fechaFinal < $fechaSolicitud) {
            throw new InvalidArgumentException('La fecha de aprobación no puede ser anterior a la fecha de solicitud.', 422);
        }

        $registro->fill(['fecha_aprobada' => $fechaFinal]);
        $registro->save();

        return $registro;
    }

    /**
     * Valida límites por periodicidad contractual permitiendo excluir un registro en edición.
     *
     * @param array{clausula: string, periodicidad: string, etiqueta_periodicidad: string, max_anio: int, documentos: list<int>, nota: string} $regla
     */
    private function validarPeriodicidadContractual(
        int $personaId,
        int $beneficioId,
        DateTimeImmutable $fecha,
        array $regla,
        ?int $excluirDtId = null
    ): void {
        $anio = $fecha->format('Y');
        $mes = $fecha->format('m');
        $periodicidad = $regla['periodicidad'];
        $maxAnio = $regla['max_anio'];

        if ($periodicidad === 'mensual') {
            $inicioMes = "{$anio}-{$mes}-01";
            $finMes = $fecha->format('Y-m-t');
            $consultaMensual = DetalleBeneficioModel::query()
                ->where('persona_id', $personaId)
                ->where('beneficio_id', $beneficioId)
                ->whereBetween('fecha_solicitud', [$inicioMes, $finMes]);
            if ($excluirDtId !== null) {
                $consultaMensual->where('dtbeneficio_id', '!=', $excluirDtId);
            }
            $yaEnMes = $consultaMensual->exists();

            if ($yaEnMes) {
                throw new InvalidArgumentException(
                    "Este beneficio ({$regla['clausula']}) es de entrega mensual y ya fue registrado para este beneficiario en {$mes}/{$anio}.",
                    422
                );
            }
            return;
        }

        if ($periodicidad === 'quinquenal') {
            $haceCincoAnios = $fecha->modify('-5 years')->modify('+1 day')->format('Y-m-d');
            $consultaQuinquenal = DetalleBeneficioModel::query()
                ->where('persona_id', $personaId)
                ->where('beneficio_id', $beneficioId)
                ->where('fecha_solicitud', '>=', $haceCincoAnios);
            if ($excluirDtId !== null) {
                $consultaQuinquenal->where('dtbeneficio_id', '!=', $excluirDtId);
            }
            $reciente = $consultaQuinquenal->exists();

            if ($reciente) {
                throw new InvalidArgumentException(
                    "La bonificación por quinquenio ({$regla['clausula']}) solo se otorga una vez cada cinco (5) años cumplidos.",
                    422
                );
            }
            return;
        }

        if ($maxAnio > 0) {
            $consultaAnual = DetalleBeneficioModel::query()
                ->where('persona_id', $personaId)
                ->where('beneficio_id', $beneficioId)
                ->whereBetween('fecha_solicitud', ["{$anio}-01-01", "{$anio}-12-31"]);
            if ($excluirDtId !== null) {
                $consultaAnual->where('dtbeneficio_id', '!=', $excluirDtId);
            }
            $entregasEnAnio = $consultaAnual->count();

            if ($entregasEnAnio >= $maxAnio) {
                throw new InvalidArgumentException(
                    "Límite anual alcanzado según {$regla['clausula']}: este beneficio permite hasta {$maxAnio} entrega(s) en el año {$anio}.",
                    422
                );
            }
        }
    }

    /**
     * Verifica o almacena el recaudo documental obligatorio reutilizando `GestorDocumentos`.
     *
     * @param array{clausula: string, periodicidad: string, etiqueta_periodicidad: string, max_anio: int, documentos: list<int>, nota: string} $regla
     * @param array<string, mixed>|null $archivoSoporte
     */
    private function procesarSoporteDocumental(
        int $personaId,
        array $regla,
        ?array $archivoSoporte,
        ?int $tipoDocumentoId,
        ?GestorDocumentos $gestorDocumentos,
        bool $requiereUploadHttp = true
    ): void {
        $idsRequeridos = $regla['documentos'];
        if ($idsRequeridos === []) {
            return;
        }

        $tieneArchivoSubido = is_array($archivoSoporte)
            && !empty($archivoSoporte['tmp_name'])
            && (int) ($archivoSoporte['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK;

        if ($tieneArchivoSubido && $gestorDocumentos instanceof GestorDocumentos) {
            $docElegido = ($tipoDocumentoId !== null && in_array($tipoDocumentoId, $idsRequeridos, true))
                ? $tipoDocumentoId
                : $idsRequeridos[0];

            $propietario = $this->resolverPropietarioPersona($personaId);
            $gestorDocumentos->subir(
                $archivoSoporte,
                $docElegido,
                $propietario['empleado_id'],
                $propietario['familia_id'],
                $requiereUploadHttp
            );
            return;
        }

        // Si no adjuntó archivo en esta petición, comprobamos si ya posee el soporte en estado 'Vigente'
        $tieneVigente = DocumentosModel::query()
            ->where('persona_id', $personaId)
            ->whereIn('tipo_documento_id', $idsRequeridos)
            ->where('estatus_documento', 'Vigente')
            ->exists();

        if (!$tieneVigente) {
            throw new InvalidArgumentException(
                "Según {$regla['clausula']}, debes adjuntar el documento de soporte obligatorio para asignar este beneficio.",
                422
            );
        }
    }

    /**
     * @return array{empleado_id: int, familia_id: int|null}
     */
    private function resolverPropietarioPersona(int $personaId): array
    {
        $emp = EmpleadosModel::query()->where('persona_id', $personaId)->first(['empleado_id']);
        if ($emp instanceof EmpleadosModel) {
            return ['empleado_id' => (int) $emp->empleado_id, 'familia_id' => null];
        }

        $fam = FamiliaresModel::query()->where('persona_id', $personaId)->first(['familia_id', 'empleado_id']);
        if ($fam instanceof FamiliaresModel) {
            return ['empleado_id' => (int) $fam->empleado_id, 'familia_id' => (int) $fam->familia_id];
        }

        throw new InvalidArgumentException('La persona seleccionada debe estar vinculada como empleado titular o carga familiar.', 422);
    }
}
