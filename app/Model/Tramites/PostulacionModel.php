<?php

declare(strict_types=1);

namespace App\Model\Tramites;

use App\Model\Catalogos\CargoModel;
use App\Model\Catalogos\DepartamentosModel;
use App\Model\Catalogos\DependenciasModel;
use App\Model\Personas\PersonasModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Eloquent de la tabla `postulacion`.
 *
 * @property int $postulacion_id
 * @property int $persona_id
 * @property int $cargo_id
 * @property int $departamento_id
 * @property int $dependencia_id
 * @property string|null $motivo
 * @property \Carbon\CarbonImmutable $fecha_postulacion
 * @property string $estatus
 */
class PostulacionModel extends Model
{
    /** @var string */
    protected $table = 'postulacion';

    /** @var string */
    protected $primaryKey = 'postulacion_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'persona_id',
        'cargo_id',
        'departamento_id',
        'dependencia_id',
        'motivo',
        'fecha_postulacion',
        'estatus',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'postulacion_id' => 'integer',
        'persona_id' => 'integer',
        'cargo_id' => 'integer',
        'departamento_id' => 'integer',
        'dependencia_id' => 'integer',
        'motivo' => 'string',
        'fecha_postulacion' => 'immutable_datetime',
        'estatus' => 'string',
    ];

    /**
     * @return BelongsTo<PersonasModel, $this>
     */
    public function persona(): BelongsTo
    {
        return $this->belongsTo(PersonasModel::class, 'persona_id', 'persona_id');
    }

    /**
     * @return BelongsTo<CargoModel, $this>
     */
    public function cargo(): BelongsTo
    {
        return $this->belongsTo(CargoModel::class, 'cargo_id', 'cargo_id');
    }

    /**
     * @return BelongsTo<DepartamentosModel, $this>
     */
    public function departamento(): BelongsTo
    {
        return $this->belongsTo(DepartamentosModel::class, 'departamento_id', 'departamento_id');
    }

    /**
     * @return BelongsTo<DependenciasModel, $this>
     */
    public function dependencia(): BelongsTo
    {
        return $this->belongsTo(DependenciasModel::class, 'dependencia_id', 'dependencia_id');
    }

    /**
     * @return HasMany<AccesoPostulacionModel, $this>
     */
    public function accesos(): HasMany
    {
        return $this->hasMany(AccesoPostulacionModel::class, 'postulacion_id', 'postulacion_id');
    }
}
