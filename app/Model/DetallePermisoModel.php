<?php

declare(strict_types=1);

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo Eloquent de la tabla `detalle_permiso`.
 *
 * @property int $dtpermiso_id
 * @property int $ausencia_id
 * @property int $tppermiso_id
 * @property string $motivo
 * @property bool $remunerable
 * @property \Carbon\CarbonImmutable $fecha_solicitud
 */
class DetallePermisoModel extends Model
{
    /** @var string */
    protected $table = 'detalle_permiso';

    /** @var string */
    protected $primaryKey = 'dtpermiso_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'ausencia_id',
        'tppermiso_id',
        'motivo',
        'remunerable',
        'fecha_solicitud',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'dtpermiso_id' => 'integer',
        'ausencia_id' => 'integer',
        'tppermiso_id' => 'integer',
        'motivo' => 'string',
        'remunerable' => 'boolean',
        'fecha_solicitud' => 'immutable_datetime',
    ];

    /**
     * @return BelongsTo<AusenciaJustificadaModel, $this>
     */
    public function ausenciaJustificada(): BelongsTo
    {
        return $this->belongsTo(AusenciaJustificadaModel::class, 'ausencia_id', 'ausencia_id');
    }

    /**
     * @return BelongsTo<TipoPermisosModel, $this>
     */
    public function tipoPermiso(): BelongsTo
    {
        return $this->belongsTo(TipoPermisosModel::class, 'tppermiso_id', 'tppermiso_id');
    }
}
