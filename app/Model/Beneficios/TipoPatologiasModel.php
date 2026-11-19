<?php

declare(strict_types=1);

namespace App\Model\Beneficios;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Eloquent de la tabla `tipo_patologias`.
 *
 * @property int $tppatologia_id
 * @property string $tipo_patologia
 */
class TipoPatologiasModel extends Model
{
    /** @var string */
    protected $table = 'tipo_patologias';

    /** @var string */
    protected $primaryKey = 'tppatologia_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'tipo_patologia',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'tppatologia_id' => 'integer',
        'tipo_patologia' => 'string',
    ];

    /**
     * @return HasMany<DetalleHcmModel, $this>
     */
    public function detallesHcm(): HasMany
    {
        return $this->hasMany(DetalleHcmModel::class, 'tppatologia_id', 'tppatologia_id');
    }
}
