<?php

declare(strict_types=1);

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo Eloquent de la tabla `auditoria`.
 *
 * @property int $auditoria_id
 * @property int $usuario_id
 * @property string $codigo
 * @property string $accion_realizada
 * @property string $navegador
 * @property string|null $sistema_operativo
 * @property string $ip
 * @property \Carbon\CarbonImmutable $fecha_creacion
 * @property string|null $descripcion
 */
class AuditoriaModel extends Model
{
    /** @var string */
    protected $table = 'auditoria';

    /** @var string */
    protected $primaryKey = 'auditoria_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'usuario_id',
        'codigo',
        'accion_realizada',
        'navegador',
        'sistema_operativo',
        'ip',
        'fecha_creacion',
        'descripcion',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'auditoria_id' => 'integer',
        'usuario_id' => 'integer',
        'codigo' => 'string',
        'accion_realizada' => 'string',
        'navegador' => 'string',
        'sistema_operativo' => 'string',
        'ip' => 'string',
        'fecha_creacion' => 'immutable_datetime',
        'descripcion' => 'string',
    ];

    /**
     * @return BelongsTo<UsuariosModel, $this>
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(UsuariosModel::class, 'usuario_id', 'usuario_id');
    }
}
