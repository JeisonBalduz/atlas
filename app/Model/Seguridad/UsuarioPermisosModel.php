<?php

declare(strict_types=1);

namespace App\Model\Seguridad;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo Eloquent de la tabla `usuario_permisos`.
 *
 * @property int $usuario_id
 * @property int $permiso_id
 */
class UsuarioPermisosModel extends Model
{
    /** @var string */
    protected $table = 'usuario_permisos';

    /**
     * Llave primaria compuesta: sin columna autoincremental.
     * Eloquent tipa $primaryKey como string; null es intencional en tablas pivote.
     */
    protected $primaryKey = null;

    /** @var bool */
    public $incrementing = false;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'usuario_id',
        'permiso_id',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'usuario_id' => 'integer',
        'permiso_id' => 'integer',
    ];

    /**
     * @return BelongsTo<UsuariosModel, $this>
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(UsuariosModel::class, 'usuario_id', 'usuario_id');
    }

    /**
     * @return BelongsTo<PermisosModel, $this>
     */
    public function permiso(): BelongsTo
    {
        return $this->belongsTo(PermisosModel::class, 'permiso_id', 'permisos_id');
    }
}
