<?php

declare(strict_types=1);

namespace App\Controller;

use App\Services\EmpleadosService;
use App\Services\UsuariosService;
use App\Services\VacacionesService;
use App\Utils\Config;
use DateTimeImmutable;
use flight\Engine;
use Illuminate\Database\Capsule\Manager as DB;
use Throwable;

/**
 * Controlador de la vista principal de inicio conectada al 100% a las tablas reales de PostgreSQL.
 */
final class InicioController extends BaseController
{
    private UsuariosService $usuariosService;
    private EmpleadosService $empleadosService;
    private VacacionesService $vacacionesService;

    public function __construct(
        Engine $app,
        Config $config,
        ?UsuariosService $usuariosService = null,
        ?EmpleadosService $empleadosService = null,
        ?VacacionesService $vacacionesService = null,
    ) {
        parent::__construct($app, $config);
        $this->usuariosService = $usuariosService ?? new UsuariosService();
        $this->empleadosService = $empleadosService ?? new EmpleadosService();
        $this->vacacionesService = $vacacionesService ?? new VacacionesService();
    }

    public function render(): void
    {
        $resumen = $this->consultarDatosReales();
        $this->renderVista('inicio', [
            'metricas' => $resumen['metricas'],
            'serie_archivos' => $resumen['serie_archivos'],
            'usuarios_activos' => array_slice($resumen['usuarios'], 0, 6),
            'actividades_recientes' => array_slice($resumen['actividades'], 0, 4),
        ]);
    }

    /**
     * [GET] Devuelve métricas reales, serie histórica de documentos, usuarios y actividad reciente para `inicio.js`.
     */
    public function datos(): void
    {
        $this->jsonOk($this->consultarDatosReales());
    }

    /**
     * @return array{
     *   metricas: array<string, int>,
     *   serie_archivos: array{etiquetas: list<string>, valores: list<int>},
     *   usuarios: list<array<string, mixed>>,
     *   actividades: list<array<string, string>>
     * }
     */
    private function consultarDatosReales(): array
    {
        $hoy = date('Y-m-d');
        $metricas = [
            'vacaciones' => 0,
            'permisos' => 0,
            'reposos' => 0,
            'beneficios' => 0,
            'expedientes' => 0,
            'familiares' => 0,
            'nomina_activa' => 0,
            'usuarios_activos' => 0,
            'presentes_hoy' => 0,
            'porcentaje_asistencia_hoy' => 0,
            'ausencias_hoy' => 0,
        ];
        $serieArchivos = $this->construirSerieMensualVacia();
        $usuarios = [];
        $actividades = [];

        try {
            $nominaActiva = (int) DB::table('empleados')->where('estatus_actual', 1)->count();
            $presentesHoy = (int) DB::table('detalle_asistencia')
                ->where('fecha', $hoy)
                ->distinct()
                ->count('empleado_id');

            $metricas = [
                'vacaciones' => (int) DB::table('solicitud_vacaciones')->count(),
                'permisos' => (int) DB::table('ausencia_justificada')->where('tipo_ausencia', 1)->count(),
                'reposos' => (int) DB::table('ausencia_justificada')->where('tipo_ausencia', 2)->count(),
                'beneficios' => (int) DB::table('detalle_beneficio')->count(),
                'expedientes' => (int) DB::table('documentos')->where('estatus_documento', 'Vigente')->count(),
                'familiares' => (int) DB::table('familiares')->count(),
                'nomina_activa' => $nominaActiva,
                'usuarios_activos' => (int) DB::table('usuarios')->where('estatus', 1)->count(),
                'presentes_hoy' => $presentesHoy,
                'porcentaje_asistencia_hoy' => $nominaActiva > 0
                    ? min(100, (int) round(($presentesHoy / $nominaActiva) * 100))
                    : 0,
                'ausencias_hoy' => (int) DB::table('ausencia_justificada')
                    ->where('fecha_inicio', '<=', $hoy)
                    ->where('fecha_fin', '>=', $hoy)
                    ->count(),
            ];

            $serieArchivos = $this->consultarSerieArchivosReales();

            $departamentosPorEmpleado = DB::table('empleados')
                ->leftJoin('departamentos', 'departamentos.departamento_id', '=', 'empleados.departamento_id')
                ->pluck('departamentos.departamento', 'empleados.empleado_id')
                ->all();

            foreach ($this->usuariosService->listarUsuarios(['limite' => 25]) as $fila) {
                $empId = isset($fila->empleado_id) ? (int) $fila->empleado_id : 0;
                $nombreCompleto = trim((string) ($fila->nombre_completo ?? ''));
                $nombreUsuario = trim((string) ($fila->nombre_usuario ?? 'usuario'));
                $estatusTexto = strtolower(trim((string) ($fila->estatus_texto ?? 'Activo')));

                $usuarios[] = [
                    'usuario_id' => (int) ($fila->usuario_id ?? 0),
                    'nombre_usuario' => $nombreUsuario,
                    'nombre_mostrar' => $nombreCompleto !== '' ? $nombreCompleto : $nombreUsuario,
                    'rol' => (string) ($fila->rol ?? 'Usuario'),
                    'departamento' => (string) ($departamentosPorEmpleado[$empId] ?? 'Talento Humano'),
                    'estatus_texto' => (string) ($fila->estatus_texto ?? 'Activo'),
                    'activo' => $estatusTexto === 'activo',
                ];
            }

            $auditorias = DB::table('auditoria')
                ->orderByDesc('auditoria_id')
                ->limit(6)
                ->get(['accion_realizada', 'descripcion', 'tabla_afectada', 'fecha_creacion']);

            foreach ($auditorias as $aud) {
                $actividades[] = [
                    'icono' => 'shield-check',
                    'titulo' => (string) ($aud->accion_realizada ?? 'Movimiento de auditoría'),
                    'descripcion' => (string) ($aud->descripcion ?? ('Tabla ' . ($aud->tabla_afectada ?? 'sistema'))),
                    'fecha' => substr((string) ($aud->fecha_creacion ?? ''), 0, 10),
                ];
            }

            if ($actividades === []) {
                foreach ($this->vacacionesService->listarSolicitudesVacaciones(['limite' => 3]) as $vac) {
                    $actividades[] = [
                        'icono' => 'palmtree',
                        'titulo' => 'Solicitud de Vacaciones #' . (string) ($vac->solicitud_vacaciones_id ?? ''),
                        'descripcion' => sprintf(
                            '%s · %d días (%s)',
                            (string) ($vac->nombre_completo ?? 'Trabajador'),
                            (int) ($vac->dias_solicitado ?? 0),
                            (string) ($vac->estatus_descripcion ?? 'Registrada')
                        ),
                        'fecha' => (string) ($vac->fecha_inicio ?? ''),
                    ];
                }

                foreach ($this->empleadosService->listarEmpleados(['limite' => 3, 'orden_por' => 'empleado_id', 'direccion' => 'desc']) as $emp) {
                    $actividades[] = [
                        'icono' => 'user-check',
                        'titulo' => 'Ficha de Personal · ' . (string) ($emp->codigo_empleado ?? ''),
                        'descripcion' => sprintf(
                            '%s registrado en %s.',
                            (string) ($emp->nombre_completo ?? 'Trabajador'),
                            (string) ($emp->departamento ?? 'Nómina general')
                        ),
                        'fecha' => '',
                    ];
                }
            }
        } catch (Throwable) {
            // En entornos de prueba sin vistas PostgreSQL mantiene valores reales en cero sin romper render()
        }

        return [
            'metricas' => $metricas,
            'serie_archivos' => $serieArchivos,
            'usuarios' => $usuarios,
            'actividades' => $actividades,
        ];
    }

