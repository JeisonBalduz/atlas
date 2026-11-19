<?php

declare(strict_types=1);

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Eloquent de la tabla `tipo_tramite`.
 *
 * @property int $tptramite_id
 * @property string $tipo_tramite
 */
class TipoTramiteModel extends Model
{
    /** @var string */
    protected $table = 'tipo_tramite';

    /** @var string */
    protected $primaryKey = 'tptramite_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'tipo_tramite',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'tptramite_id' => 'integer',
        'tipo_tramite' => 'string',
    ];

    /**
     * @return HasMany<TramiteModel, $this>
     */
    public function tramites(): HasMany
    {
        return $this->hasMany(TramiteModel::class, 'tipo_tramite_id', 'tptramite_id');
    }
}
