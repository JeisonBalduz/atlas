<?php

declare(strict_types=1);

namespace App\Model\Vacaciones;

use App\Model\Personas\EmpleadosModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Eloquent de la tabla `solicitud_vacaciones`.
 *
 * @property int $solicitud_vacaciones_id
 * @property int $dias_solicitado
 * @property int $dias_afectados
 * @property \Illuminate\Support\Carbon $fecha_inicio
 * @property \Illuminate\Support\Carbon $fecha_final
 * @property int|null $estatus
 * @property int|null $empleado_id
 * @property bool|null $gerencia
 */
class SolicitudVacacionesModel extends Model
{
    /** @var string */
    protected $table = 'solicitud_vacaciones';

    /** @var string */
    protected $primaryKey = 'solicitud_vacaciones_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'dias_solicitado',
        'dias_afectados',
        'fecha_inicio',
        'fecha_final',
        'estatus',
        'empleado_id',
        'gerencia',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'solicitud_vacaciones_id' => 'integer',
        'dias_solicitado' => 'integer',
        'dias_afectados' => 'integer',
        'fecha_inicio' => 'date',
        'fecha_final' => 'date',
        'estatus' => 'integer',
        'empleado_id' => 'integer',
        'gerencia' => 'boolean',
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
        return $this->hasMany(DetalleVacacionesModel::class, 'solicitud_vacaciones_id', 'solicitud_vacaciones_id');
    }

    /**
     * @return HasMany<HistorialVacacionesModel, $this>
     */
    public function historialVacaciones(): HasMany
    {
        return $this->hasMany(HistorialVacacionesModel::class, 'solicitud_vacaciones_id', 'solicitud_vacaciones_id');
    }

    /**
     * @return HasMany<DetalleSolicitudReposoModel, $this>
     */
    public function detallesSolicitudReposo(): HasMany
    {
        return $this->hasMany(DetalleSolicitudReposoModel::class, 'solicitud_vacaciones_id', 'solicitud_vacaciones_id');
    }
}
