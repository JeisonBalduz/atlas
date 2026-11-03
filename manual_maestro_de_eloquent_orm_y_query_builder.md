# Manual Maestro: Eloquent ORM y Query Builder en FlightPHP (Camino a Laravel)

Este documento es una referencia exhaustiva de ingeniería de software diseñada para dominar **Eloquent ORM (`illuminate/database`)** y su **Query Builder** integrado con **PostgreSQL** dentro de la arquitectura de **FlightPHP**, preparándote al 100% para el ecosistema profesional de **Laravel**.

---

## 1. Análisis Crítico: ¿Objetos, Arrays o JSON? (Confirmando tu intuición)

**¡Estás en lo cierto al 100%!** Uno de los mayores errores de los desarrolladores principiantes es convertir siempre los resultados de Eloquent a arreglos (`.toArray()`) por costumbre de trabajar con PDO puro. **No todo el tiempo debes retornar un array; depende estrictamente de la capa de tu aplicación que consumirá los datos.**

### ¿Qué devuelve realmente Eloquent?

1. Cuando pides **varios registros** (ej. `->get()` o `::all()`), Eloquent devuelve un objeto de la clase **`Illuminate\Database\Eloquent\Collection`** (una "Colección", que es un super-array vitaminado lleno de objetos de tu Modelo).
2. Cuando pides **un solo registro** (ej. `->first()` o `::find(1)`), devuelve una **Instancia de tu Modelo** (un objeto individual de PHP) o `null` si no existe.

### Regla de Oro: ¿Cuándo usar cada formato?

| Escenario / Destino                                       | ¿Qué formato debes usar?                | ¿Por qué? (Justificación Arquitectónica)                                                                                                                                                                                                       | Ejemplo de Código                                                 |
| :-------------------------------------------------------- | :-------------------------------------- | :--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | :---------------------------------------------------------------- |
| **Vistas en BladeOne (`.blade.php`)**                     | **Objetos / Colecciones (Por defecto)** | En Blade es mucho más limpio, legible y elegante escribir `$empleado->nombre` que `$empleado['nombre']`. Además, como objeto tienes acceso a métodos personalizados, relaciones y mutadores en tiempo real dentro de la vista.                 | `Flight::render('empleados.index', ['empleados' => $empleados]);` |
| **Lógica de Negocio (Controladores / Servicios)**         | **Objetos / Colecciones (Por defecto)** | Las Colecciones tienen métodos funcionales (`->filter()`, `->map()`, `->sum()`, `->isEmpty()`) que te ahorran escribir bucles `foreach` manuales.                                                                                              | `if ($empleados->isEmpty()) { ... }`                              |
| **Respuestas a Peticiones AJAX / Fetch JS (APIs)**        | **JSON**                                | El navegador (JavaScript) no entiende objetos de PHP; necesita una cadena JSON estructurada. Tanto `Flight::json()` como el método `->toJson()` de Eloquent serializan automáticamente la colección respetando los campos ocultos (`$hidden`). | `Flight::json($empleados);` o `echo $empleados->toJson();`        |
| **Librerías externas antiguas o exportación a Excel/CSV** | **Arrays Asociativos (`->toArray()`)**  | Úsalo **únicamente** cuando una función nativa de PHP (como `fputcsv`) o una librería de terceros te exija estrictamente un `array` plano de toda la vida.                                                                                     | `$datosPlanos = $empleados->toArray();`                           |

---

## 2. Anatomía Profunda de un Modelo en Eloquent

En Eloquent, un Modelo no es solo un archivo vacío que apunta a una tabla; es el corazón de tus reglas de datos. Puedes tener dos tipos de modelos en tu proyecto **Atlas**: **Modelos de Vistas (Solo Lectura)** y **Modelos de Tablas Reales (Lectura/Escritura)**.

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;

class EmpleadoModel extends Model
{
    // =========================================================================
    // 1. CONFIGURACIÓN DE TABLA Y LLAVE PRIMARIA
    // =========================================================================

    // Nombre de la tabla o vista en PostgreSQL (ej. 'empleados' o 'vw_empleados')
    protected $table = 'empleados';

    // Llave primaria (por defecto Eloquent asume 'id')
    protected $primaryKey = 'id';

    // ¿La llave primaria es autoincremental (SERIAL / IDENTITY)?
    // (Ponlo en false si es una VISTA o si usas la cédula como llave primaria)
    public $incrementing = true;

