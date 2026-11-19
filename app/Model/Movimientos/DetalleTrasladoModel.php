<?php

declare(strict_types=1);

namespace App\Model\Movimientos;

use App\Model\Catalogos\DepartamentosModel;
use App\Model\Catalogos\DependenciasModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo Eloquent de la tabla `detalle_traslado`.
 *
 * @property int $dttraslado_id
 * @property int $dtmovimiento_id
 * @property int $departamento_actual_id
 * @property int $departamento_destino_id
 * @property int $dependencia_actual_id
 * @property int $dependencia_destino_id
 * @property string $motivo_traslado
 * @property \Illuminate\Support\Carbon $fecha_notificacion
 * @property \Illuminate\Support\Carbon|null $fecha_efectiva
 */
class DetalleTrasladoModel extends Model
{
    /** @var string */
    protected $table = 'detalle_traslado';

    /** @var string */
    protected $primaryKey = 'dttraslado_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'dtmovimiento_id',
        'departamento_actual_id',
        'departamento_destino_id',
        'dependencia_actual_id',
        'dependencia_destino_id',
        'motivo_traslado',
        'fecha_notificacion',
        'fecha_efectiva',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'dttraslado_id' => 'integer',
        'dtmovimiento_id' => 'integer',
        'departamento_actual_id' => 'integer',
        'departamento_destino_id' => 'integer',
        'dependencia_actual_id' => 'integer',
        'dependencia_destino_id' => 'integer',
        'motivo_traslado' => 'string',
        'fecha_notificacion' => 'date',
        'fecha_efectiva' => 'date',
    ];

    /**
     * @return BelongsTo<DetalleMovimientoModel, $this>
     */
    public function detalleMovimiento(): BelongsTo
    {
        return $this->belongsTo(DetalleMovimientoModel::class, 'dtmovimiento_id', 'dtmovimiento_id');
    }

    /**
     * @return BelongsTo<DepartamentosModel, $this>
     */
    public function departamentoActual(): BelongsTo
    {
        return $this->belongsTo(DepartamentosModel::class, 'departamento_actual_id', 'departamento_id');
    }

    /**
     * @return BelongsTo<DepartamentosModel, $this>
     */
    public function departamentoDestino(): BelongsTo
    {
        return $this->belongsTo(DepartamentosModel::class, 'departamento_destino_id', 'departamento_id');
    }

    /**
     * @return BelongsTo<DependenciasModel, $this>
     */
    public function dependenciaActual(): BelongsTo
    {
        return $this->belongsTo(DependenciasModel::class, 'dependencia_actual_id', 'dependencia_id');
    }

    /**
     * @return BelongsTo<DependenciasModel, $this>
     */
    public function dependenciaDestino(): BelongsTo
    {
        return $this->belongsTo(DependenciasModel::class, 'dependencia_destino_id', 'dependencia_id');
    }
}
