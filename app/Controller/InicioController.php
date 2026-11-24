<?php

declare(strict_types=1);

namespace App\Controller;

use App\Services\EmpleadosService;
use App\Services\UsuariosService;
use App\Services\VacacionesService;
use App\Utils\Config;
use flight\Engine;
use Illuminate\Database\Capsule\Manager as DB;
use Throwable;

/**
 * Controlador de la vista principal de inicio conectada a las tablas reales (excepto estadísticas).
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
            'usuarios_activos' => array_slice($resumen['usuarios'], 0, 6),
            'actividades_recientes' => array_slice($resumen['actividades'], 0, 4),
        ]);
    }

    /**
     * [GET] Devuelve usuarios reales y actividad reciente para `inicio.js`.
     */
    public function datos(): void
    {
        $this->jsonOk($this->consultarDatosReales());
    }

    /**
     * @return array{usuarios: list<array<string, mixed>>, actividades: list<array<string, string>>}
     */
    private function consultarDatosReales(): array
    {
        $usuarios = [];
        $actividades = [];

        try {
            $departamentosPorEmpleado = DB::table('public.empleados')
                ->leftJoin('public.departamentos', 'departamentos.departamento_id', '=', 'empleados.departamento_id')
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

            $auditorias = DB::table('public.auditoria')
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
            // En entornos de prueba sin vistas PostgreSQL mantiene arreglo vacío sin romper render()
        }

        return [
            'usuarios' => $usuarios,
            'actividades' => $actividades,
        ];
    }
}