    // Tipo de dato de la llave primaria ('int' o 'string')
    protected $keyType = 'int';

    // ¿La tabla tiene las columnas 'created_at' y 'updated_at'?
    // (En vistas de PostgreSQL siempre debe ir en false)
    public $timestamps = false;

    // =========================================================================
    // 2. SEGURIDAD DE ASIGNACIÓN MASIVA (Mass Assignment)
    // =========================================================================

    // Lista blanca de columnas que permites guardar de golpe con EmpleadoModel::create($_POST)
    protected $fillable = [
        'cedula',
        'primer_nombre',
        'primer_apellido',
        'fecha_nacimiento',
        'sueldo_base',
        'estatus',
        'departamento_id',
        'datos_extra_json'
    ];

    // Opcional: Lista negra (columnas protegidas que no se pueden llenar masivamente)
    // protected $guarded = ['id', 'es_super_admin'];

    // =========================================================================
    // 3. SERIALIZACIÓN (Ocultar o añadir datos al convertir a Array / JSON)
    // =========================================================================

    // Columnas que NUNCA se enviarán cuando conviertas el modelo a JSON o Array
    protected $hidden = [
        'clave_seguridad',
        'token_recuperacion'
    ];

    // Atributos virtuales (Accessors) que quieres que se incluyan siempre en el JSON
    protected $appends = [
        'nombre_completo'
    ];

    // =========================================================================
    // 4. CASTING AUTOMÁTICO DE TIPOS DE DATOS (Ideal para PostgreSQL)
    // =========================================================================

    // Convierte automáticamente los tipos de la BD a tipos nativos de PHP al leer/escribir
    protected $casts = [
        'sueldo_base'      => 'float',
        'activo'           => 'boolean',
        'fecha_nacimiento' => 'date:Y-m-d',
        'datos_extra_json' => 'array', // ¡Convierte columnas JSONB de Postgres en arrays de PHP automáticamente!
    ];

    // =========================================================================
    // 5. ACCESSORS Y MUTATORS (Transformación de datos al vuelo)
    // =========================================================================

