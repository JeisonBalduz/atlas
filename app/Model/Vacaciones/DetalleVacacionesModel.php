<?php

declare(strict_types=1);

namespace App\Model\Vacaciones;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo Eloquent de la tabla `detalle_vacaciones`.
 *
 * @property int $dt_vacaciones_id
 * @property int $solicitud_vacaciones_id
 * @property int $periodo_vacaciones_id
 * @property int $dia_descontados
 */
class DetalleVacacionesModel extends Model
{
    /** @var string */
    protected $table = 'detalle_vacaciones';

    /** @var string */
    protected $primaryKey = 'dt_vacaciones_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'solicitud_vacaciones_id',
        'periodo_vacaciones_id',
        'dia_descontados',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'dt_vacaciones_id' => 'integer',
        'solicitud_vacaciones_id' => 'integer',
        'periodo_vacaciones_id' => 'integer',
        'dia_descontados' => 'integer',
    ];

    /**
     * @return BelongsTo<SolicitudVacacionesModel, $this>
     */
    public function solicitudVacaciones(): BelongsTo
    {
        return $this->belongsTo(SolicitudVacacionesModel::class, 'solicitud_vacaciones_id', 'solicitud_vacaciones_id');
    }

    /**
     * @return BelongsTo<PeriodoVacacionesModel, $this>
     */
    public function periodoVacaciones(): BelongsTo
    {
        return $this->belongsTo(PeriodoVacacionesModel::class, 'periodo_vacaciones_id', 'periodo_vacaciones_id');
    }
}
