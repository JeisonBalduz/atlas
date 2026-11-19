<?php

declare(strict_types=1);

namespace App\Model\Personas;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo Eloquent de la tabla `talla_personas`.
 *
 * @property int $talla_persona_id
 * @property int $persona_id
 * @property string|null $talla_camisa
 * @property string|null $talla_pantalon
 * @property string|null $talla_calzado
 * @property string|null $talla_franela
 * @property string|null $descripcion
 */
class TallaPersonasModel extends Model
{
    /** @var string */
    protected $table = 'talla_personas';

    /** @var string */
    protected $primaryKey = 'talla_persona_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'persona_id',
        'talla_camisa',
        'talla_pantalon',
        'talla_calzado',
        'talla_franela',
        'descripcion',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'talla_persona_id' => 'integer',
        'persona_id' => 'integer',
        'talla_camisa' => 'string',
        'talla_pantalon' => 'string',
        'talla_calzado' => 'string',
        'talla_franela' => 'string',
        'descripcion' => 'string',
    ];

    /**
     * @return BelongsTo<PersonasModel, $this>
     */
    public function persona(): BelongsTo
    {
        return $this->belongsTo(PersonasModel::class, 'persona_id', 'persona_id');
    }
}