    /**
     * ACCESSOR: Crea una columna virtual 'nombre_completo' que no existe en la BD.
     * Uso: echo $empleado->nombre_completo;
     */
    protected function nombreCompleto(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) =>
                trim(($attributes['primer_nombre'] ?? '') . ' ' . ($attributes['primer_apellido'] ?? ''))
        );
    }

    /**
     * MUTATOR: Limpia y pone en mayúsculas la cédula automáticamente antes de guardarla en BD.
     * Uso: $empleado->cedula = 'v-12345678'; -> Guarda 'V-12345678'
     */
    protected function cedula(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => strtoupper(trim($value))
        );
    }

    // =========================================================================
    // 6. LOCAL SCOPES (Filtros reutilizables y elegantes)
    // =========================================================================

    /**
     * Permite llamar a EmpleadoModel::activos()->get() en cualquier controlador.
     */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('estatus', 'activo');
    }

    /**
     * Scope dinámico con parámetros: EmpleadoModel::porDepartamento(3)->get()
     */
    public function scopePorDepartamento(Builder $query, int $deptoId): Builder
    {
        return $query->where('departamento_id', $deptoId);
    }

    // =========================================================================
    // 7. RELACIONES ENTRE TABLAS
    // =========================================================================

    /**
     * Un empleado TIENE MUCHOS familiares (Relación 1 a N)
     */
    public function familiares()
    {
        // (Modelo Relacionado, 'llave_foranea_en_familiares', 'llave_local_en_empleados')
        return $this->hasMany(FamiliarModel::class, 'empleado_id', 'id');
    }

    /**
     * Un empleado PERTENECE A un departamento (Relación N a 1)
     */
    public function departamento()
    {
        return $this->belongsTo(DepartamentoModel::class, 'departamento_id', 'id');
    }
}
```

---

## 3. El Catálogo Definitivo de Consultas (Todas las Posibilidades)

Aquí tienes un archivo de referencia con **todas las formas posibles** de consultar, seleccionar columnas específicas, filtrar, agrupar y transformar datos usando Eloquent y el Query Builder (`Capsule`).

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\Models\ViewsFamiliarModel;
use App\Models\EmpleadoModel;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Builder;

class CatalogoConsultasEloquent
{
    // =========================================================================
    // SECCIÓN A: OBTENCIÓN BÁSICA DE DATOS (GETTERS)
    // =========================================================================
    public function obtencionBasica(): void
    {
        // 1. Traer absolutamente TODAS las filas y TODAS las columnas (SELECT * FROM ...)
        // Retorna: Collection de objetos ViewsFamiliarModel
        $todos = ViewsFamiliarModel::all();

        // 2. Equivalente con el constructor de consultas (útil cuando encadenas filtros antes)
        $todosConGet = ViewsFamiliarModel::get();

        // 3. Obtener un único registro por su Llave Primaria (ID)
        // Retorna: Objeto ViewsFamiliarModel o null
        $unoPorId = ViewsFamiliarModel::find(15);

        // 4. Obtener varios registros pasando un arreglo de IDs
        // SQL: SELECT * FROM ... WHERE id IN (1, 5, 20)
        $variosPorId = ViewsFamiliarModel::find([1, 5, 20]);

        // 5. Obtener el PRIMER registro que cumpla la consulta
        // SQL: SELECT * FROM ... LIMIT 1
        // Retorna: Objeto ViewsFamiliarModel o null
        $primero = ViewsFamiliarModel::where('nacionalidad', 'V')->first();

        // 6. Atajo moderno (firstWhere): hace el where y el first en un solo paso
        $primeroAtajo = ViewsFamiliarModel::firstWhere('cedula_familiar', 'V-19080300');

        // 7. Buscar o lanzar excepción automática (Ideal para mostrar error 404 si el ID no existe)
        $seguro = ViewsFamiliarModel::findOrFail(999);
        $primeroSeguro = ViewsFamiliarModel::where('cedula', '123')->firstOrFail();
    }

    // =========================================================================
    // SECCIÓN B: SELECCIÓN DE COLUMNAS ESPECÍFICAS (OPTIMIZACIÓN DE MEMORIA)
    // "¡No quiero traerme todo, solo ciertas columnas!"
    // =========================================================================
    public function columnasEspecificas(): void
    {
        // 1. Método select(): Trae todas las filas, pero SOLO las columnas indicadas
        // SQL: SELECT id, nombre_familiar, parentesco FROM vw_familiares
        $optimizado = ViewsFamiliarModel::select('id', 'nombre_familiar', 'parentesco')->get();

        // 2. Pasar las columnas directamente dentro de get() o all() (Atajo súper limpio)
        $optimizadoAtajo = ViewsFamiliarModel::get(['id', 'nombre_familiar', 'parentesco']);
        $todosOptimizado = ViewsFamiliarModel::all(['id', 'nombre_familiar']);

        // 3. Renombrar columnas al vuelo con alias (AS)
        // Acceso en PHP: $item->nombre_completo y $item->tipo_relacion
        $conAlias = ViewsFamiliarModel::select(
            'id',
            'nombre_familiar as nombre_completo',
            'parentesco as tipo_relacion'
        )->get();

        // 4. Traer valores ÚNICOS sin repetir (DISTINCT)
        // SQL: SELECT DISTINCT parentesco FROM vw_familiares
        $parentescosUnicos = ViewsFamiliarModel::select('parentesco')->distinct()->get();

        // 5. Agregar una columna extra a una consulta que ya tenía un select previo (addSelect)
        $query = ViewsFamiliarModel::select('id', 'nombre_familiar');
        if ($necesitoCedula = true) {
            $query->addSelect('cedula_familiar');
        }
        $resultadoDinamico = $query->get();

        // 6. Obtener UN SOLO VALOR directo de una celda (sin traer el objeto entero)
        // Retorna directamente el string o número (ej: "Carlos Pérez")
        $soloElNombre = ViewsFamiliarModel::where('id', 10)->value('nombre_familiar');

        // 7. Obtener una lista plana de una sola columna (pluck)
        // Retorna una colección simple: ['Carlos', 'Ana', 'Pedro']
        $listaNombres = ViewsFamiliarModel::pluck('nombre_familiar');

        // 8. Pluck Asociativo (Clave => Valor): ¡EL REY PARA LLENAR <select> EN HTML!
        // Retorna: [ 1 => 'Carlos', 2 => 'Ana', 3 => 'Pedro' ] (id como clave, nombre como valor)
        $opcionesParaSelect = ViewsFamiliarModel::pluck('nombre_familiar', 'id');
    }

    // =========================================================================
    // SECCIÓN C: FILTRADO DE DATOS (TODAS LAS VARIANTES DE WHERE)
    // =========================================================================
    public function todosLosFiltrosWhere(): void
    {
        // 1. Igualdad simple (Operador '=' implícito)
        $q1 = ViewsFamiliarModel::where('nacionalidad', 'V')->get();

        // 2. Operadores de comparación (<, >, <=, >=, <>, !=)
        $mayoresDeEdad = ViewsFamiliarModel::where('edad', '>=', 18)->get();
        $noHijos = ViewsFamiliarModel::where('parentesco', '!=', 'HIJO')->get();

        // 3. Múltiples condiciones AND (encadenando where o pasando un array)
        $hijosVenezolanos = ViewsFamiliarModel::where('nacionalidad', 'V')
            ->where('parentesco', 'HIJO')
            ->where('edad', '<', 25)
            ->get();

        // Equivalente pasando un arreglo de condiciones:
        $hijosArray = ViewsFamiliarModel::where([
            ['nacionalidad', '=', 'V'],
            ['parentesco', '=', 'HIJO'],
            ['edad', '<', 25]
        ])->get();

        // 4. Condiciones OR (orWhere)
        $padresOMadres = ViewsFamiliarModel::where('parentesco', 'PADRE')
            ->orWhere('parentesco', 'MADRE')
            ->get();

        // 5. AGRUPACIÓN LÓGICA CON PARÉNTESIS (¡CRÍTICO PARA NO ROMPER CONSULTAS CON OR!)
        // SQL: WHERE estatus = 'activo' AND (parentesco = 'PADRE' OR parentesco = 'MADRE')
        $agrupadoCorrecto = ViewsFamiliarModel::where('estatus', 'activo')
            ->where(function (Builder $query) {
                $query->where('parentesco', 'PADRE')
                      ->orWhere('parentesco', 'MADRE');
            })
            ->get();

        // 6. Búsqueda de texto en PostgreSQL: LIKE vs ILIKE (Insensible a mayúsculas/acento)
        // En PostgreSQL, 'ILIKE' busca sin importar si está en mayúsculas o minúsculas:
        $busquedaPostgres = ViewsFamiliarModel::where('nombre_familiar', 'ILIKE', '%rodriguez%')->get();

        // 7. Buscar el mismo término en MÚLTIPLES columnas a la vez (whereAny - Laravel moderno)
        // SQL: WHERE (nombre ILIKE '%juan%' OR apellido ILIKE '%juan%' OR cedula ILIKE '%juan%')
        $buscadorGlobal = ViewsFamiliarModel::whereAny(
            ['nombre_familiar', 'apellido_familiar', 'cedula_familiar'],
            'ILIKE',
            '%juan%'
        )->get();

        // 8. Filtrar por lista de valores: WHERE IN y WHERE NOT IN
        $grupoEspecifico = ViewsFamiliarModel::whereIn('parentesco', ['HIJO', 'CONYUGE', 'PADRE'])->get();
        $excluidos = ViewsFamiliarModel::whereNotIn('estatus', ['inactivo', 'fallecido'])->get();

        // 9. Filtrar por rangos (Números o Fechas): BETWEEN y NOT BETWEEN
        $adolescentes = ViewsFamiliarModel::whereBetween('edad', [12, 17])->get();
        $nacidosEn2020 = ViewsFamiliarModel::whereBetween('fecha_nacimiento', ['2020-01-01', '2020-12-31'])->get();
        $fueraDeRango = ViewsFamiliarModel::whereNotBetween('edad', [18, 65])->get();

        // 10. Valores Nulos: IS NULL y IS NOT NULL
        $sinCedula = ViewsFamiliarModel::whereNull('cedula_familiar')->get();
        $conCedula = ViewsFamiliarModel::whereNotNull('cedula_familiar')->get();

        // 11. Comparar dos columnas de la misma tabla entre sí (whereColumn)
        // Ejemplo: Donde la fecha de actualización sea mayor a la fecha de creación
        $modificados = EmpleadoModel::whereColumn('updated_at', '>', 'created_at')->get();

        // 12. Filtros específicos para FECHAS (extraen año, mes o día automáticamente en SQL)
        $nacidosEnMayo = ViewsFamiliarModel::whereMonth('fecha_nacimiento', '05')->get();
        $nacidosEn1995 = ViewsFamiliarModel::whereYear('fecha_nacimiento', '1995')->get();
        $nacidosElDia15 = ViewsFamiliarModel::whereDay('fecha_nacimiento', '15')->get();
        $fechaExacta = ViewsFamiliarModel::whereDate('created_at', '2026-03-15')->get();
    }

    // =========================================================================
    // SECCIÓN D: CONSULTAS DINÁMICAS INTELIGENTES (EL MÉTODO ->when())
    // "¡Adiós a los bloques if() desordenados en el controlador!"
    // =========================================================================
    public function filtrosDinamicosLimpos(?string $busqueda, ?string $parentesco): void
    {
        // El método when() SOLO ejecuta el filtro si el primer valor es verdadero (no vacío/null)
        $resultados = ViewsFamiliarModel::query()
            ->select('id', 'cedula_familiar', 'nombre_familiar', 'parentesco')
            ->when($busqueda, function (Builder $query, string $texto) {
                $query->where('nombre_familiar', 'ILIKE', "%{$texto}%");
            })
            ->when($parentesco, function (Builder $query, string $tipo) {
                $query->where('parentesco', $tipo);
            })
            ->orderBy('nombre_familiar', 'ASC')
            ->get();
    }

    // =========================================================================
    // SECCIÓN E: ORDENAMIENTO, LÍMITES Y PAGINACIÓN
    // =========================================================================
    public function ordenamientoYLimites(): void
    {
        // 1. Ordenar ascendente (ASC) o descendente (DESC)
        $ordenados = ViewsFamiliarModel::orderBy('apellido_familiar', 'ASC')
            ->orderBy('nombre_familiar', 'ASC')
            ->get();

        // 2. Atajos por fecha (latest = ORDER BY created_at DESC, oldest = ASC)
        $ultimosRegistrados = ViewsFamiliarModel::latest('fecha_registro')->get();

        // 3. Orden aleatorio
        $alAzar = ViewsFamiliarModel::inRandomOrder()->first();

        // 4. Limitar y saltar registros (LIMIT y OFFSET para paginación manual)
        // Trae 10 registros, saltándose los primeros 20 (Página 3)
        $paginadosManual = ViewsFamiliarModel::skip(20)->take(10)->get();
        // Equivalente exacto:
        $paginadosOffset = ViewsFamiliarModel::offset(20)->limit(10)->get();
    }

    // =========================================================================
    // SECCIÓN F: AGREGACIONES, ESTADÍSTICAS Y AGRUPAMIENTO (GROUP BY)
    // =========================================================================
    public function agregacionesYEstadisticas(): void
    {
        // 1. Verificar si existe al menos un registro que cumpla la condición (Más rápido que count() > 0)
        // Retorna: true o false
        $tieneHijos = ViewsFamiliarModel::where('empleado_id', 10)->where('parentesco', 'HIJO')->exists();
        $noTieneHijos = ViewsFamiliarModel::where('empleado_id', 10)->doesntExist();

        // 2. Funciones matemáticas directas
        $totalRegistros = ViewsFamiliarModel::count();
        $edadMaxima     = ViewsFamiliarModel::max('edad');
        $edadMinima     = ViewsFamiliarModel::min('edad');
        $edadPromedio   = ViewsFamiliarModel::avg('edad');
        $gastoTotal     = EmpleadoModel::sum('sueldo_base');

        // 3. Agrupamiento estadístico con GROUP BY y HAVING (Consultas personalizadas)
        // Ejemplo: Cantidad de familiares agrupados por tipo de parentesco
        $estadisticaParentesco = ViewsFamiliarModel::select('parentesco')
            ->selectRaw('COUNT(*) as total_familiares')
            ->groupBy('parentesco')
            ->havingRaw('COUNT(*) > ?', [5]) // Solo mostrar parentescos que tengan más de 5 registros
            ->get();
    }

    // =========================================================================
    // SECCIÓN G: DATOS PERSONALIZADOS, EXPRESIONES RAW Y FUNCIONES DE POSTGRES
    // =========================================================================
    public function consultasPersonalizadasYPostgres(): void
    {
        // 1. Concatenar o calcular columnas personalizadas en SQL (selectRaw)
        // Siempre pasa las variables en el segundo array (?) para evitar Inyección SQL
        $calculados = EmpleadoModel::select('id', 'primer_nombre', 'sueldo_base')
            ->selectRaw('(sueldo_base * ?) as sueldo_con_bono', [1.15])
            ->selectRaw("CONCAT(primer_nombre, ' ', primer_apellido) as nombre_completo_sql")
            ->get();

        // 2. Filtros avanzados con funciones nativas de PostgreSQL (whereRaw)
        // Ejemplo: Extraer el año usando sintaxis propia de Postgres
        $filtroRaw = ViewsFamiliarModel::whereRaw('EXTRACT(YEAR FROM fecha_nacimiento) = ?', [1990])->get();

        // 3. Ejecutar FUNCIONES ALMACENADAS (plpgsql) de PostgreSQL usando Capsule
        $empleadoId = 25;
        $anio = 2026;
        // Retorna un array de objetos stdClass con lo que devuelva tu función de Postgres
        $resultadoFuncion = Capsule::select(
            'SELECT * FROM fn_calcular_vacaciones_empleado(?, ?)',
            [$empleadoId, $anio]
        );

        // 4. Consultar columnas JSON / JSONB nativas de PostgreSQL con sintaxis de flecha (->)
        // Si tienes una columna jsonb llamada 'metadatos' con {"tipo_sangre": "O+", "alergias": ["polvo"]}
        $tipoSangreO = EmpleadoModel::where('metadatos->tipo_sangre', 'O+')->get();
        $alergicosAlPolvo = EmpleadoModel::whereJsonContains('metadatos->alergias', 'polvo')->get();
    }

    // =========================================================================
    // SECCIÓN H: JOINS (CRUCE DE TABLAS) Y RELACIONES (EAGER LOADING)
    // =========================================================================
    public function crucesDeTablasYRelaciones(): void
    {
        // OPCIÓN 1: Usando Relaciones de Eloquent (with) - ¡Evita el problema de rendimiento N+1!
        // Trae todos los empleados y, en una sola consulta adicional, les adjunta sus familiares y departamento
        $empleadosConTodo = EmpleadoModel::with(['familiares', 'departamento'])->get();

        // Optimización extrema: Traer la relación pero SOLO con ciertas columnas de cada tabla
        // (Nota: En with() siempre debes incluir el 'id' y la llave foránea para que Eloquent las pueda unir)
        $empleadosOptimizados = EmpleadoModel::select('id', 'primer_nombre', 'departamento_id')
            ->with([
                'departamento:id,nombre_depto',
                'familiares:id,empleado_id,nombre_familiar,parentesco'
            ])
            ->get();

        // Filtrar empleados según si TIENEN o NO TIENEN registros en otra tabla (has / whereHas)
        $empleadosConFamiliares = EmpleadoModel::has('familiares')->get();
        $empleadosConHijosMenores = EmpleadoModel::whereHas('familiares', function (Builder $q) {
            $q->where('parentesco', 'HIJO')->where('edad', '<', 18);
        })->get();

        // OPCIÓN 2: Usando JOIN clásico (Query Builder) cuando no tienes relaciones o quieres un resultado plano
        $reportePlano = EmpleadoModel::join('departamentos', 'empleados.departamento_id', '=', 'departamentos.id')
            ->leftJoin('familiares', 'empleados.id', '=', 'familiares.empleado_id')
            ->select(
                'empleados.id',
                'empleados.primer_nombre',
                'departamentos.nombre as nombre_departamento',
                'familiares.nombre_familiar'
            )
            ->where('departamentos.activo', true)
            ->get();
    }

    // =========================================================================
    // SECCIÓN I: ESCRITURA DE DATOS (INSERT, UPDATE, DELETE, TRANSACCIONES)
    // Nota: Esto se aplica sobre Modelos de TABLAS REALES (no sobre vistas vw_)
    // =========================================================================
    public function escrituraYTransacciones(): void
    {
        // 1. CREAR un registro (Forma Orientada a Objetos)
        $nuevo = new EmpleadoModel();
        $nuevo->cedula = 'V-25000111';
        $nuevo->primer_nombre = 'Mariana';
        $nuevo->sueldo_base = 500.00;
        $nuevo->save(); // Ejecuta el INSERT y asigna $nuevo->id automáticamente

        // 2. CREAR un registro (Asignación Masiva - Requiere configurar $fillable en el Modelo)
        $creado = EmpleadoModel::create([
            'cedula'        => 'V-26000222',
            'primer_nombre' => 'Alejandro',
            'sueldo_base'   => 600.00
        ]);

        // 3. BUSCAR O CREAR (Ideal para evitar duplicados de cédula)
        // Busca por cédula; si no existe en PostgreSQL, lo inserta con los datos del segundo array
        $empleado = EmpleadoModel::firstOrCreate(
            ['cedula' => 'V-27000333'],
            ['primer_nombre' => 'Roberto', 'sueldo_base' => 450.00]
        );

        // 4. ACTUALIZAR un registro individual
        $emp = EmpleadoModel::find(10);
        if ($emp) {
            $emp->sueldo_base = 750.00;
            $emp->save(); // Solo actualiza las columnas que hayan cambiado (isDirty)
        }

        // 5. ACTUALIZACIÓN MASIVA (Por condición)
        // Aumentar el sueldo a todo un departamento
        $filasAfectadas = EmpleadoModel::where('departamento_id', 4)
            ->update(['estatus' => 'revisado']);

        // Incrementar o disminuir valores numéricos directamente en SQL
        EmpleadoModel::where('id', 10)->increment('sueldo_base', 50); // Suma 50 al sueldo actual

        // 6. ELIMINAR registros
        EmpleadoModel::destroy(15); // Borra por ID directo
        EmpleadoModel::where('estatus', 'temporal')->delete(); // Borrado por filtro

        // 7. TRANSACCIONES SEGURAS EN POSTGRESQL (Todo o Nada)
        // Si ocurre cualquier excepción dentro de la función, PostgreSQL hace ROLLBACK automático
        Capsule::transaction(function () {
            $emp = EmpleadoModel::create([
                'cedula' => 'V-30111222',
                'primer_nombre' => 'Lucía'
            ]);

            // Si esta segunda inserción falla, el empleado de arriba tampoco se guarda
            Capsule::table('auditoria_rrhh')->insert([
                'accion' => 'CREACION_EMPLEADO',
                'empleado_id' => $emp->id,
                'fecha' => date('Y-m-d H:i:s')
            ]);
        });
    }
}
```

