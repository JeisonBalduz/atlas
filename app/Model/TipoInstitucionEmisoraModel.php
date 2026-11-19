<?php

declare(strict_types=1);

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Eloquent de la tabla `tipo_institucion_emisora`.
 *
 * @property int $tpinstitucion_emisora_id
 * @property string $institucion
 */
class TipoInstitucionEmisoraModel extends Model
{
    /** @var string */
    protected $table = 'tipo_institucion_emisora';

    /** @var string */
    protected $primaryKey = 'tpinstitucion_emisora_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'institucion',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'tpinstitucion_emisora_id' => 'integer',
        'institucion' => 'string',
    ];

    /**
     * @return HasMany<DetalleReposoModel, $this>
     */
    public function detallesReposo(): HasMany
    {
        return $this->hasMany(DetalleReposoModel::class, 'tipo_institucion_emisora_id', 'tpinstitucion_emisora_id');
    }

    /**
     * @return HasMany<DetalleReposoValidadosModel, $this>
     */
    public function detallesReposoValidados(): HasMany
    {
        return $this->hasMany(DetalleReposoValidadosModel::class, 'tpinstitucion_emisora_id', 'tpinstitucion_emisora_id');
    }
}
