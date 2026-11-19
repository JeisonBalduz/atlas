<?php

declare(strict_types=1);

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo Eloquent de la tabla `detalle_beneficio`.
 *
 * @property int $dtbeneficio_id
 * @property int $persona_id
 * @property int $beneficio_id
 * @property \Illuminate\Support\Carbon $fecha_solicitud
 * @property \Illuminate\Support\Carbon|null $fecha_aprobada
 */
class DetalleBeneficioModel extends Model
{
    /** @var string */
    protected $table = 'detalle_beneficio';

    /** @var string */
    protected $primaryKey = 'dtbeneficio_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'persona_id',
        'beneficio_id',
        'fecha_solicitud',
        'fecha_aprobada',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'dtbeneficio_id' => 'integer',
        'persona_id' => 'integer',
        'beneficio_id' => 'integer',
        'fecha_solicitud' => 'date',
        'fecha_aprobada' => 'date',
    ];

    /**
     * @return BelongsTo<PersonasModel, $this>
     */
    public function persona(): BelongsTo
    {
        return $this->belongsTo(PersonasModel::class, 'persona_id', 'persona_id');
    }

    /**
     * @return BelongsTo<TipoBeneficiosModel, $this>
     */
    public function tipoBeneficio(): BelongsTo
    {
        return $this->belongsTo(TipoBeneficiosModel::class, 'beneficio_id', 'tpbeneficio_id');
    }
}
