<?php

declare(strict_types=1);

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo Eloquent de la tabla `detalle_reposo_validados`.
 *
 * @property int $detalle_reposo_validado_id
 * @property int|null $dtreposo_id
 * @property int|null $tpinstitucion_emisora_id
 * @property string|null $codigo
 * @property int|null $num_aseguradora
 * @property \Illuminate\Support\Carbon|null $fecha_expide
 * @property string|null $medico
 * @property int|null $clave
 */
class DetalleReposoValidadosModel extends Model
{
    /** @var string */
    protected $table = 'detalle_reposo_validados';

    /** @var string */
    protected $primaryKey = 'detalle_reposo_validado_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'dtreposo_id',
        'tpinstitucion_emisora_id',
        'codigo',
        'num_aseguradora',
        'fecha_expide',
        'medico',
        'clave',
    ];

    /** @var list<string> */
    protected $hidden = [
        'clave',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'detalle_reposo_validado_id' => 'integer',
        'dtreposo_id' => 'integer',
        'tpinstitucion_emisora_id' => 'integer',
        'codigo' => 'string',
        'num_aseguradora' => 'integer',
        'fecha_expide' => 'date',
        'medico' => 'string',
        'clave' => 'integer',
    ];

    /**
     * @return BelongsTo<DetalleReposoModel, $this>
     */
    public function detalleReposo(): BelongsTo
    {
        return $this->belongsTo(DetalleReposoModel::class, 'dtreposo_id', 'dtreposo_id');
    }

    /**
     * @return BelongsTo<TipoInstitucionEmisoraModel, $this>
     */
    public function tipoInstitucionEmisora(): BelongsTo
    {
        return $this->belongsTo(TipoInstitucionEmisoraModel::class, 'tpinstitucion_emisora_id', 'tpinstitucion_emisora_id');
    }
}