---

## 4. Cómo Transformar y Leer los Objetos (Serialización y Colecciones)

Cuando ejecutas `$familiares = ViewsFamiliarModel::get();`, tienes una **Colección de Objetos**. Aquí tienes todas las formas de manipularla y convertirla según lo que necesites hacer:

```php
<?php

// 1. RECORRER COMO OBJETOS (La forma recomendada en PHP y en vistas BladeOne)
$familiares = ViewsFamiliarModel::select('id', 'nombre_familiar', 'edad')->get();

foreach ($familiares as $fam) {
    // Accedes con la flecha (->) de forma limpia
    echo $fam->nombre_familiar . " tiene " . $fam->edad . " años.";
}

// 2. MANIPULAR LA COLECCIÓN EN MEMORIA (Sin volver a consultar la base de datos)
$totalCargados   = $familiares->count(); // Cuenta cuántos elementos hay en la variable
$estaVacia       = $familiares->isEmpty(); // Devuelve true si no trajo nada
$primerElemento  = $familiares->first(); // Saca el primer objeto de la colección
$menoresEnMemoria = $familiares->where('edad', '<', 18); // Filtra la colección ya cargada

// Transformar los datos a medida (map) para crear una estructura personalizada:
$resumenPersonalizado = $familiares->map(function ($fam) {
    return [
        'identificador' => $fam->id,
        'descripcion'   => strtoupper($fam->nombre_familiar) . " ({$fam->edad} años)",
        'es_mayor'      => $fam->edad >= 18
    ];
});

// 3. CONVERTIR A ARRAY ASOCIATIVO PURO (Cuando una librería te pida un array nativo)
$arrayCompleto = $familiares->toArray();
// Ahora sí se lee con corchetes: $arrayCompleto[0]['nombre_familiar']

$unSoloObjeto = ViewsFamiliarModel::first();
$arrayDeUno   = $unSoloObjeto ? $unSoloObjeto->toArray() : [];

// 4. OCULTAR O MOSTRAR COLUMNAS AL VUELO ANTES DE CONVERTIR A JSON / ARRAY
// Si en un endpoint específico no quieres enviar la cédula ni el ID:
$datosLimpios = $familiares->makeHidden(['id', 'cedula_familiar'])->toArray();

// 5. CONVERTIR Y RESPONDER EN JSON (Para llamadas AJAX / Fetch desde tus modales)
// En FlightPHP puedes pasarle directamente la colección a Flight::json():
Flight::json([
    'exito' => true,
    'data'  => $familiares // Se convierte automáticamente a JSON limpio
]);
```

