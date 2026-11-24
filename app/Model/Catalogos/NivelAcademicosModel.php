<?php

declare(strict_types=1);

namespace App\Model\Catalogos;

use App\Model\Personas\EmpleadosModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Eloquent de la tabla `nivel_academicos`.
 *
 * @property int $nivel_academico_id
 * @property string $nivel_academico
 * @property int $jerarquia
 * @property bool $activo
 */
class NivelAcademicosModel extends Model
{
    /** @var string */
    protected $table = 'nivel_academicos';

    /** @var string */
    protected $primaryKey = 'nivel_academico_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'nivel_academico',
        'jerarquia',
        'activo',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'nivel_academico_id' => 'integer',
        'nivel_academico' => 'string',
        'jerarquia' => 'integer',
        'activo' => 'boolean',
    ];

    /**
     * @param Builder<static> $query
     * @return Builder<static>
     */
    public function scopeActivo(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    /**
     * @return HasMany<EmpleadosModel, $this>
     */
    public function empleados(): HasMany
    {
        return $this->hasMany(EmpleadosModel::class, 'nivel_academico_id', 'nivel_academico_id');
    }
}
