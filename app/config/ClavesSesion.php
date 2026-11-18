<?php

declare(strict_types=1);

namespace App\config;

class ClavesSesion
{
    public const ID                  = 'usuario_id';
    public const NOMBRE              = 'nombre_usuario';
    public const ESTADO              = 'estado';
    public const EMPLEADO_ID         = 'empleado_id';
    public const NOMBRE_EMPLEADO     = 'empleado_nombre';
    public const CORREO_EMPLEADO     = 'empleado_correo';
    public const ROL                 = 'rol';
    public const PERMISOS            = 'permiso';
    public const ESTA_AUTENTICADO    = 'esta_autenticado'; // También puedes usar 'sesion_iniciada'
}