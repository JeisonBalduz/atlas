<?php

declare(strict_types=1);

namespace App\Model\Catalogos;

use App\Model\Personas\PersonasModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Eloquent de la tabla `estado_civil`.
 *
 * @property int $estado_civil_id
 * @property string $estado_civil
 */
class EstadoCivilModel extends Model
{
    /** @var string */
    protected $table = 'estado_civil';

    /** @var string */
    protected $primaryKey = 'estado_civil_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'estado_civil',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'estado_civil_id' => 'integer',
        'estado_civil' => 'string',
    ];

    /**
     * @return HasMany<PersonasModel, $this>
     */
    public function personas(): HasMany
    {
        return $this->hasMany(PersonasModel::class, 'estado_civil_id', 'estado_civil_id');
    }
}
