@extends('layouts.app') {{-- CARGAR PAGINA PRINCIPAL --}}

@section('titulo', 'Editar Empleado') {{-- TÍTULO DE LA PÁGINA --}}

@section('main_class', 'ml-20 transition-[margin] duration-300 flex-1 h-screen overflow-y-auto px-6 md:px-10 xl:px-12
    2xl:px-16 pb-12 pt-6 bg-slate-50') {{-- CSS DE LA ETIQUETA MAIN --}}

@section('menu_lateral'){{-- CARGAR HTML EN ESTE APARTADO --}}
    @include('layouts.menu_lateral'){{-- CARGAR menu_lateral  --}}
@endsection

@section('header'){{-- CARGAR HTML EN ESTE APARTADO --}}
    @include('layouts.header'){{-- CARGAR HEADER  --}}
@endsection

@section('contenido') {{-- CARGAR CONTENIDO PRINCIPAL --}}
    <form id="formulario-editar-empleado" data-empleado-id="{{ $empleado->empleado_id ?? '' }}" enctype="multipart/form-data"
        class="w-full max-w-none mx-auto grid grid-cols-1 md:grid-cols-12 gap-6 atlas-vista-entrada" novalidate>

        {{-- Card 1 (Bento Hero Oscura): Cabecera con retorno al listado de empleados --}}
        <header
            class="col-span-full lg:col-span-8 rounded-3xl border border-zinc-900 bg-zinc-950 p-6 sm:p-7 text-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div class="space-y-1.5">
                <span
                    class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-zinc-300">
                    <i data-lucide="user-cog" class="h-3.5 w-3.5" aria-hidden="true"></i>
                    <span>Actualización de Expediente</span>
                </span>
                <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">Editar Empleado</h1>
                <p id="subtitulo-editar-empleado" class="text-sm text-zinc-400">Actualiza la información personal,
                    ubicación, teléfonos y datos laborales.</p>
            </div>

            <a href="/empleado/registros"
                class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl border border-white/15 bg-white/10 px-4 py-2.5 text-sm font-medium text-white transition-all hover:bg-white/15 focus:outline-none focus:ring-2 focus:ring-marca-400 focus:ring-offset-2 focus:ring-offset-zinc-950">
                <i data-lucide="arrow-left" class="h-4 w-4" aria-hidden="true"></i>
                <span>Volver a Empleados</span>
            </a>
        </header>

        {{-- Card 2 (Bento Marca): Resumen de identificación del trabajador --}}
        <aside
            class="col-span-full lg:col-span-4 rounded-3xl border border-marca bg-marca p-6 text-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-marca-200">Identificación en Nómina</span>
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white/10 text-marca-100">
                    <i data-lucide="badge-check" class="h-5 w-5" aria-hidden="true"></i>
                </div>
            </div>
            <div class="mt-4 flex flex-wrap items-end justify-between gap-2">
                <div>
                    <p class="text-2xl sm:text-3xl font-bold tracking-tight text-white">
                        {{ $empleado->codigo_empleado ?? 'SIN-COD' }}</p>
                    <p class="mt-1 text-xs font-medium text-marca-100">Cédula:
                        {{ ($persona->nacionalidad ?? 'V') . '-' . ($persona->cedula ?? '') }}</p>
                </div>
                <span
                    class="inline-flex items-center rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-semibold text-white">
                    @if (((int) ($empleado->estatus_actual ?? 1)) === 3)
                        Pendiente de Documentos
                    @elseif(((int) ($empleado->estatus_actual ?? 1)) === 1)
                        Activo en Nómina
                    @else
                        Inactivo
                    @endif
                </span>
            </div>
        </aside>

        {{-- Sección 1: Datos Personales (PersonasModel) --}}
        <section
            class="col-span-full md:col-span-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_15px_30px_rgba(0,0,0,0.05)] space-y-5">
            <header>
                <h2 class="text-lg font-semibold text-zinc-900">Datos Personales</h2>
                <p class="text-sm text-zinc-500">Identificación civil del trabajador.</p>
            </header>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                @component('components.input', [
                    'label' => 'Primer nombre',
                    'id' => 'primer_nombre',
                    'name' => 'primer_nombre',
                    'value' => $persona->primer_nombre ?? '',
                    'maxlength' => 100,
                    'required' => true,
                ])
                @endcomponent
                @component('components.input', [
                    'label' => 'Segundo nombre',
                    'id' => 'segundo_nombre',
                    'name' => 'segundo_nombre',
                    'value' => $persona->segundo_nombre ?? '',
                    'maxlength' => 100,
                    'required' => true,
                ])
                @endcomponent
                @component('components.input', [
                    'label' => 'Primer apellido',
                    'id' => 'primer_apellido',
                    'name' => 'primer_apellido',
                    'value' => $persona->primer_apellido ?? '',
                    'maxlength' => 100,
                    'required' => true,
                ])
                @endcomponent
                @component('components.input', [
                    'label' => 'Segundo apellido',
                    'id' => 'segundo_apellido',
                    'name' => 'segundo_apellido',
                    'value' => $persona->segundo_apellido ?? '',
                    'maxlength' => 100,
                    'required' => true,
                ])
                @endcomponent

                @component('components.select', [
                    'label' => 'Nacionalidad',
                    'id' => 'nacionalidad',
                    'name' => 'nacionalidad',
                    'required' => true,
                    'options' => ['V' => 'V', 'E' => 'E'],
                    'selected' => $persona->nacionalidad ?? '',
                ])
                @endcomponent
                @component('components.input', [
                    'label' => 'Cédula',
                    'id' => 'cedula',
                    'name' => 'cedula',
                    'value' => $persona->cedula ?? '',
                    'maxlength' => 9,
                    'leftIcon' => 'id-card',
                    'required' => true,
                ])
                @endcomponent

                @component('components.input', [
                    'label' => 'Fecha de nacimiento',
                    'id' => 'fecha_nacimiento',
                    'name' => 'fecha_nacimiento',
                    'value' => isset($persona->fecha_nacimiento) ? $persona->fecha_nacimiento->format('Y-m-d') : '',
                    'type' => 'date',
                    'required' => true,
                ])
                @endcomponent
                @component('components.select', [
                    'label' => 'Género',
                    'id' => 'genero',
                    'name' => 'genero',
                    'required' => true,
                    'options' => $generos ?? ['Masculino' => 'Masculino', 'Femenino' => 'Femenino'],
                    'selected' => $persona->genero ?? '',
                ])
                @endcomponent
                @component('components.select', [
                    'label' => 'Estado civil',
                    'id' => 'estado_civil_id',
                    'name' => 'estado_civil_id',
                    'required' => true,
                    'options' => $estados_civiles ?? [],
                    'selected' => $persona->estado_civil_id ?? '',
                ])
                @endcomponent
            </div>
        </section>

        {{-- Sección 2: Datos Laborales (EmpleadosModel) --}}
        <section
            class="col-span-full md:col-span-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_15px_30px_rgba(0,0,0,0.05)] space-y-5">
            <header>
                <h2 class="text-lg font-semibold text-zinc-900">Datos Laborales</h2>
                <p class="text-sm text-zinc-500">Ubicación administrativa, cargo y formación académica.</p>
            </header>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                @component('components.input', [
                    'label' => 'Código de empleado',
                    'id' => 'codigo_empleado',
                    'name' => 'codigo_empleado',
                    'value' => $empleado->codigo_empleado ?? '',
                    'maxlength' => 10,
                    'readonly' => true,
                ])
                @endcomponent
                @component('components.input', [
                    'label' => 'Correo institucional',
                    'id' => 'correo',
                    'name' => 'correo',
                    'value' => $empleado->correo ?? '',
                    'type' => 'email',
                    'leftIcon' => 'mail',
                    'maxlength' => 255,
                ])
                @endcomponent

                @component('components.select', [
                    'label' => 'Dependencia',
                    'id' => 'dependencia_id',
                    'name' => 'dependencia_id',
                    'required' => true,
                    'search' => true,
                    'options' => $dependencias ?? [],
                    'selected' => $empleado->dependencia_id ?? '',
                ])
                @endcomponent
                @component('components.select', [
                    'label' => 'Departamento',
                    'id' => 'departamento_id',
                    'name' => 'departamento_id',
                    'required' => true,
                    'search' => true,
                    'options' => $departamentos ?? [],
                    'selected' => $empleado->departamento_id ?? '',
                ])
                @endcomponent
                @component('components.select', [
                    'label' => 'Cargo',
                    'id' => 'cargo_id',
                    'name' => 'cargo_id',
                    'required' => true,
                    'search' => true,
                    'options' => $cargos ?? [],
                    'selected' => $empleado->cargo_id ?? '',
                ])
                @endcomponent
                @component('components.select', [
                    'label' => 'Estatus laboral',
                    'id' => 'estatus_id',
                    'name' => 'estatus_id',
                    'required' => true,
                    'options' => $estatus ?? [],
                    'selected' => $empleado->estatus_id ?? '',
                ])
                @endcomponent
                @component('components.select', [
                    'label' => 'Nivel académico',
                    'id' => 'nivel_academico_id',
                    'name' => 'nivel_academico_id',
                    'required' => true,
                    'options' => $niveles_academicos ?? [],
                    'selected' => $empleado->nivel_academico_id ?? '',
                ])
                @endcomponent
                @component('components.select', [
                    'label' => 'Carrera',
                    'id' => 'carrera_id',
                    'name' => 'carrera_id',
                    'search' => true,
                    'options' => $carreras ?? [],
                    'selected' => $empleado->carrera_id ?? '',
                ])
                @endcomponent

                <div class="sm:col-span-2">
                    @component('components.select', [
                        'label' => 'Estatus actual en nómina',
                        'id' => 'estatus_actual',
                        'name' => 'estatus_actual',
                        'required' => true,
                        'options' => ['1' => 'Activo (1)', '3' => 'Pendiente (3)', '0' => 'Inactivo (0)'],
                        'selected' => (string) ($empleado->estatus_actual ?? '1'),
                    ])
                    @endcomponent
                </div>
            </div>
        </section>

        {{-- Sección 3: Dirección de Habitación (DireccionModel) --}}
        <section
            class="col-span-full md:col-span-7 rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_15px_30px_rgba(0,0,0,0.05)] space-y-5">
            <header>
                <h2 class="text-lg font-semibold text-zinc-900">Dirección de Habitación</h2>
                <p class="text-sm text-zinc-500">Campos correspondientes al modelo Direccion.</p>
            </header>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                @component('components.select', [
                    'label' => 'Estado',
                    'id' => 'estado',
                    'name' => 'estado',
                    'search' => true,
                    'options' => $estados ?? [],
                    'selected' => $estado_seleccionado ?? '',
                ])
                @endcomponent
                @component('components.select', [
                    'label' => 'Municipio',
                    'id' => 'municipio',
                    'name' => 'municipio',
                    'search' => true,
                    'options' => $municipios ?? [],
                    'selected' => $municipio_seleccionado ?? '',
                ])
                @endcomponent
                @component('components.select', [
                    'label' => 'Parroquia',
                    'id' => 'parroquia_id',
                    'name' => 'parroquia_id',
                    'required' => true,
                    'search' => true,
                    'options' => $parroquias ?? [],
                    'selected' => $direccion->parroquia_id ?? '',
                ])
                @endcomponent
                @component('components.input', [
                    'label' => 'Tipo de vivienda',
                    'id' => 'vivienda',
                    'name' => 'vivienda',
                    'value' => $direccion->vivienda ?? '',
                    'maxlength' => 30,
                    'required' => true,
                ])
                @endcomponent
                @component('components.input', [
                    'label' => 'Calle / Avenida',
                    'id' => 'calle',
                    'name' => 'calle',
                    'value' => $direccion->calle ?? '',
                    'maxlength' => 30,
                    'required' => true,
                ])
                @endcomponent
                @component('components.input', [
                    'label' => 'Urbanización / Sector',
                    'id' => 'nombre_urb',
                    'name' => 'nombre_urb',
                    'value' => $direccion->nombre_urb ?? '',
                    'maxlength' => 100,
                    'required' => true,
                ])
                @endcomponent
                @component('components.input', [
                    'label' => 'Piso / Edificio',
                    'id' => 'piso_edificio',
                    'name' => 'piso_edificio',
                    'value' => $direccion->piso_edificio ?? '',
                    'maxlength' => 10,
                    'required' => true,
                ])
                @endcomponent
                @component('components.input', [
                    'label' => 'Número de vivienda',
                    'id' => 'numero_vivienda',
                    'name' => 'numero_vivienda',
                    'value' => $direccion->numero_vivienda ?? '',
                    'maxlength' => 10,
                    'required' => true,
                ])
                @endcomponent
                <div class="sm:col-span-2">
                    @component('components.input', [
                        'label' => 'Punto de referencia / Descripción',
                        'id' => 'descripcion',
                        'name' => 'descripcion',
                        'value' => $direccion->descripcion ?? '',
                        'maxlength' => 255,
                    ])
                    @endcomponent
                </div>
            </div>
        </section>

        {{-- Sección 4: Teléfonos de Contacto (TelefonoPersonasModel) --}}
        <section
            class="col-span-full md:col-span-5 rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_15px_30px_rgba(0,0,0,0.05)] space-y-5 flex flex-col justify-between">
            <div class="space-y-5">
                <header>
                    <h2 class="text-lg font-semibold text-zinc-900">Teléfonos de Contacto</h2>
                    <p class="text-sm text-zinc-500">Número personal principal y contacto de emergencia.</p>
                </header>

                <div class="space-y-4">
                    @component('components.input', [
                        'label' => 'Teléfono principal',
                        'id' => 'telefono_principal',
                        'name' => 'telefono_principal',
                        'value' => $telefono_principal ?? '',
                        'maxlength' => 20,
                        'leftIcon' => 'phone',
                        'required' => true,
                    ])
                    @endcomponent
                    @component('components.input', [
                        'label' => 'Teléfono de emergencia',
                        'id' => 'telefono_emergencia',
                        'name' => 'telefono_emergencia',
                        'value' => $telefono_emergencia ?? '',
                        'maxlength' => 20,
                        'leftIcon' => 'phone-call',
                    ])
                    @endcomponent
                </div>
            </div>
        </section>

        {{-- Sección 5: Expediente Documental del Trabajador (Contrato Colectivo / Procesos RRHH) --}}
        <section
            class="col-span-full rounded-3xl border border-slate-200 bg-white p-6 shadow-[0_15px_30px_rgba(0,0,0,0.05)] space-y-5">
            <header
                class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between border-b border-slate-200 pb-4">
                <div>
                    <h2 class="text-lg font-semibold text-zinc-900">Expediente Documental del Trabajador</h2>
                    <p class="text-sm text-zinc-500">
                        Verifica los documentos cargados y adjunta los faltantes.
                        @if (((int) ($empleado->estatus_actual ?? 1)) === 3)
                            <span class="font-medium text-yellow-800">Al completar todos los documentos obligatorios, el
                                trabajador pasará automáticamente a estado Activo.</span>
                        @endif
                    </p>
                </div>
                @php
                    $totalOblig = 0;
                    $cargadosOblig = 0;
                    foreach ($recaudos ?? [] as $itemRec) {
                        if (!empty($itemRec['obligatorio'])) {
                            $totalOblig++;
                            if (!empty($itemRec['cargado'])) {
                                $cargadosOblig++;
                            }
                        }
                    }
                @endphp
                <span
                    class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold {{ $cargadosOblig >= $totalOblig && $totalOblig > 0 ? 'bg-green-400/20 text-green-800' : 'bg-yellow-400/20 text-yellow-900' }}">
                    <i data-lucide="{{ $cargadosOblig >= $totalOblig && $totalOblig > 0 ? 'check-circle-2' : 'file-warning' }}"
                        class="h-3.5 w-3.5" aria-hidden="true"></i>
                    <span>Obligatorios: {{ $cargadosOblig }} / {{ $totalOblig }}</span>
                </span>
            </header>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($recaudos ?? [] as $rec)
                    @component('components.tarjeta_recaudo', [
                        'rec' => $rec,
                        'prefijoId' => 'doc-emp',
                        'claseInput' => 'atlas-doc-input',
                    ])
                    @endcomponent
                @endforeach
            </div>

            <footer class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end">
                <a href="/empleado/registros"
                    class="inline-flex w-full sm:w-auto items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-zinc-900 transition-all hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2">
                    Cancelar
                </a>
                <button type="submit"
                    class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl bg-marca px-5 py-2.5 text-sm font-medium text-white shadow-[0_15px_30px_rgba(0,0,0,0.05)] transition-all hover:bg-marca-700 focus:outline-none focus:ring-2 focus:ring-marca-500 focus:ring-offset-2 active:scale-[0.98] cursor-pointer">
                    <i data-lucide="save" class="h-4 w-4" aria-hidden="true"></i>
                    <span>Actualizar Empleado</span>
                </button>
            </footer>
        </section>
    </form>
@endsection

@push('scripts')
    {{-- CARGAR SCRIPTS DE JS --}}
    <script type="module" src="/js/editar_empleado.js"></script>
@endpush
