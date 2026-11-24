<?php

declare(strict_types=1);

namespace App\Model\Catalogos;

use App\Model\Movimientos\DetalleTrasladoModel;
use App\Model\Personas\EmpleadosModel;
use App\Model\Tramites\PostulacionModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Eloquent de la tabla `departamentos`.
 *
 * @property int $departamento_id
 * @property string $departamento
 * @property bool $activo
 */
class DepartamentosModel extends Model
{
    /** @var string */
    protected $table = 'departamentos';

    /** @var string */
    protected $primaryKey = 'departamento_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'departamento',
        'activo',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'departamento_id' => 'integer',
        'departamento' => 'string',
        'activo' => 'boolean',
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
     * @return HasMany<EmpleadosModel, $this>
     */
    public function empleados(): HasMany
    {
        return $this->hasMany(EmpleadosModel::class, 'departamento_id', 'departamento_id');
    }

    /**
     * @return HasMany<PostulacionModel, $this>
     */
    public function postulaciones(): HasMany
    {
        return $this->hasMany(PostulacionModel::class, 'departamento_id', 'departamento_id');
    }

    /**
     * @return HasMany<DetalleTrasladoModel, $this>
     */
    public function trasladosOrigen(): HasMany
    {
        return $this->hasMany(DetalleTrasladoModel::class, 'departamento_actual_id', 'departamento_id');
    }

    /**
     * @return HasMany<DetalleTrasladoModel, $this>
     */
    public function trasladosDestino(): HasMany
    {
        return $this->hasMany(DetalleTrasladoModel::class, 'departamento_destino_id', 'departamento_id');
    }
}
