<?php

declare(strict_types=1);

namespace App\Model;

use Flight;

class ViwsEmpleadoModel
{
    /**
     * Obtiene todos los registros desde la vista de PostgreSQL vw_empleados
     */
    public static function obtenerTodos(): array
    {
        $db = Flight::db();
        $stmt = $db->query("SELECT * FROM empleados");
        return $stmt->fetchAll();
    }

    /**
     * Obtiene un empleado específico por su ID desde la vista
     */
    public static function obtenerPorId(int $id): ? array
    {
        $db = Flight::db();
        // Usamos consultas preparadas para mayor seguridad contra inyecciones SQL
        $stmt = $db->prepare("SELECT * FROM empleados WHERE persona_id = ?");
        $stmt->execute([$id]);
        
        $resultado = $stmt->fetch();
        return $resultado !== false ? $resultado : null;
    }
}