    /**
     * @return array{etiquetas: list<string>, valores: list<int>}
     */
    private function construirSerieMensualVacia(): array
    {
        $mesesNombres = [
            1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr',
            5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago',
            9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic',
        ];
        $base = new DateTimeImmutable('first day of this month');
        $etiquetas = [];
        $valores = [];

        for ($i = 5; $i >= 0; $i--) {
            $mesFecha = $base->modify("-{$i} months");
            $mesNum = (int) $mesFecha->format('n');
            $etiquetas[] = $mesesNombres[$mesNum] . ' ' . $mesFecha->format('Y');
            $valores[] = 0;
        }

        return ['etiquetas' => $etiquetas, 'valores' => $valores];
    }

    /**
     * @return array{etiquetas: list<string>, valores: list<int>}
     */
    private function consultarSerieArchivosReales(): array
    {
        $mesesNombres = [
            1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr',
            5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago',
            9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic',
        ];

        $base = new DateTimeImmutable('first day of this month');
        $inicioRango = $base->modify('-5 months')->format('Y-m-01');

        $conteosPorClave = [];
        $fechas = DB::table('documentos')
            ->where('fecha_subida', '>=', $inicioRango)
            ->pluck('fecha_subida');

        foreach ($fechas as $fechaRaw) {
            $clave = substr((string) $fechaRaw, 0, 7);
            if ($clave !== '') {
                $conteosPorClave[$clave] = ($conteosPorClave[$clave] ?? 0) + 1;
            }
        }

        $etiquetas = [];
        $valores = [];
        for ($i = 5; $i >= 0; $i--) {
            $mesFecha = $base->modify("-{$i} months");
            $clave = $mesFecha->format('Y-m');
            $mesNum = (int) $mesFecha->format('n');
            $etiquetas[] = $mesesNombres[$mesNum] . ' ' . $mesFecha->format('Y');
            $valores[] = (int) ($conteosPorClave[$clave] ?? 0);
        }

        return ['etiquetas' => $etiquetas, 'valores' => $valores];
    }
}
