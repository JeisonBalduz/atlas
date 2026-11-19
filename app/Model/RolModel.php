<?php

declare(strict_types=1);

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Eloquent de la tabla `rol`.
 *
 * @property int $rol_id
 * @property string $rol
 */
class RolModel extends Model
{
    /** @var string */
    protected $table = 'rol';

    /** @var string */
    protected $primaryKey = 'rol_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'rol',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'rol_id' => 'integer',
        'rol' => 'string',
    ];

    /**
     * @return HasMany<UsuariosModel, $this>
     */
    public function usuarios(): HasMany
    {
        return $this->hasMany(UsuariosModel::class, 'rol_id', 'rol_id');
    }

    /**
     * @return BelongsToMany<PermisosModel, $this>
     */
    public function permisos(): BelongsToMany
    {
        return $this->belongsToMany(PermisosModel::class, 'rol_permiso', 'rol_id', 'permiso_id', 'rol_id', 'permisos_id');
    }
}
