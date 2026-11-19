<?php

declare(strict_types=1);

namespace App\Model\Ubicacion;

use App\Model\Catalogos\DependenciasModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Eloquent de la tabla `estados`.
 *
 * @property int $estado_id
 * @property string $estado
 */
class EstadosModel extends Model
{
    /** @var string */
    protected $table = 'estados';

    /** @var string */
    protected $primaryKey = 'estado_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'estado',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'estado_id' => 'integer',
        'estado' => 'string',
    ];

    /**
     * @return HasMany<MunicipiosModel, $this>
     */
    public function municipios(): HasMany
    {
        return $this->hasMany(MunicipiosModel::class, 'estado_id', 'estado_id');
    }

    /**
     * @return HasMany<CiudadesModel, $this>
     */
    public function ciudades(): HasMany
    {
        return $this->hasMany(CiudadesModel::class, 'estado_id', 'estado_id');
    }

    /**
     * @return HasMany<DependenciasModel, $this>
     */
    public function dependencias(): HasMany
    {
        return $this->hasMany(DependenciasModel::class, 'estado_id', 'estado_id');
    }
}
