<?php

declare(strict_types=1);

namespace App\Model\Movimientos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Eloquent de la tabla `tipo_movimientos`.
 *
 * @property int $tpmovimiento_id
 * @property string $tipo_movimiento
 */
class TipoMovimientosModel extends Model
{
    /** @var string */
    protected $table = 'tipo_movimientos';

    /** @var string */
    protected $primaryKey = 'tpmovimiento_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'tipo_movimiento',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'tpmovimiento_id' => 'integer',
        'tipo_movimiento' => 'string',
    ];

    /**
     * @return HasMany<DetalleMovimientoModel, $this>
     */
    public function detallesMovimiento(): HasMany
    {
        return $this->hasMany(DetalleMovimientoModel::class, 'tpmovimiento_id', 'tpmovimiento_id');
    }
}
