<?php

declare(strict_types=1);

namespace App\Model\Ubicacion;

use App\Model\Personas\DireccionModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Eloquent de la tabla `parroquias`.
 *
 * @property int $parroquia_id
 * @property int $municipio_id
 * @property string|null $parroquia
 * @property bool $activo
 */
class ParroquiasModel extends Model
{
    /** @var string */
    protected $table = 'parroquias';

    /** @var string */
    protected $primaryKey = 'parroquia_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'municipio_id',
        'parroquia',
        'activo',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'parroquia_id' => 'integer',
        'municipio_id' => 'integer',
        'parroquia' => 'string',
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
     * @return BelongsTo<MunicipiosModel, $this>
     */
    public function municipio(): BelongsTo
    {
        return $this->belongsTo(MunicipiosModel::class, 'municipio_id', 'municipio_id');
    }

    /**
     * @return HasMany<DireccionModel, $this>
     */
    public function direcciones(): HasMany
    {
        return $this->hasMany(DireccionModel::class, 'parroquia_id', 'parroquia_id');
    }
}
