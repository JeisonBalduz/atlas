<?php

declare(strict_types=1);

namespace App\Model\Vacaciones;

use App\Model\Personas\EmpleadosModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Eloquent de la tabla `periodo_vacaciones`.
 *
 * @property int $periodo_vacaciones_id
 * @property int $empleado_id
 * @property int $periodo
 * @property string $estado_vacaciones
 * @property int $dias_pendientes
 * @property int $dias_disfrutados
 * @property int $dias_correspondientes
 */
class PeriodoVacacionesModel extends Model
{
    /** @var string */
    protected $table = 'periodo_vacaciones';

    /** @var string */
    protected $primaryKey = 'periodo_vacaciones_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'empleado_id',
        'periodo',
        'estado_vacaciones',
        'dias_pendientes',
        'dias_disfrutados',
        'dias_correspondientes',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'periodo_vacaciones_id' => 'integer',
        'empleado_id' => 'integer',
        'periodo' => 'integer',
        'estado_vacaciones' => 'string',
        'dias_pendientes' => 'integer',
        'dias_disfrutados' => 'integer',
        'dias_correspondientes' => 'integer',
    ];

    /**
     * @return BelongsTo<EmpleadosModel, $this>
     */
    public function empleado(): BelongsTo
    {
        return $this->belongsTo(EmpleadosModel::class, 'empleado_id', 'empleado_id');
    }

    /**
     * @return HasMany<DetalleVacacionesModel, $this>
     */
    public function detallesVacaciones(): HasMany
    {
        return $this->hasMany(DetalleVacacionesModel::class, 'periodo_vacaciones_id', 'periodo_vacaciones_id');
    }
}
