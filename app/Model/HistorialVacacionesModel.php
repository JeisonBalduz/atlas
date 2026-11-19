<?php

declare(strict_types=1);

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo Eloquent de la tabla `historial_vacaciones`.
 *
 * @property int $historial_id
 * @property int $solicitud_vacaciones_id
 * @property int $estatus_id
 * @property \Carbon\CarbonImmutable|null $fecha_registro
 * @property string|null $observacion
 */
class HistorialVacacionesModel extends Model
{
    /** @var string */
    protected $table = 'historial_vacaciones';

    /** @var string */
    protected $primaryKey = 'historial_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'solicitud_vacaciones_id',
        'estatus_id',
        'fecha_registro',
        'observacion',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'historial_id' => 'integer',
        'solicitud_vacaciones_id' => 'integer',
        'estatus_id' => 'integer',
        'fecha_registro' => 'immutable_datetime',
        'observacion' => 'string',
    ];

    /**
     * @return BelongsTo<SolicitudVacacionesModel, $this>
     */
    public function solicitudVacaciones(): BelongsTo
    {
        return $this->belongsTo(SolicitudVacacionesModel::class, 'solicitud_vacaciones_id', 'solicitud_vacaciones_id');
    }
}
