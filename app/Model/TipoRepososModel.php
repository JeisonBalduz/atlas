<?php

declare(strict_types=1);

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Eloquent de la tabla `tipo_reposos`.
 *
 * @property int $tpreposo_id
 * @property string $reposo
 */
class TipoRepososModel extends Model
{
    /** @var string */
    protected $table = 'tipo_reposos';

    /** @var string */
    protected $primaryKey = 'tpreposo_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'reposo',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'tpreposo_id' => 'integer',
        'reposo' => 'string',
    ];

    /**
     * @return HasMany<DetalleReposoModel, $this>
     */
    public function detallesReposo(): HasMany
    {
        return $this->hasMany(DetalleReposoModel::class, 'tpreposo_id', 'tpreposo_id');
    }
}
