<?php

declare(strict_types=1);

namespace App\Model\Catalogos;

use App\Model\Personas\DocumentosModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Eloquent de la tabla `tipo_documentos`.
 *
 * @property int $tpdocumentos_id
 * @property string $tipo_documentos
 */
class TipoDocumentosModel extends Model
{
    /** @var string */
    protected $table = 'tipo_documentos';

    /** @var string */
    protected $primaryKey = 'tpdocumentos_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'tipo_documentos',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'tpdocumentos_id' => 'integer',
        'tipo_documentos' => 'string',
    ];

    /**
     * @return HasMany<DocumentosModel, $this>
     */
    public function documentos(): HasMany
    {
        return $this->hasMany(DocumentosModel::class, 'tipo_documento_id', 'tpdocumentos_id');
    }
}
