<?php

declare(strict_types=1);

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo Eloquent de la tabla `direccion`.
 *
 * @property int $direccion_id
 * @property int $empleado_id
 * @property int $parroquia_id
 * @property string $vivienda
 * @property string $calle
 * @property string $nombre_urb
 * @property string $piso_edificio
 * @property string $numero_vivienda
 * @property string|null $descripcion
 */
class DireccionModel extends Model
{
    /** @var string */
    protected $table = 'direccion';

    /** @var string */
    protected $primaryKey = 'direccion_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'empleado_id',
        'parroquia_id',
        'vivienda',
        'calle',
        'nombre_urb',
        'piso_edificio',
        'numero_vivienda',
        'descripcion',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'direccion_id' => 'integer',
        'empleado_id' => 'integer',
        'parroquia_id' => 'integer',
        'vivienda' => 'string',
        'calle' => 'string',
        'nombre_urb' => 'string',
        'piso_edificio' => 'string',
        'numero_vivienda' => 'string',
        'descripcion' => 'string',
    ];

    /**
     * @return BelongsTo<EmpleadosModel, $this>
     */
    public function empleado(): BelongsTo
    {
        return $this->belongsTo(EmpleadosModel::class, 'empleado_id', 'empleado_id');
    }

    /**
     * @return BelongsTo<ParroquiasModel, $this>
     */
    public function parroquia(): BelongsTo
    {
        return $this->belongsTo(ParroquiasModel::class, 'parroquia_id', 'parroquia_id');
    }
}
