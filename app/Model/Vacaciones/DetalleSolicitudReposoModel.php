<?php

declare(strict_types=1);

namespace App\Model\Vacaciones;

use App\Model\Asistencia\DetalleReposoModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo Eloquent de la tabla `detalle_solicitud_reposo`.
 *
 * @property int $dtsolicitud_reposo_id
 * @property int $solicitud_vacaciones_id
 * @property int $dtreposo_id
 * @property int $dias_afectados
 * @property \Carbon\CarbonImmutable|null $fecha_registro
 */
class DetalleSolicitudReposoModel extends Model
{
    /** @var string */
    protected $table = 'detalle_solicitud_reposo';

    /** @var string */
    protected $primaryKey = 'dtsolicitud_reposo_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'solicitud_vacaciones_id',
        'dtreposo_id',
        'dias_afectados',
        'fecha_registro',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'dtsolicitud_reposo_id' => 'integer',
        'solicitud_vacaciones_id' => 'integer',
        'dtreposo_id' => 'integer',
        'dias_afectados' => 'integer',
        'fecha_registro' => 'immutable_datetime',
    ];

    /**
     * @return BelongsTo<SolicitudVacacionesModel, $this>
     */
    public function solicitudVacaciones(): BelongsTo
    {
        return $this->belongsTo(SolicitudVacacionesModel::class, 'solicitud_vacaciones_id', 'solicitud_vacaciones_id');
    }

    /**
     * @return BelongsTo<DetalleReposoModel, $this>
     */
    public function detalleReposo(): BelongsTo
    {
        return $this->belongsTo(DetalleReposoModel::class, 'dtreposo_id', 'dtreposo_id');
    }
}
