<?php

declare(strict_types=1);

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Modelo Eloquent de la tabla `ausencia_justificada`.
 *
 * @property int $ausencia_id
 * @property int $empleado_id
 * @property \Illuminate\Support\Carbon $fecha_inicio
 * @property \Illuminate\Support\Carbon $fecha_fin
 * @property int $dias_ausencia
 * @property int $tipo_ausencia
 */
class AusenciaJustificadaModel extends Model
{
    public const TIPO_PERMISO = 1;
    public const TIPO_REPOSO = 2;

    /** @var string */
    protected $table = 'ausencia_justificada';

    /** @var string */
    protected $primaryKey = 'ausencia_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'empleado_id',
        'fecha_inicio',
        'fecha_fin',
        'dias_ausencia',
        'tipo_ausencia',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'ausencia_id' => 'integer',
        'empleado_id' => 'integer',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'dias_ausencia' => 'integer',
        'tipo_ausencia' => 'integer',
    ];

    /**
     * @return BelongsTo<EmpleadosModel, $this>
     */
    public function empleado(): BelongsTo
    {
        return $this->belongsTo(EmpleadosModel::class, 'empleado_id', 'empleado_id');
    }

    /**
     * @return HasOne<DetallePermisoModel, $this>
     */
    public function detallePermiso(): HasOne
    {
        return $this->hasOne(DetallePermisoModel::class, 'ausencia_id', 'ausencia_id');
    }

    /**
     * @return HasOne<DetalleReposoModel, $this>
     */
    public function detalleReposo(): HasOne
    {
        return $this->hasOne(DetalleReposoModel::class, 'ausencia_id', 'ausencia_id');
    }
}
