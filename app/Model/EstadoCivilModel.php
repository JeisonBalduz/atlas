<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class EstadoCivilModel extends Model
{
    protected $table = 'estado_civil';

    protected $primaryKey = 'estado_civil_id';

    public $incrementing = false;
    public $timestamps = false;

    public static function todoDatosEstadoCivil(): array
    {
        return static::query()
            ->whereNotNull('estado_civil_id')
            ->pluck('estado_civil', 'estado_civil_id') // <-- Esto genera [ 1 => 'Soltero/a', 2 => 'Casado/a' ]
            ->toArray();
    }
}
