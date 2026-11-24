<?php

declare(strict_types=1);

namespace App\Model\Ubicacion;

use App\Model\Catalogos\DependenciasModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Eloquent de la tabla `estados`.
 *
 * @property int $estado_id
 * @property string $estado
 * @property bool $activo
 */
class EstadosModel extends Model
{
    /** @var string */
    protected $table = 'estados';

    /** @var string */
    protected $primaryKey = 'estado_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'estado',
        'activo',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'estado_id' => 'integer',
        'estado' => 'string',
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
     * @return HasMany<MunicipiosModel, $this>
     */
    public function municipios(): HasMany
    {
        return $this->hasMany(MunicipiosModel::class, 'estado_id', 'estado_id');
    }

    /**
     * @return HasMany<CiudadesModel, $this>
     */
    public function ciudades(): HasMany
    {
        return $this->hasMany(CiudadesModel::class, 'estado_id', 'estado_id');
    }

    /**
     * @return HasMany<DependenciasModel, $this>
     */
    public function dependencias(): HasMany
    {
        return $this->hasMany(DependenciasModel::class, 'estado_id', 'estado_id');
    }
}
