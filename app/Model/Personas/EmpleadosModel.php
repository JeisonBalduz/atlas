<?php

declare(strict_types=1);

namespace App\Model\Personas;

use App\Model\Asistencia\AusenciaJustificadaModel;
use App\Model\Asistencia\DetalleAsistenciaModel;
use App\Model\Catalogos\CargoModel;
use App\Model\Catalogos\CarreraModel;
use App\Model\Catalogos\DepartamentosModel;
use App\Model\Catalogos\DependenciasModel;
use App\Model\Catalogos\EstatusModel;
use App\Model\Catalogos\NivelAcademicosModel;
use App\Model\Movimientos\DetalleMovimientoModel;
use App\Model\Movimientos\SancionesDisciplinariasModel;
use App\Model\Seguridad\UsuariosModel;
use App\Model\Vacaciones\PeriodoVacacionesModel;
use App\Model\Vacaciones\SolicitudVacacionesModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Modelo Eloquent de la tabla `empleados`.
 *
 * @property int $empleado_id
 * @property int $persona_id
 * @property int $dependencia_id
 * @property int $departamento_id
 * @property int $estatus_id
 * @property int $cargo_id
 * @property int $nivel_academico_id
 * @property int|null $carrera_id
 * @property string|null $codigo_empleado
 * @property int $estatus_actual
 * @property string|null $correo
 */
class EmpleadosModel extends Model
{
    /** @var string */
    protected $table = 'empleados';

    /** @var string */
    protected $primaryKey = 'empleado_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'persona_id',
        'dependencia_id',
        'departamento_id',
        'estatus_id',
        'cargo_id',
        'nivel_academico_id',
        'carrera_id',
        'codigo_empleado',
        'estatus_actual',
        'correo',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'empleado_id' => 'integer',
        'persona_id' => 'integer',
        'dependencia_id' => 'integer',
        'departamento_id' => 'integer',
        'estatus_id' => 'integer',
        'cargo_id' => 'integer',
        'nivel_academico_id' => 'integer',
        'carrera_id' => 'integer',
        'codigo_empleado' => 'string',
        'estatus_actual' => 'integer',
        'correo' => 'string',
    ];

    /**
     * @return BelongsTo<PersonasModel, $this>
     */
    public function persona(): BelongsTo
    {
        return $this->belongsTo(PersonasModel::class, 'persona_id', 'persona_id');
    }

    /**
     * @return BelongsTo<DependenciasModel, $this>
     */
    public function dependencia(): BelongsTo
    {
        return $this->belongsTo(DependenciasModel::class, 'dependencia_id', 'dependencia_id');
    }

    /**
     * @return BelongsTo<DepartamentosModel, $this>
     */
    public function departamento(): BelongsTo
    {
        return $this->belongsTo(DepartamentosModel::class, 'departamento_id', 'departamento_id');
    }

    /**
     * @return BelongsTo<EstatusModel, $this>
     */
    public function estatus(): BelongsTo
    {
        return $this->belongsTo(EstatusModel::class, 'estatus_id', 'estatus_id');
    }

    /**
     * @return BelongsTo<CargoModel, $this>
     */
    public function cargo(): BelongsTo
    {
        return $this->belongsTo(CargoModel::class, 'cargo_id', 'cargo_id');
    }

    /**
     * @return BelongsTo<NivelAcademicosModel, $this>
     */
    public function nivelAcademico(): BelongsTo
    {
        return $this->belongsTo(NivelAcademicosModel::class, 'nivel_academico_id', 'nivel_academico_id');
    }

    /**
     * @return BelongsTo<CarreraModel, $this>
     */
    public function carrera(): BelongsTo
    {
        return $this->belongsTo(CarreraModel::class, 'carrera_id', 'carrera_id');
    }

    /**
     * @return HasOne<UsuariosModel, $this>
     */
    public function usuario(): HasOne
    {
        return $this->hasOne(UsuariosModel::class, 'empleado_id', 'empleado_id');
    }

    /**
     * @return HasOne<DireccionModel, $this>
     */
    public function direccion(): HasOne
    {
        return $this->hasOne(DireccionModel::class, 'empleado_id', 'empleado_id');
    }

    /**
     * @return HasMany<FamiliaresModel, $this>
     */
    public function familiares(): HasMany
    {
        return $this->hasMany(FamiliaresModel::class, 'empleado_id', 'empleado_id');
    }

    /**
     * @return HasMany<DetalleMovimientoModel, $this>
     */
    public function detallesMovimiento(): HasMany
    {
        return $this->hasMany(DetalleMovimientoModel::class, 'empleado_id', 'empleado_id');
    }

    /**
     * @return HasMany<DetalleAsistenciaModel, $this>
     */
    public function detallesAsistencia(): HasMany
    {
        return $this->hasMany(DetalleAsistenciaModel::class, 'empleado_id', 'empleado_id');
    }

    /**
     * @return HasMany<AusenciaJustificadaModel, $this>
     */
    public function ausenciasJustificadas(): HasMany
    {
        return $this->hasMany(AusenciaJustificadaModel::class, 'empleado_id', 'empleado_id');
    }

    /**
     * @return HasMany<PeriodoVacacionesModel, $this>
     */
    public function periodosVacaciones(): HasMany
    {
        return $this->hasMany(PeriodoVacacionesModel::class, 'empleado_id', 'empleado_id');
    }

    /**
     * @return HasMany<SolicitudVacacionesModel, $this>
     */
    public function solicitudesVacaciones(): HasMany
    {
        return $this->hasMany(SolicitudVacacionesModel::class, 'empleado_id', 'empleado_id');
    }

    /**
     * @return HasMany<SancionesDisciplinariasModel, $this>
     */
    public function sancionesDisciplinarias(): HasMany
    {
        return $this->hasMany(SancionesDisciplinariasModel::class, 'empleado_id', 'empleado_id');
    }
}
