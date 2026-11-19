<?php

declare(strict_types=1);

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Modelo Eloquent de la tabla `personas`.
 *
 * @property int $persona_id
 * @property string $primer_nombre
 * @property string $segundo_nombre
 * @property string $primer_apellido
 * @property string $segundo_apellido
 * @property string $genero
 * @property int $cedula
 * @property string $nacionalidad
 * @property \Illuminate\Support\Carbon $fecha_nacimiento
 * @property int|null $estado_civil_id
 */
class PersonasModel extends Model
{
    /** @var string */
    protected $table = 'personas';

    /** @var string */
    protected $primaryKey = 'persona_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'primer_nombre',
        'segundo_nombre',
        'primer_apellido',
        'segundo_apellido',
        'genero',
        'cedula',
        'nacionalidad',
        'fecha_nacimiento',
        'estado_civil_id',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'persona_id' => 'integer',
        'primer_nombre' => 'string',
        'segundo_nombre' => 'string',
        'primer_apellido' => 'string',
        'segundo_apellido' => 'string',
        'genero' => 'string',
        'cedula' => 'integer',
        'nacionalidad' => 'string',
        'fecha_nacimiento' => 'date',
        'estado_civil_id' => 'integer',
    ];

    /**
     * @return BelongsTo<EstadoCivilModel, $this>
     */
    public function estadoCivil(): BelongsTo
    {
        return $this->belongsTo(EstadoCivilModel::class, 'estado_civil_id', 'estado_civil_id');
    }

    /**
     * @return HasOne<EmpleadosModel, $this>
     */
    public function empleado(): HasOne
    {
        return $this->hasOne(EmpleadosModel::class, 'persona_id', 'persona_id');
    }

    /**
     * @return HasOne<TallaPersonasModel, $this>
     */
    public function tallaPersona(): HasOne
    {
        return $this->hasOne(TallaPersonasModel::class, 'persona_id', 'persona_id');
    }

    /**
     * @return HasMany<TelefonoPersonasModel, $this>
     */
    public function telefonos(): HasMany
    {
        return $this->hasMany(TelefonoPersonasModel::class, 'persona_id', 'persona_id');
    }

    /**
     * @return HasMany<FamiliaresModel, $this>
     */
    public function familiares(): HasMany
    {
        return $this->hasMany(FamiliaresModel::class, 'persona_id', 'persona_id');
    }

    /**
     * @return HasMany<DocumentosModel, $this>
     */
    public function documentos(): HasMany
    {
        return $this->hasMany(DocumentosModel::class, 'persona_id', 'persona_id');
    }

    /**
     * @return HasMany<DetalleBeneficioModel, $this>
     */
    public function detallesBeneficio(): HasMany
    {
        return $this->hasMany(DetalleBeneficioModel::class, 'persona_id', 'persona_id');
    }

    /**
     * @return HasMany<DetalleDiscapacidadModel, $this>
     */
    public function detallesDiscapacidad(): HasMany
    {
        return $this->hasMany(DetalleDiscapacidadModel::class, 'persona_id', 'persona_id');
    }

    /**
     * @return HasMany<DetalleHcmModel, $this>
     */
    public function detallesHcm(): HasMany
    {
        return $this->hasMany(DetalleHcmModel::class, 'persona_id', 'persona_id');
    }

    /**
     * @return HasMany<PostulacionModel, $this>
     */
    public function postulaciones(): HasMany
    {
        return $this->hasMany(PostulacionModel::class, 'persona_id', 'persona_id');
    }

    /**
     * @return HasMany<TramiteModel, $this>
     */
    public function tramites(): HasMany
    {
        return $this->hasMany(TramiteModel::class, 'persona_id', 'persona_id');
    }
}
