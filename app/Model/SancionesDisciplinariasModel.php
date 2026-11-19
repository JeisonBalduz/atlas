<?php

declare(strict_types=1);

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo Eloquent de la tabla `sanciones_disciplinarias`.
 *
 * @property int $sanciones_disciplinaria_id
 * @property int $tpsanciones_id
 * @property int $empleado_id
 * @property string $motivo
 * @property \Illuminate\Support\Carbon $fecha_incidente
 * @property \Illuminate\Support\Carbon $fecha_sancion
 * @property string $num_memorandum
 */
class SancionesDisciplinariasModel extends Model
{
    /** @var string */
    protected $table = 'sanciones_disciplinarias';

    /** @var string */
    protected $primaryKey = 'sanciones_disciplinaria_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'tpsanciones_id',
        'empleado_id',
        'motivo',
        'fecha_incidente',
        'fecha_sancion',
        'num_memorandum',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'sanciones_disciplinaria_id' => 'integer',
        'tpsanciones_id' => 'integer',
        'empleado_id' => 'integer',
        'motivo' => 'string',
        'fecha_incidente' => 'date',
        'fecha_sancion' => 'date',
        'num_memorandum' => 'string',
    ];

    /**
     * @return BelongsTo<TipoSancionesModel, $this>
     */
    public function tipoSancion(): BelongsTo
    {
        return $this->belongsTo(TipoSancionesModel::class, 'tpsanciones_id', 'tpsanciones_id');
    }

    /**
     * @return BelongsTo<EmpleadosModel, $this>
     */
    public function empleado(): BelongsTo
    {
        return $this->belongsTo(EmpleadosModel::class, 'empleado_id', 'empleado_id');
    }
}
