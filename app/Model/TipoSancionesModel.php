<?php

declare(strict_types=1);

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Eloquent de la tabla `tipo_sanciones`.
 *
 * @property int $tpsanciones_id
 * @property string $tipo_sancion
 */
class TipoSancionesModel extends Model
{
    /** @var string */
    protected $table = 'tipo_sanciones';

    /** @var string */
    protected $primaryKey = 'tpsanciones_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'tipo_sancion',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'tpsanciones_id' => 'integer',
        'tipo_sancion' => 'string',
    ];

    /**
     * @return HasMany<SancionesDisciplinariasModel, $this>
     */
    public function sancionesDisciplinarias(): HasMany
    {
        return $this->hasMany(SancionesDisciplinariasModel::class, 'tpsanciones_id', 'tpsanciones_id');
    }
}
