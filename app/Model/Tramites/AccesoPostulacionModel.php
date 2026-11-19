<?php

declare(strict_types=1);

namespace App\Model\Tramites;

use App\Model\Seguridad\UsuariosModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo Eloquent de la tabla `acceso_postulacion`.
 *
 * @property int $acceso_postulacion_id
 * @property int $postulacion_id
 * @property int $usuario_id
 * @property int $visibilidad
 * @property \Carbon\CarbonImmutable $fecha_acceso
 */
class AccesoPostulacionModel extends Model
{
    /** @var string */
    protected $table = 'acceso_postulacion';

    /** @var string */
    protected $primaryKey = 'acceso_postulacion_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'postulacion_id',
        'usuario_id',
        'visibilidad',
        'fecha_acceso',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'acceso_postulacion_id' => 'integer',
        'postulacion_id' => 'integer',
        'usuario_id' => 'integer',
        'visibilidad' => 'integer',
        'fecha_acceso' => 'immutable_datetime',
    ];

    /**
     * @return BelongsTo<PostulacionModel, $this>
     */
    public function postulacion(): BelongsTo
    {
        return $this->belongsTo(PostulacionModel::class, 'postulacion_id', 'postulacion_id');
    }

    /**
     * @return BelongsTo<UsuariosModel, $this>
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(UsuariosModel::class, 'usuario_id', 'usuario_id');
    }
}
