<?php

declare(strict_types=1);

namespace App\Model\Personas;

use App\Model\Catalogos\TipoParentescoModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo Eloquent de la tabla `familiares`.
 *
 * @property int $familia_id
 * @property int $empleado_id
 * @property int $persona_id
 * @property int $parentesco_id
 * @property int $tomo
 * @property int $folio
 * @property string|null $genero
 */
class FamiliaresModel extends Model
{
    /** @var string */
    protected $table = 'familiares';

    /** @var string */
    protected $primaryKey = 'familia_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'empleado_id',
        'persona_id',
        'parentesco_id',
        'tomo',
        'folio',
        'genero',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'familia_id' => 'integer',
        'empleado_id' => 'integer',
        'persona_id' => 'integer',
        'parentesco_id' => 'integer',
        'tomo' => 'integer',
        'folio' => 'integer',
        'genero' => 'string',
    ];

    /**
     * @return BelongsTo<EmpleadosModel, $this>
     */
    public function empleado(): BelongsTo
    {
        return $this->belongsTo(EmpleadosModel::class, 'empleado_id', 'empleado_id');
    }

    /**
     * @return BelongsTo<PersonasModel, $this>
     */
    public function persona(): BelongsTo
    {
        return $this->belongsTo(PersonasModel::class, 'persona_id', 'persona_id');
    }

    /**
     * @return BelongsTo<TipoParentescoModel, $this>
     */
    public function tipoParentesco(): BelongsTo
    {
        return $this->belongsTo(TipoParentescoModel::class, 'parentesco_id', 'parentesco_id');
    }
}
