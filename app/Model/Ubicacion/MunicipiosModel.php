<?php

declare(strict_types=1);

namespace App\Model\Ubicacion;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Eloquent de la tabla `municipios`.
 *
 * @property int $municipio_id
 * @property int $estado_id
 * @property string|null $municipio
 * @property bool $activo
 */
class MunicipiosModel extends Model
{
    /** @var string */
    protected $table = 'municipios';

    /** @var string */
    protected $primaryKey = 'municipio_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'estado_id',
        'municipio',
        'activo',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'municipio_id' => 'integer',
        'estado_id' => 'integer',
        'municipio' => 'string',
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
     * @return BelongsTo<EstadosModel, $this>
     */
    public function estado(): BelongsTo
    {
        return $this->belongsTo(EstadosModel::class, 'estado_id', 'estado_id');
    }

    /**
     * @return HasMany<ParroquiasModel, $this>
     */
    public function parroquias(): HasMany
    {
        return $this->hasMany(ParroquiasModel::class, 'municipio_id', 'municipio_id');
    }
}
