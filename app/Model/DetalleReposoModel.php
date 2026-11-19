<?php

declare(strict_types=1);

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Modelo Eloquent de la tabla `detalle_reposo`.
 *
 * @property int $dtreposo_id
 * @property int $ausencia_id
 * @property int $tpreposo_id
 * @property string $descripcion
 * @property int|null $tipo_institucion_emisora_id
 * @property \Carbon\CarbonImmutable $fecha_solicitud
 * @property string|null $doctor
 */
class DetalleReposoModel extends Model
{
    /** @var string */
    protected $table = 'detalle_reposo';

    /** @var string */
    protected $primaryKey = 'dtreposo_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'ausencia_id',
        'tpreposo_id',
        'descripcion',
        'tipo_institucion_emisora_id',
        'fecha_solicitud',
        'doctor',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'dtreposo_id' => 'integer',
        'ausencia_id' => 'integer',
        'tpreposo_id' => 'integer',
        'descripcion' => 'string',
        'tipo_institucion_emisora_id' => 'integer',
        'fecha_solicitud' => 'immutable_datetime',
        'doctor' => 'string',
    ];

    /**
     * @return BelongsTo<AusenciaJustificadaModel, $this>
     */
    public function ausenciaJustificada(): BelongsTo
    {
        return $this->belongsTo(AusenciaJustificadaModel::class, 'ausencia_id', 'ausencia_id');
    }

    /**
     * @return BelongsTo<TipoRepososModel, $this>
     */
    public function tipoReposo(): BelongsTo
    {
        return $this->belongsTo(TipoRepososModel::class, 'tpreposo_id', 'tpreposo_id');
    }

    /**
     * @return BelongsTo<TipoInstitucionEmisoraModel, $this>
     */
    public function tipoInstitucionEmisora(): BelongsTo
    {
        return $this->belongsTo(TipoInstitucionEmisoraModel::class, 'tipo_institucion_emisora_id', 'tpinstitucion_emisora_id');
    }

    /**
     * @return HasOne<DetalleReposoValidadosModel, $this>
     */
    public function validacion(): HasOne
    {
        return $this->hasOne(DetalleReposoValidadosModel::class, 'dtreposo_id', 'dtreposo_id');
    }

    /**
     * @return HasMany<DetalleSolicitudReposoModel, $this>
     */
    public function detallesSolicitudReposo(): HasMany
    {
        return $this->hasMany(DetalleSolicitudReposoModel::class, 'dtreposo_id', 'dtreposo_id');
    }
}
