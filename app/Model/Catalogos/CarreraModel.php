<?php

declare(strict_types=1);

namespace App\Model\Catalogos;

use App\Model\Personas\EmpleadosModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Eloquent de la tabla `carrera`.
 *
 * @property int $carrera_id
 * @property string $carrera
 * @property bool $activo
 */
class CarreraModel extends Model
{
    /** @var string */
    protected $table = 'carrera';

    /** @var string */
    protected $primaryKey = 'carrera_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'carrera',
        'activo',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'carrera_id' => 'integer',
        'carrera' => 'string',
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
        return $this->hasMany(EmpleadosModel::class, 'carrera_id', 'carrera_id');
    }
}
