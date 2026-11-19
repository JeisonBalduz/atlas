<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class EstadoCivilModel extends Model
{
    protected $table = 'estado_civil';

    protected $primaryKey = 'estado_civil_id';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'estado_civil',
    ];

    public static function todoDatosEstadoCivil(): array
    {
        return static::query()
            ->whereNotNull('estado_civil_id')
            ->pluck('estado_civil', 'estado_civil_id')
            ->toArray();
    }
}