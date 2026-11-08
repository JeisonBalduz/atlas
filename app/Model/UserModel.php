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

    public function getUser(string $usuario): ? UserModel
    {
        return self::select('usuario_id', 'nombre_usuario', 'contrasena')
            ->where('nombre_usuario', $usuario)
            ->first();

    }

    // public static function findUserIfExists(int $id): ? array
    // {
    //     // Ejecutamos la consulta usando selectRaw y pasamos el $id de forma segura
    //     $resultado = static::selectRaw("nombre_de_tu_funcion(?) as datos_usuario", [$id])
    //         ->first();

    //     // Si la función de la BD no encontró al usuario, el registro o la columna vendrán vacíos
    //     if (!$resultado || empty($resultado->datos_usuario)) {
    //         return null;
    //     }

    //     // Como PostgreSQL devuelve el JSON como una cadena de texto, lo decodificamos a un array asociativo de PHP
    //     return json_decode($resultado->datos_usuario, true);
    // }
}
