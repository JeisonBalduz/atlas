<?php

declare(strict_types=1);

namespace App\Model\Ubicacion;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo Eloquent de la tabla `ciudades`.
 *
 * @property int $ciudad_id
 * @property int $estado_id
 * @property string $ciudad
 * @property bool $capital
 */
class CiudadesModel extends Model
{
    /** @var string */
    protected $table = 'ciudades';

    /** @var string */
    protected $primaryKey = 'ciudad_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'estado_id',
        'ciudad',
        'capital',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'ciudad_id' => 'integer',
        'estado_id' => 'integer',
        'ciudad' => 'string',
        'capital' => 'boolean',
    ];

    /**
     * @return BelongsTo<EstadosModel, $this>
     */
    public function estado(): BelongsTo
    {
        return $this->belongsTo(EstadosModel::class, 'estado_id', 'estado_id');
    }
}
