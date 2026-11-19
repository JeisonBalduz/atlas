<?php

declare(strict_types=1);

namespace App\Model\Seguridad;

use App\Model\Personas\EmpleadosModel;
use App\Model\Tramites\AccesoPostulacionModel;
use App\Model\Tramites\SeguimientoTramiteModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Eloquent de la tabla `usuarios`.
 *
 * @property int $usuario_id
 * @property int $rol_id
 * @property int $empleado_id
 * @property string $nombre_usuario
 * @property string $contrasena
 * @property int|null $pin
 * @property int $estatus
 * @property int $intentos
 */
class UsuariosModel extends Model
{
    /** @var string */
    protected $table = 'usuarios';

    /** @var string */
    protected $primaryKey = 'usuario_id';

    /** @var string */
    protected $keyType = 'int';

    /** @var bool */
    public $incrementing = true;

    /** @var bool */
    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'rol_id',
        'empleado_id',
        'nombre_usuario',
        'contrasena',
        'pin',
        'estatus',
        'intentos',
    ];

    /** @var list<string> */
    protected $hidden = [
        'contrasena',
        'pin',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'usuario_id' => 'integer',
        'rol_id' => 'integer',
        'empleado_id' => 'integer',
        'nombre_usuario' => 'string',
        'contrasena' => 'string',
        'pin' => 'integer',
        'estatus' => 'integer',
        'intentos' => 'integer',
    ];

    /**
     * @return BelongsTo<RolModel, $this>
     */
    public function rol(): BelongsTo
    {
        return $this->belongsTo(RolModel::class, 'rol_id', 'rol_id');
    }

    /**
     * @return BelongsTo<EmpleadosModel, $this>
     */
    public function empleado(): BelongsTo
    {
        return $this->belongsTo(EmpleadosModel::class, 'empleado_id', 'empleado_id');
    }

    /**
     * @return BelongsToMany<PermisosModel, $this>
     */
    public function permisos(): BelongsToMany
    {
        return $this->belongsToMany(PermisosModel::class, 'usuario_permisos', 'usuario_id', 'permiso_id', 'usuario_id', 'permisos_id');
    }

    /**
     * @return HasMany<AuditoriaModel, $this>
     */
    public function auditorias(): HasMany
    {
        return $this->hasMany(AuditoriaModel::class, 'usuario_id', 'usuario_id');
    }

    /**
     * @return HasMany<AccesoPostulacionModel, $this>
     */
    public function accesosPostulacion(): HasMany
    {
        return $this->hasMany(AccesoPostulacionModel::class, 'usuario_id', 'usuario_id');
    }

    /**
     * @return HasMany<SeguimientoTramiteModel, $this>
     */
    public function tramitesEjecutados(): HasMany
    {
        return $this->hasMany(SeguimientoTramiteModel::class, 'usuario_ejecutador_id', 'usuario_id');
    }

    /**
     * @return HasMany<SeguimientoTramiteModel, $this>
     */
    public function tramitesAprobados(): HasMany
    {
        return $this->hasMany(SeguimientoTramiteModel::class, 'usuario_aprobador_id', 'usuario_id');
    }
}
