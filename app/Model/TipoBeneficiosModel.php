<?php

declare(strict_types=1);

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Eloquent de la tabla `tipo_beneficios`.
 *
 * @property int $tpbeneficio_id
 * @property string $tipo_beneficio
 */
class TipoBeneficiosModel extends Model
{
    /** @var string */
    protected $table = 'tipo_beneficios';

    /** @var string */
    protected $primaryKey = 'tpbeneficio_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'tipo_beneficio',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'tpbeneficio_id' => 'integer',
        'tipo_beneficio' => 'string',
    ];

    /**
     * @return HasMany<DetalleBeneficioModel, $this>
     */
    public function detallesBeneficio(): HasMany
    {
        return $this->hasMany(DetalleBeneficioModel::class, 'beneficio_id', 'tpbeneficio_id');
    }
}
