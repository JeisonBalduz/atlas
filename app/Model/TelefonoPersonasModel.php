<?php

declare(strict_types=1);

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo Eloquent de la tabla `telefono_personas`.
 *
 * @property int $telefono_id
 * @property int $persona_id
 * @property string $numero_telefono
 * @property bool|null $emergencia
 */
class TelefonoPersonasModel extends Model
{
    /** @var string */
    protected $table = 'telefono_personas';

    /** @var string */
    protected $primaryKey = 'telefono_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'persona_id',
        'numero_telefono',
        'emergencia',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'telefono_id' => 'integer',
        'persona_id' => 'integer',
        'numero_telefono' => 'string',
        'emergencia' => 'boolean',
    ];

    /**
     * @return BelongsTo<PersonasModel, $this>
     */
    public function persona(): BelongsTo
    {
        return $this->belongsTo(PersonasModel::class, 'persona_id', 'persona_id');
    }
}
