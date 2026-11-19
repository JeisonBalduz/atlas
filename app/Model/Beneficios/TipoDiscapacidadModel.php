<?php

declare(strict_types=1);

namespace App\Model\Beneficios;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo Eloquent de la tabla `tipo_discapacidad`.
 *
 * @property int $tpdiscapacidad_id
 * @property string $tipo_discapacidad
 */
class TipoDiscapacidadModel extends Model
{
    /** @var string */
    protected $table = 'tipo_discapacidad';

    /** @var string */
    protected $primaryKey = 'tpdiscapacidad_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'tipo_discapacidad',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'tpdiscapacidad_id' => 'integer',
        'tipo_discapacidad' => 'string',
    ];
}
