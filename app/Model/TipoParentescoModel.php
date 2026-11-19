<?php

declare(strict_types=1);

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Eloquent de la tabla `tipo_parentesco`.
 *
 * @property int $parentesco_id
 * @property string $parentesco
 */
class TipoParentescoModel extends Model
{
    /** @var string */
    protected $table = 'tipo_parentesco';

    /** @var string */
    protected $primaryKey = 'parentesco_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'parentesco',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'parentesco_id' => 'integer',
        'parentesco' => 'string',
    ];

    /**
     * @return HasMany<FamiliaresModel, $this>
     */
    public function familiares(): HasMany
    {
        return $this->hasMany(FamiliaresModel::class, 'parentesco_id', 'parentesco_id');
    }
}