---

## 5. Buenas Prácticas de Arquitectura: Modelos vs. Controladores

Para que tu código en **Atlas** tenga nivel de ingeniería profesional y tu salto futuro a **Laravel** sea impecable, aplica el principio de **"Modelos Inteligentes, Controladores Delgados" (_Skinny Controllers, Fat Models_)**.

### ❌ Lo que NO debes hacer (Malas Prácticas)

1. **No escribas consultas encadenadas gigantescas repetidas en múltiples controladores:** Si en 4 controladores distintos escribes `where('estatus', 'activo')->where('condicion', 'fijo')`, el día que cambie esa regla tendrás que buscar y editar 4 archivos.
2. **No uses `SELECT *` (`::all()`) en tablas gigantes si solo vas a mostrar 3 columnas en una tabla HTML:** Desperdicias memoria RAM en el servidor.
3. **No conviertas a `.toArray()` antes de enviar los datos a una vista de BladeOne:** Pierdes las ventajas de los objetos, los _Accessors_ y los métodos de la colección.

### ✅ Cómo debe verse la interacción perfecta (El Estándar Profesional)

#### 1. El Modelo encapsula los filtros del negocio (`app/Models/ViewsFamiliarModel.php`)

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ViewsFamiliarModel extends Model
{
    protected $table = 'vw_familiares';
    protected $primaryKey = 'id';
    public $incrementing = false;
    public $timestamps = false;

    /**
     * Scope reutilizable para buscar familiares filtrados y optimizados
     */
    public function scopeBuscarFiltrado(Builder $query, ?string $termino, ?string $parentesco): Builder
    {
        return $query
            ->select('id', 'empleado_id', 'cedula_familiar', 'nombre_familiar', 'parentesco', 'edad')
            ->when($termino, function (Builder $q, string $texto) {
                $q->whereAny(['nombre_familiar', 'cedula_familiar'], 'ILIKE', "%{$texto}%");
            })
            ->when($parentesco, function (Builder $q, string $tipo) {
                $q->where('parentesco', $tipo);
            })
            ->orderBy('nombre_familiar', 'ASC');
    }
}
```

#### 2. El Controlador solo coordina la petición y entrega la respuesta (`app/Controller/FamiliaresController.php`)

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\Models\ViewsFamiliarModel;
use Flight;

class FamiliaresController
{
    /**
     * Renderiza la vista HTML principal con BladeOne
     */
    public function index(): void
    {
        // 1. Capturamos los filtros de la URL (?buscar=...&parentesco=...) usando el Request de Flight
        $busqueda   = Flight::request()->query['buscar'] ?? null;
        $parentesco = Flight::request()->query['parentesco'] ?? null;

        // 2. El controlador lee como una oración en español gracias al Modelo
        // Mantenemos el resultado como Colección de Objetos para que BladeOne lo aproveche al máximo
        $familiares = ViewsFamiliarModel::buscarFiltrado($busqueda, $parentesco)->get();

        // 3. Renderizamos la vista pasando los objetos directamente
        Flight::render('familiares.index', [
            'familiares' => $familiares,
            'total'      => $familiares->count()
        ]);
    }

    /**
     * Endpoint API para cargar los datos de un familiar en un Modal vía Fetch JS
     */
    public function obtenerDetalleJson(int $id): void
    {
        $familiar = ViewsFamiliarModel::select('id', 'cedula_familiar', 'nombre_familiar', 'parentesco', 'fecha_nacimiento')
            ->find($id);

        if (!$familiar) {
            Flight::jsonHalt(['exito' => false, 'mensaje' => 'Familiar no encontrado'], 404);
        }

        // Aquí sí respondemos en JSON porque lo consume JavaScript para llenar el modal
        Flight::json([
            'exito' => true,
            'datos' => $familiar
        ]);
    }
}
```
