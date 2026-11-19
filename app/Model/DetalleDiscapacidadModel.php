<?php

declare(strict_types=1);

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo Eloquent de la tabla `detalle_discapacidad`.
 *
 * @property int $dt_discapacidad_id
 * @property int $persona_id
 * @property int $grado_discapacidad_id
 * @property string $numero_carnet
 * @property string $entidad_emisora
 * @property string|null $descripcion
 * @property string|null $dirigido
 * @property \Illuminate\Support\Carbon $fecha_emision_carnet
 * @property \Illuminate\Support\Carbon $fecha_vencimiento_carnet
 */
class DetalleDiscapacidadModel extends Model
{
    /** @var string */
    protected $table = 'detalle_discapacidad';

    /** @var string */
    protected $primaryKey = 'dt_discapacidad_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'persona_id',
        'grado_discapacidad_id',
        'numero_carnet',
        'entidad_emisora',
        'descripcion',
        'dirigido',
        'fecha_emision_carnet',
        'fecha_vencimiento_carnet',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'dt_discapacidad_id' => 'integer',
        'persona_id' => 'integer',
        'grado_discapacidad_id' => 'integer',
        'numero_carnet' => 'string',
        'entidad_emisora' => 'string',
        'descripcion' => 'string',
        'dirigido' => 'string',
        'fecha_emision_carnet' => 'date',
        'fecha_vencimiento_carnet' => 'date',
    ];

    /**
     * @return BelongsTo<PersonasModel, $this>
     */
    public function persona(): BelongsTo
    {
        return $this->belongsTo(PersonasModel::class, 'persona_id', 'persona_id');
    }

    /**
     * @return BelongsTo<GradoDiscapacidadModel, $this>
     */
    public function gradoDiscapacidad(): BelongsTo
    {
        return $this->belongsTo(GradoDiscapacidadModel::class, 'grado_discapacidad_id', 'gradodisca_id');
    }
}
