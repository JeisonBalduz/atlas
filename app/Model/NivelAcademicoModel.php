<?php

declare(strict_types=1);

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class NivelAcademicoModel extends Model
{
    protected $table = 'nivel_academicos';

    protected $primaryKey = 'nivel_academico_id';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'nivel_academico_id',
        'nivel_academico',
        'jerarquia'
        
    ];

    public static function todoDatos(): array
    {
        return static::query()
            ->whereNotNull()
            ->pluck('nivel_academico', 'nivel_academico_id')
            ->toArray();
    }
}