<?php

declare(strict_types=1);

namespace App\Model\Seguridad;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Modelo Eloquent de la tabla `permisos`.
 *
 * @property int $permisos_id
 * @property string $permiso
 * @property string|null $descripcion
 */
class PermisosModel extends Model
{
    /** @var string */
    protected $table = 'permisos';

    /** @var string */
    protected $primaryKey = 'permisos_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'permiso',
        'descripcion',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'permisos_id' => 'integer',
        'permiso' => 'string',
        'descripcion' => 'string',
    ];

    /**
     * @return BelongsToMany<RolModel, $this>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(RolModel::class, 'rol_permiso', 'permiso_id', 'rol_id', 'permisos_id', 'rol_id');
    }

    /**
     * @return BelongsToMany<UsuariosModel, $this>
     */
    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(UsuariosModel::class, 'usuario_permisos', 'permiso_id', 'usuario_id', 'permisos_id', 'usuario_id');
    }
}
