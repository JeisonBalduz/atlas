<?php

declare(strict_types=1);

namespace App\Model\Tramites;

use App\Model\Personas\DocumentosModel;
use App\Model\Personas\PersonasModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Eloquent de la tabla `tramite`.
 *
 * @property int $tramite_id
 * @property int $persona_id
 * @property int $tipo_tramite_id
 * @property string $estatus_global
 * @property \Carbon\CarbonImmutable $fecha
 */
class TramiteModel extends Model
{
    /** @var string */
    protected $table = 'tramite';

    /** @var string */
    protected $primaryKey = 'tramite_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'persona_id',
        'tipo_tramite_id',
        'estatus_global',
        'fecha',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'tramite_id' => 'integer',
        'persona_id' => 'integer',
        'tipo_tramite_id' => 'integer',
        'estatus_global' => 'string',
        'fecha' => 'immutable_datetime',
    ];

    /**
     * @return BelongsTo<PersonasModel, $this>
     */
    public function persona(): BelongsTo
    {
        return $this->belongsTo(PersonasModel::class, 'persona_id', 'persona_id');
    }

    /**
     * @return BelongsTo<TipoTramiteModel, $this>
     */
    public function tipoTramite(): BelongsTo
    {
        return $this->belongsTo(TipoTramiteModel::class, 'tipo_tramite_id', 'tptramite_id');
    }

    /**
     * @return HasMany<SeguimientoTramiteModel, $this>
     */
    public function seguimientos(): HasMany
    {
        return $this->hasMany(SeguimientoTramiteModel::class, 'tramite_id', 'tramite_id');
    }

    /**
     * @return BelongsToMany<DocumentosModel, $this>
     */
    public function documentos(): BelongsToMany
    {
        return $this->belongsToMany(DocumentosModel::class, 'tramite_documento', 'tramite_id', 'documento_id', 'tramite_id', 'documentos_id');
    }
}
