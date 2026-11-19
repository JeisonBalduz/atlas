<?php

declare(strict_types=1);

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo Eloquent de la tabla `detalle_ingreso`.
 *
 * @property int $dtingreso_id
 * @property int $dtmovimiento_id
 * @property string $tiempo_contrato
 * @property bool|null $periodo_prueba
 * @property string $numero_contrato
 * @property \Illuminate\Support\Carbon $fecha_ingreso
 */
class DetalleIngresoModel extends Model
{
    /** @var string */
    protected $table = 'detalle_ingreso';

    /** @var string */
    protected $primaryKey = 'dtingreso_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'dtmovimiento_id',
        'tiempo_contrato',
        'periodo_prueba',
        'numero_contrato',
        'fecha_ingreso',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'dtingreso_id' => 'integer',
        'dtmovimiento_id' => 'integer',
        'tiempo_contrato' => 'string',
        'periodo_prueba' => 'boolean',
        'numero_contrato' => 'string',
        'fecha_ingreso' => 'date',
    ];

    /**
     * @return BelongsTo<DetalleMovimientoModel, $this>
     */
    public function detalleMovimiento(): BelongsTo
    {
        return $this->belongsTo(DetalleMovimientoModel::class, 'dtmovimiento_id', 'dtmovimiento_id');
    }
}
