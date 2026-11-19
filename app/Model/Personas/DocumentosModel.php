<?php

declare(strict_types=1);

namespace App\Model\Personas;

use App\Model\Catalogos\TipoDocumentosModel;
use App\Model\Tramites\TramiteModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Modelo Eloquent de la tabla `documentos`.
 *
 * @property int $documentos_id
 * @property int $tipo_documento_id
 * @property int $persona_id
 * @property string|null $codigo
 * @property string $ruta
 * @property string|null $estatus_documento
 * @property string $nombre_original
 * @property int $tamano
 * @property string $formato
 * @property \Carbon\CarbonImmutable|null $fecha_subida
 */
class DocumentosModel extends Model
{
    /** @var string */
    protected $table = 'documentos';

    /** @var string */
    protected $primaryKey = 'documentos_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'tipo_documento_id',
        'persona_id',
        'codigo',
        'ruta',
        'estatus_documento',
        'nombre_original',
        'tamano',
        'formato',
        'fecha_subida',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'documentos_id' => 'integer',
        'tipo_documento_id' => 'integer',
        'persona_id' => 'integer',
        'codigo' => 'string',
        'ruta' => 'string',
        'estatus_documento' => 'string',
        'nombre_original' => 'string',
        'tamano' => 'integer',
        'formato' => 'string',
        'fecha_subida' => 'immutable_datetime',
    ];

    /**
     * @return BelongsTo<TipoDocumentosModel, $this>
     */
    public function tipoDocumento(): BelongsTo
    {
        return $this->belongsTo(TipoDocumentosModel::class, 'tipo_documento_id', 'tpdocumentos_id');
    }

    /**
     * @return BelongsTo<PersonasModel, $this>
     */
    public function persona(): BelongsTo
    {
        return $this->belongsTo(PersonasModel::class, 'persona_id', 'persona_id');
    }

    /**
     * @return BelongsToMany<TramiteModel, $this>
     */
    public function tramites(): BelongsToMany
    {
        return $this->belongsToMany(TramiteModel::class, 'tramite_documento', 'documento_id', 'tramite_id', 'documentos_id', 'tramite_id');
    }
}
