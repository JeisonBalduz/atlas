<?php

declare(strict_types=1);

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo Eloquent de la tabla `seguimiento_tramite`.
 *
 * @property int $seguimiento_id
 * @property int $tramite_id
 * @property int $usuario_ejecutador_id
 * @property int $usuario_aprobador_id
 * @property string $estatus_tramite
 * @property string|null $descripcion
 * @property \Carbon\CarbonImmutable $fecha_tramite
 */
class SeguimientoTramiteModel extends Model
{
    /** @var string */
    protected $table = 'seguimiento_tramite';

    /** @var string */
    protected $primaryKey = 'seguimiento_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'tramite_id',
        'usuario_ejecutador_id',
        'usuario_aprobador_id',
        'estatus_tramite',
        'descripcion',
        'fecha_tramite',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'seguimiento_id' => 'integer',
        'tramite_id' => 'integer',
        'usuario_ejecutador_id' => 'integer',
        'usuario_aprobador_id' => 'integer',
        'estatus_tramite' => 'string',
        'descripcion' => 'string',
        'fecha_tramite' => 'immutable_datetime',
    ];

    /**
     * @return BelongsTo<TramiteModel, $this>
     */
    public function tramite(): BelongsTo
    {
        return $this->belongsTo(TramiteModel::class, 'tramite_id', 'tramite_id');
    }

    /**
     * @return BelongsTo<UsuariosModel, $this>
     */
    public function usuarioEjecutador(): BelongsTo
    {
        return $this->belongsTo(UsuariosModel::class, 'usuario_ejecutador_id', 'usuario_id');
    }

    /**
     * @return BelongsTo<UsuariosModel, $this>
     */
    public function usuarioAprobador(): BelongsTo
    {
        return $this->belongsTo(UsuariosModel::class, 'usuario_aprobador_id', 'usuario_id');
    }
}
