<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

final class VwWorkerModel extends Model
{
    protected $table = 'vw_empleados';

    protected $primaryKey = ''; 

    public $incrementing = false;
    public $timestamps = false;

    public static function getIdentityId(int $workedId): ?string{
        return static::where('empleado_id', $workedId)->value('cedula');
    }
}
