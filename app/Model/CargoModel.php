<?php

declare(strict_types=1);

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Eloquent de la tabla `cargo`.
 *
 * @property int $cargo_id
 * @property string $cargo
 * @property bool $activo
 */
class CargoModel extends Model
{
    /** @var string */
    protected $table = 'cargo';

    /** @var string */
    protected $primaryKey = 'cargo_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'cargo',
        'activo',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'cargo_id' => 'integer',
        'cargo' => 'string',
        'activo' => 'boolean',
    ];

    /**
     * @return HasMany<EmpleadosModel, $this>
     */
    public function empleados(): HasMany
    {
        return $this->hasMany(EmpleadosModel::class, 'cargo_id', 'cargo_id');
    }

    /**
     * @return HasMany<PostulacionModel, $this>
     */
    public function postulaciones(): HasMany
    {
        return $this->hasMany(PostulacionModel::class, 'cargo_id', 'cargo_id');
    }
}
