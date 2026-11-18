<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class EstatusModel extends Model
{
    protected $table = 'estatus';

    protected $primaryKey = ''; 

    public $incrementing = false;
    public $timestamps = false;

    public static function todoDatosEstatus(): array
    {
        return static::query()
            ->whereNotNull('estatus_id')
            ->pluck('estatus', 'estatus_id') // <-- Esto genera [ 1 => 'Soltero/a', 2 => 'Casado/a' ]
            ->toArray();
    }
}
