<?php

declare(strict_types=1);

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo Eloquent de la tabla `detalle_asistencia`.
 *
 * @property int $dtasistencia_id
 * @property int $empleado_id
 * @property string $turno
 * @property \Illuminate\Support\Carbon $fecha
 * @property string $hora_entrada
 * @property string|null $hora_salida
 */
class DetalleAsistenciaModel extends Model
{
    /** @var string */
    protected $table = 'detalle_asistencia';

    /** @var string */
    protected $primaryKey = 'dtasistencia_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'empleado_id',
        'turno',
        'fecha',
        'hora_entrada',
        'hora_salida',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'dtasistencia_id' => 'integer',
        'empleado_id' => 'integer',
        'turno' => 'string',
        'fecha' => 'date',
        'hora_entrada' => 'string',
        'hora_salida' => 'string',
    ];

    /**
     * @return BelongsTo<EmpleadosModel, $this>
     */
    public function empleado(): BelongsTo
    {
        return $this->belongsTo(EmpleadosModel::class, 'empleado_id', 'empleado_id');
    }
}
