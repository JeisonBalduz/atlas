<?php

declare(strict_types=1);

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Eloquent de la tabla `grado_discapacidad`.
 *
 * @property int $gradodisca_id
 * @property string $nombre_gradodis
 * @property string $porcentaje_min
 * @property string $porcentaje_max
 */
class GradoDiscapacidadModel extends Model
{
    /** @var string */
    protected $table = 'grado_discapacidad';

    /** @var string */
    protected $primaryKey = 'gradodisca_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'nombre_gradodis',
        'porcentaje_min',
        'porcentaje_max',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'gradodisca_id' => 'integer',
        'nombre_gradodis' => 'string',
        'porcentaje_min' => 'decimal:2',
        'porcentaje_max' => 'decimal:2',
    ];

    /**
     * @return HasMany<DetalleDiscapacidadModel, $this>
     */
    public function detallesDiscapacidad(): HasMany
    {
        return $this->hasMany(DetalleDiscapacidadModel::class, 'grado_discapacidad_id', 'gradodisca_id');
    }
}
