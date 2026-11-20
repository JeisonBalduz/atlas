<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\AsistenciaService;
use App\Services\AusenciasRepososService;
use App\Services\EmpleadosService;
use App\Services\SancionesService;
use App\Services\UsuariosService;
use App\Services\UtilidadesService;
use App\Services\VacacionesService;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionNamedType;

/**
 * Verifica la estructura, firmas y tipos estrictos de los 7 servicios de vistas y funciones PostgreSQL.
 */
final class PostgresServicesTest extends TestCase
{
    /**
     * @return array<class-string, array<string, array{params: int, returnType: string, nullable: bool}>>
     */
    private static function expectedContract(): array
    {
        return [
            EmpleadosService::class => [
                'listarEmpleados' => ['params' => 1, 'returnType' => 'array', 'nullable' => false],
                'obtenerEmpleadoPorId' => ['params' => 1, 'returnType' => 'object', 'nullable' => true],
                'listarDireccionesEmpleados' => ['params' => 1, 'returnType' => 'array', 'nullable' => false],
                'listarFamiliares' => ['params' => 1, 'returnType' => 'array', 'nullable' => false],
                'obtenerFichaTecnica' => ['params' => 1, 'returnType' => 'array', 'nullable' => false],
                'obtenerDireccion' => ['params' => 1, 'returnType' => 'array', 'nullable' => false],
                'obtenerFamiliares' => ['params' => 1, 'returnType' => 'array', 'nullable' => false],
                'obtenerTelefonos' => ['params' => 1, 'returnType' => 'array', 'nullable' => false],
                'obtenerTallasPersona' => ['params' => 1, 'returnType' => 'array', 'nullable' => false],
            ],
            UsuariosService::class => [
                'listarUsuarios' => ['params' => 1, 'returnType' => 'array', 'nullable' => false],
                'listarPermisosUsuarios' => ['params' => 1, 'returnType' => 'array', 'nullable' => false],
                'obtenerDatosCompletosUsuario' => ['params' => 1, 'returnType' => 'array', 'nullable' => false],
            ],
            AsistenciaService::class => [
                'listarAsistencias' => ['params' => 1, 'returnType' => 'array', 'nullable' => false],
            ],
            AusenciasRepososService::class => [
                'listarAusenciasJustificadas' => ['params' => 1, 'returnType' => 'array', 'nullable' => false],
                'listarDetallePermisos' => ['params' => 1, 'returnType' => 'array', 'nullable' => false],
                'listarDetalleReposos' => ['params' => 1, 'returnType' => 'array', 'nullable' => false],
                'obtenerReposoValidado' => ['params' => 1, 'returnType' => 'array', 'nullable' => false],
                'registrarAusenciaJustificada' => ['params' => 5, 'returnType' => 'int', 'nullable' => false],
                'registrarDetallePermiso' => ['params' => 5, 'returnType' => 'string', 'nullable' => false],
                'registrarDetalleReposo' => ['params' => 6, 'returnType' => 'int', 'nullable' => false],
                'registrarDetalleReposoValido' => ['params' => 7, 'returnType' => 'int', 'nullable' => false],
            ],
            VacacionesService::class => [
                'listarBalanceVacaciones' => ['params' => 2, 'returnType' => 'array', 'nullable' => false],
                'listarResumenVacaciones' => ['params' => 1, 'returnType' => 'array', 'nullable' => false],
                'listarSolicitudesVacaciones' => ['params' => 1, 'returnType' => 'array', 'nullable' => false],
                'obtenerDetalleVacacion' => ['params' => 1, 'returnType' => 'array', 'nullable' => false],
                'obtenerHistorialVacacion' => ['params' => 1, 'returnType' => 'array', 'nullable' => false],
                'obtenerDetalleSolicitudReposo' => ['params' => 1, 'returnType' => 'array', 'nullable' => false],
                'registrarSolicitudVacaciones' => ['params' => 5, 'returnType' => 'array', 'nullable' => false],
                'registrarDetalleSolicitudReposo' => ['params' => 3, 'returnType' => 'string', 'nullable' => false],
                'reintegrarVacacionesPorReposo' => ['params' => 4, 'returnType' => 'string', 'nullable' => false],
            ],
            SancionesService::class => [
                'listarSanciones' => ['params' => 1, 'returnType' => 'array', 'nullable' => false],
            ],
            UtilidadesService::class => [
                'calcularEdad' => ['params' => 1, 'returnType' => 'string', 'nullable' => false],
            ],
        ];
    }

    public function testSevenServicesExistAndMatchSignatures(): void
    {
        $contracts = self::expectedContract();
        self::assertCount(7, $contracts);

        foreach ($contracts as $class => $methods) {
            self::assertTrue(class_exists($class), "No existe la clase $class");
            $reflection = new ReflectionClass($class);
            self::assertTrue($reflection->isFinal(), "La clase $class debe ser final");

            foreach ($methods as $methodName => $spec) {
                self::assertTrue($reflection->hasMethod($methodName), "Falta método $class::$methodName");
                $method = $reflection->getMethod($methodName);
                self::assertTrue($method->isPublic());
                self::assertCount($spec['params'], $method->getParameters(), "Parámetros en $class::$methodName");

                $returnType = $method->getReturnType();
                self::assertInstanceOf(ReflectionNamedType::class, $returnType);
                self::assertSame($spec['returnType'], $returnType->getName());
                self::assertSame($spec['nullable'], $returnType->allowsNull());
            }
        }
    }
}
