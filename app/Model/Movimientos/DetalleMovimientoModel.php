<?php

declare(strict_types=1);

namespace App\Model\Movimientos;

use App\Model\Personas\EmpleadosModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Modelo Eloquent de la tabla `detalle_movimiento`.
 *
 * @property int $dtmovimiento_id
 * @property int $empleado_id
 * @property int $tpmovimiento_id
 * @property string|null $codigo
 */
class DetalleMovimientoModel extends Model
{
    /** @var string */
    protected $table = 'detalle_movimiento';

    /** @var string */
    protected $primaryKey = 'dtmovimiento_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'empleado_id',
        'tpmovimiento_id',
        'codigo',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'dtmovimiento_id' => 'integer',
        'empleado_id' => 'integer',
        'tpmovimiento_id' => 'integer',
        'codigo' => 'string',
    ];

    /**
     * @return BelongsTo<EmpleadosModel, $this>
     */
    public function empleado(): BelongsTo
    {
        return $this->belongsTo(EmpleadosModel::class, 'empleado_id', 'empleado_id');
    }

    /**
     * @return BelongsTo<TipoMovimientosModel, $this>
     */
    public function tipoMovimiento(): BelongsTo
    {
        return $this->belongsTo(TipoMovimientosModel::class, 'tpmovimiento_id', 'tpmovimiento_id');
    }

    /**
     * @return HasOne<DetalleIngresoModel, $this>
     */
    public function detalleIngreso(): HasOne
    {
        return $this->hasOne(DetalleIngresoModel::class, 'dtmovimiento_id', 'dtmovimiento_id');
    }

    /**
     * @return HasOne<DetalleEgresoModel, $this>
     */
    public function detalleEgreso(): HasOne
    {
        return $this->hasOne(DetalleEgresoModel::class, 'dtmovimiento_id', 'dtmovimiento_id');
    }

    /**
     * @return HasOne<DetalleTrasladoModel, $this>
     */
    public function detalleTraslado(): HasOne
    {
        return $this->hasOne(DetalleTrasladoModel::class, 'dtmovimiento_id', 'dtmovimiento_id');
    }
}
