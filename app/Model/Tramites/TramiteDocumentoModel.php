<?php

declare(strict_types=1);

namespace App\Model\Tramites;

use App\Model\Personas\DocumentosModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo Eloquent de la tabla `tramite_documento`.
 *
 * @property int $tramite_id
 * @property int $documento_id
 */
class TramiteDocumentoModel extends Model
{
    /** @var string */
    protected $table = 'tramite_documento';

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
        'tramite_id',
        'documento_id',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'tramite_id' => 'integer',
        'documento_id' => 'integer',
    ];

    /**
     * @return BelongsTo<TramiteModel, $this>
     */
    public function tramite(): BelongsTo
    {
        return $this->belongsTo(TramiteModel::class, 'tramite_id', 'tramite_id');
    }

    /**
     * @return BelongsTo<DocumentosModel, $this>
     */
    public function documento(): BelongsTo
    {
        return $this->belongsTo(DocumentosModel::class, 'documento_id', 'documentos_id');
    }
}
