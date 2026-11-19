<?php

declare(strict_types=1);

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Eloquent de la tabla `parroquias`.
 *
 * @property int $parroquia_id
 * @property int $municipio_id
 * @property string|null $parroquia
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
    ];

    /** @var array<string, string> */
    protected $casts = [
        'parroquia_id' => 'integer',
        'municipio_id' => 'integer',
        'parroquia' => 'string',
    ];

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
