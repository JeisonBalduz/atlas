<?php

declare(strict_types=1);

namespace App\Model\Asistencia;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Eloquent de la tabla `tipo_permisos`.
 *
 * @property int $tppermiso_id
 * @property string $permiso
 */
class TipoPermisosModel extends Model
{
    /** @var string */
    protected $table = 'tipo_permisos';

    /** @var string */
    protected $primaryKey = 'tppermiso_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'permiso',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'tppermiso_id' => 'integer',
        'permiso' => 'string',
    ];

    /**
     * @return HasMany<DetallePermisoModel, $this>
     */
    public function detallesPermiso(): HasMany
    {
        return $this->hasMany(DetallePermisoModel::class, 'tppermiso_id', 'tppermiso_id');
    }
}
