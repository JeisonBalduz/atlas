<?php

declare(strict_types=1);

namespace App\Model\Movimientos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Eloquent de la tabla `tipo_egresos`.
 *
 * @property int $tpegreso_id
 * @property string $tipo_egreso
 * @property string $descripcion
 */
class TipoEgresosModel extends Model
{
    /** @var string */
    protected $table = 'tipo_egresos';

    /** @var string */
    protected $primaryKey = 'tpegreso_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'tipo_egreso',
        'descripcion',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'tpegreso_id' => 'integer',
        'tipo_egreso' => 'string',
        'descripcion' => 'string',
    ];

    /**
     * @return HasMany<DetalleEgresoModel, $this>
     */
    public function detallesEgreso(): HasMany
    {
        return $this->hasMany(DetalleEgresoModel::class, 'tpegreso_id', 'tpegreso_id');
    }
}
