<?php

declare(strict_types=1);

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo Eloquent de la tabla `rol_permiso`.
 *
 * @property int $rol_permiso_id
 * @property int $rol_id
 * @property int $permiso_id
 */
class RolPermisoModel extends Model
{
    /** @var string */
    protected $table = 'rol_permiso';

    /** @var string */
    protected $primaryKey = 'rol_permiso_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'rol_id',
        'permiso_id',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'rol_permiso_id' => 'integer',
        'rol_id' => 'integer',
        'permiso_id' => 'integer',
    ];

    /**
     * @return BelongsTo<RolModel, $this>
     */
    public function rol(): BelongsTo
    {
        return $this->belongsTo(RolModel::class, 'rol_id', 'rol_id');
    }

    /**
     * @return BelongsTo<PermisosModel, $this>
     */
    public function permiso(): BelongsTo
    {
        return $this->belongsTo(PermisosModel::class, 'permiso_id', 'permisos_id');
    }
}
