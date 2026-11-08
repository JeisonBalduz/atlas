<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UserModel extends Model
{
    protected $table = 'usuarios';

    protected $primaryKey = '';

    public $incrementing = false;
    public $timestamps = false;

    // Recupera la contraseña del usuario por su nombre de usuario
    public static function getPasswordByUsername(string $usuario): ?string
    {
        return static::where('nombre_usuario', $usuario)->value('contrasena');
    }

    // Verifica si un usuario existe por su nombre de usuario
    public static function userExists(string $usuario): bool
    {
        return static::where('nombre_usuario', $usuario)->exists();
    }

    // Recupera el ID del usuario por su nombre de usuario
    public static function getIdByUsername(string $usuario): ?int
    {
        return self::where('nombre_usuario', $usuario)->value('usuario_id');
    }

    // Recupera todos los datos del usuario por su ID
    public function getUserAllId(string|int $userID): ?array
    {
        $resultado = self::selectRaw("fn_obtener_datos_usuario(?) as datos", [$userID])
            ->first();

        if (!$resultado || empty($resultado->datos)) {
            return null;
        }

        // Pasamos el string JSON que envió Postgres a un array nativo asociativo de PHP
        return json_decode($resultado->datos, true);
    }

}
