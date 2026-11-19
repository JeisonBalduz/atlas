<?php

declare(strict_types=1);

namespace App\Model\Movimientos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo Eloquent de la tabla `detalle_egreso`.
 *
 * @property int $dtegreso_id
 * @property int $dtmovimiento_id
 * @property \Illuminate\Support\Carbon $fecha_notificacion
 * @property string $num_resolucion_egreso
 * @property string|null $descripcion
 * @property int $tpegreso_id
 * @property \Illuminate\Support\Carbon $fecha
 * @property string $hora
 */
class DetalleEgresoModel extends Model
{
    /** @var string */
    protected $table = 'detalle_egreso';

    /** @var string */
    protected $primaryKey = 'dtegreso_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'dtmovimiento_id',
        'fecha_notificacion',
        'num_resolucion_egreso',
        'descripcion',
        'tpegreso_id',
        'fecha',
        'hora',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'dtegreso_id' => 'integer',
        'dtmovimiento_id' => 'integer',
        'fecha_notificacion' => 'date',
        'num_resolucion_egreso' => 'string',
        'descripcion' => 'string',
        'tpegreso_id' => 'integer',
        'fecha' => 'date',
        'hora' => 'string',
    ];

    /**
     * @return BelongsTo<DetalleMovimientoModel, $this>
     */
    public function detalleMovimiento(): BelongsTo
    {
        return $this->belongsTo(DetalleMovimientoModel::class, 'dtmovimiento_id', 'dtmovimiento_id');
    }

    /**
     * @return BelongsTo<TipoEgresosModel, $this>
     */
    public function tipoEgreso(): BelongsTo
    {
        return $this->belongsTo(TipoEgresosModel::class, 'tpegreso_id', 'tpegreso_id');
    }
}
