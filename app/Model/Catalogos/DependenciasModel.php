<?php

declare(strict_types=1);

namespace App\Model\Catalogos;

use App\Model\Movimientos\DetalleTrasladoModel;
use App\Model\Personas\EmpleadosModel;
use App\Model\Tramites\PostulacionModel;
use App\Model\Ubicacion\EstadosModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Eloquent de la tabla `dependencias`.
 *
 * @property int $dependencia_id
 * @property string $dependencia
 * @property string $codigo
 * @property bool|null $activo
 * @property int $estado_id
 */
class DependenciasModel extends Model
{
    /** @var string */
    protected $table = 'dependencias';

    /** @var string */
    protected $primaryKey = 'dependencia_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'dependencia',
        'codigo',
        'activo',
        'estado_id',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'dependencia_id' => 'integer',
        'dependencia' => 'string',
        'codigo' => 'string',
        'activo' => 'boolean',
        'estado_id' => 'integer',
    ];

    /**
     * @param Builder<static> $query
     * @return Builder<static>
     */
    public function scopeActivo(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    /**
     * @return BelongsTo<EstadosModel, $this>
     */
    public function estado(): BelongsTo
    {
        return $this->belongsTo(EstadosModel::class, 'estado_id', 'estado_id');
    }

    /**
     * @return HasMany<EmpleadosModel, $this>
     */
    public function empleados(): HasMany
    {
        return $this->hasMany(EmpleadosModel::class, 'dependencia_id', 'dependencia_id');
    }

    /**
     * @return HasMany<PostulacionModel, $this>
     */
    public function postulaciones(): HasMany
    {
        return $this->hasMany(PostulacionModel::class, 'dependencia_id', 'dependencia_id');
    }

    /**
     * @return HasMany<DetalleTrasladoModel, $this>
     */
    public function trasladosOrigen(): HasMany
    {
        return $this->hasMany(DetalleTrasladoModel::class, 'dependencia_actual_id', 'dependencia_id');
    }

    /**
     * @return HasMany<DetalleTrasladoModel, $this>
     */
    public function trasladosDestino(): HasMany
    {
        return $this->hasMany(DetalleTrasladoModel::class, 'dependencia_destino_id', 'dependencia_id');
    }
}
