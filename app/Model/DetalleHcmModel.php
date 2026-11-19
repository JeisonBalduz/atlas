<?php

declare(strict_types=1);

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo Eloquent de la tabla `detalle_hcm`.
 *
 * @property int $dthcm_id
 * @property int $persona_id
 * @property int $tppatologia_id
 * @property string $estatus
 * @property \Illuminate\Support\Carbon $fecha_ingreso
 * @property string|null $tipo_afiliacion
 */
class DetalleHcmModel extends Model
{
    /** @var string */
    protected $table = 'detalle_hcm';

    /** @var string */
    protected $primaryKey = 'dthcm_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'persona_id',
        'tppatologia_id',
        'estatus',
        'fecha_ingreso',
        'tipo_afiliacion',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'dthcm_id' => 'integer',
        'persona_id' => 'integer',
        'tppatologia_id' => 'integer',
        'estatus' => 'string',
        'fecha_ingreso' => 'date',
        'tipo_afiliacion' => 'string',
    ];

    /**
     * @return BelongsTo<PersonasModel, $this>
     */
    public function persona(): BelongsTo
    {
        return $this->belongsTo(PersonasModel::class, 'persona_id', 'persona_id');
    }

    /**
     * @return BelongsTo<TipoPatologiasModel, $this>
     */
    public function tipoPatologia(): BelongsTo
    {
        return $this->belongsTo(TipoPatologiasModel::class, 'tppatologia_id', 'tppatologia_id');
    }
}
