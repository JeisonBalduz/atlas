<?php

declare(strict_types=1);

namespace App\config;

class SessionKeys
{

    public const ID             = 'user_id';
    public const NAME           = 'username';
    public const STATUS         = 'status';
    public const EMPLOYEE_ID    = 'employee_id';
    public const EMPLOYEE_NAME  = 'employee_nombre';
    public const EMPLOYEE_EMAIL = 'employee_correo';
    public const ROLE           = 'role';
    public const PERMISSION     = 'permission'; // Corregido: antes 'permisson'
    public const IS_LOGGED_IN   = 'is_logged_in';
}