<?php

declare(strict_types=1);

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Eloquent de la tabla `estatus`.
 *
 * @property int $estatus_id
 * @property string $estatus
 * @property bool $activo
 */
class EstatusModel extends Model
{
    /** @var string */
    protected $table = 'estatus';

    /** @var string */
    protected $primaryKey = 'estatus_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'estatus',
        'activo',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'estatus_id' => 'integer',
        'estatus' => 'string',
        'activo' => 'boolean',
    ];

    /**
     * @return HasMany<EmpleadosModel, $this>
     */
    public function empleados(): HasMany
    {
        return $this->hasMany(EmpleadosModel::class, 'estatus_id', 'estatus_id');
    }

    /**
     * @return array<int, string>
     */
    public static function todoDatosEstatus(): array
    {
        return static::query()
            ->whereNotNull('estatus_id')
            ->pluck('estatus', 'estatus_id')
            ->toArray();
    }
}
